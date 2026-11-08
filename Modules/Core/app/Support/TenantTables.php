<?php

namespace Modules\Core\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Company\Models\Company;
use Modules\Shop\Models\Shop;

/**
 * Which tables hold a shop's (or its company's) data, and how to select
 * those rows, worked out from the database schema rather than a hand-kept
 * list, so new modules are covered without touching the backup:
 *
 * - shop-owned: a table with a shop_id (unless the table is shared by the
 *   company, see below) — rows of the shop;
 * - company-owned: a table with a company_id whose shop_id is missing or
 *   optional (customers, the ledger, HR rules) — rows of the company, only
 *   when the company runs just this shop (a shared company's data is not
 *   rewritten by restoring one of its shops);
 * - child tables: rows whose required foreign key points at a selected row
 *   (sale_items → sales, payslip_items → payslips);
 * - polymorphic owners: rows of a table without its own shop or company
 *   column that are owned by the company or the shop through a
 *   *_type/*_id pair (subscriptions).
 *
 * Tables reached by none of these (plans, permissions, framework tables)
 * are global and left out.
 */
class TenantTables
{
    /**
     * Framework tables that are never tenant data.
     *
     * @var list<string>
     */
    private const FRAMEWORK_TABLES = [
        'migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'password_reset_tokens', 'personal_access_tokens',
    ];

    /**
     * Tables with both columns that belong to a shop even though their
     * shop_id is optional (an employee works at a shop).
     *
     * @var list<string>
     */
    private const SHOP_OWNED = ['employees'];

    /**
     * Foreign keys to these tables don't make a row tenant data (almost
     * everything records who created it).
     *
     * @var list<string>
     */
    private const IGNORED_PARENTS = ['users', 'shops', 'companies'];

    /**
     * @var array<string, array{columns: array<string, bool>, foreign_keys: list<array{column: string, table: string, foreign_column: string}>}>|null
     */
    private ?array $schema = null;

    /**
     * The WHERE clause selecting each tenant table's rows for a shop,
     * parents before children (insert order; delete in reverse).
     *
     * @param  array<string, string>  $extraConditions  table => extra condition ANDed on (e.g. keep super admins)
     * @return array<string, string>
     */
    public function plan(int $shopId, ?int $companyId, bool $includeCompany, array $extraConditions = []): array
    {
        $schema = $this->schema();
        $conditions = [];
        $pdo = DB::connection()->getPdo();

        // Direct ownership first.
        foreach ($schema as $table => $info) {
            $parts = [];
            $columns = $info['columns'];

            if ($table === 'shops') {
                $parts[] = "`id` = {$shopId}";
            } elseif ($table === 'companies') {
                if ($includeCompany && $companyId) {
                    $parts[] = "`id` = {$companyId}";
                }
            } elseif ($this->isShopOwned($table, $columns)) {
                $parts[] = "`shop_id` = {$shopId}";
            } elseif (array_key_exists('company_id', $columns) && $includeCompany && $companyId) {
                $parts[] = "`company_id` = {$companyId}";
            }

            // A polymorphic owner only counts for tables without their own
            // shop or company column (subscriptions belong to a company).
            $ownsDirectly = array_key_exists('shop_id', $columns) || array_key_exists('company_id', $columns);

            foreach ($ownsDirectly ? [] : $this->morphPairs($columns) as [$typeColumn, $idColumn]) {
                $parts[] = "(`{$typeColumn}` = ".$pdo->quote(Shop::class)." AND `{$idColumn}` = {$shopId})";

                if ($includeCompany && $companyId) {
                    $parts[] = "(`{$typeColumn}` = ".$pdo->quote(Company::class)." AND `{$idColumn}` = {$companyId})";
                }
            }

            if ($parts) {
                $conditions[$table] = $parts;
            }
        }

        // Then children, through their required foreign keys to a table
        // already reached, until nothing new is reached.
        $links = [];
        do {
            $added = false;

            foreach ($schema as $table => $info) {
                if ($this->isShopOwned($table, $info['columns']) || in_array($table, ['shops', 'companies'], true)) {
                    continue;
                }

                foreach ($info['foreign_keys'] as $key) {
                    $parent = $key['table'];
                    $reached = isset($conditions[$parent]) || isset($links[$parent]);

                    if ($parent === $table || in_array($parent, self::IGNORED_PARENTS, true) || ! $reached || ($info['columns'][$key['column']] ?? true) || isset($links[$table][$key['column']])) {
                        continue;
                    }

                    $links[$table][$key['column']] = $key;
                    $added = true;
                }
            }
        } while ($added);

        // Build each table's condition once, parents first.
        $plan = [];
        foreach ($this->parentsFirst(array_keys($conditions + $links)) as $table) {
            $parts = $conditions[$table] ?? [];

            foreach ($links[$table] ?? [] as $key) {
                if (isset($plan[$key['table']])) {
                    $parts[] = "`{$key['column']}` IN (SELECT `{$key['foreign_column']}` FROM `{$key['table']}` WHERE ".$plan[$key['table']].')';
                }
            }

            if ($parts === []) {
                continue;
            }

            $where = $this->join($parts);
            $plan[$table] = isset($extraConditions[$table]) ? "({$where}) AND ({$extraConditions[$table]})" : $where;
        }

        return $plan;
    }

    /**
     * @return array<string, array{columns: array<string, bool>, foreign_keys: list<array{column: string, table: string, foreign_column: string}>}>
     */
    private function schema(): array
    {
        if ($this->schema !== null) {
            return $this->schema;
        }

        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $tables = collect(Schema::getTables())
            ->when($connection->getDriverName() === 'mysql' || $connection->getDriverName() === 'mariadb', fn ($tables) => $tables->where('schema', $database))
            ->pluck('name')
            ->reject(fn (string $table) => in_array($table, self::FRAMEWORK_TABLES, true) || str_starts_with($table, 'sqlite_'))
            ->values();

        $this->schema = [];

        foreach ($tables as $table) {
            $columns = collect(Schema::getColumns($table))->mapWithKeys(fn (array $column) => [$column['name'] => (bool) $column['nullable']])->all();
            $foreignKeys = collect(Schema::getForeignKeys($table))
                ->filter(fn (array $key) => count($key['columns']) === 1)
                ->map(fn (array $key) => ['column' => $key['columns'][0], 'table' => $key['foreign_table'], 'foreign_column' => $key['foreign_columns'][0] ?? 'id'])
                ->values()
                ->all();

            $this->schema[$table] = ['columns' => $columns, 'foreign_keys' => $foreignKeys];
        }

        return $this->schema;
    }

    /**
     * @param  array<string, bool>  $columns  name => nullable
     */
    private function isShopOwned(string $table, array $columns): bool
    {
        if (! array_key_exists('shop_id', $columns) || in_array($table, ['shops', 'companies'], true)) {
            return false;
        }

        return in_array($table, self::SHOP_OWNED, true) || ! array_key_exists('company_id', $columns) || ! $columns['shop_id'];
    }

    /**
     * @param  array<string, bool>  $columns
     * @return list<array{0: string, 1: string}>
     */
    private function morphPairs(array $columns): array
    {
        $pairs = [];

        foreach (array_keys($columns) as $column) {
            if (str_ends_with($column, '_type') && array_key_exists(substr($column, 0, -5).'_id', $columns)) {
                $pairs[] = [$column, substr($column, 0, -5).'_id'];
            }
        }

        return $pairs;
    }

    /**
     * @param  list<string>  $parts
     */
    private function join(array $parts): string
    {
        return count($parts) === 1 ? $parts[0] : '('.implode(' OR ', $parts).')';
    }

    /**
     * Order tables so that referenced tables come before those referring to
     * them (cycles are broken arbitrarily).
     *
     * @param  list<string>  $tables
     * @return list<string>
     */
    private function parentsFirst(array $tables): array
    {
        $schema = $this->schema();
        $included = array_flip($tables);
        $ordered = [];
        $visiting = [];

        $visit = function (string $table) use (&$visit, &$ordered, &$visiting, $included, $schema) {
            if (isset($ordered[$table]) || isset($visiting[$table])) {
                return;
            }

            $visiting[$table] = true;

            foreach ($schema[$table]['foreign_keys'] as $key) {
                if (isset($included[$key['table']]) && $key['table'] !== $table) {
                    $visit($key['table']);
                }
            }

            unset($visiting[$table]);
            $ordered[$table] = true;
        };

        sort($tables);
        foreach ($tables as $table) {
            $visit($table);
        }

        return array_keys($ordered);
    }
}
