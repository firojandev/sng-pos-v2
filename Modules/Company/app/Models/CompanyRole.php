<?php

namespace Modules\Company\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;

/**
 * A company role: the company-level permissions (HR, payroll, accounting,
 * tasks) given to the company employees that hold it.
 */
class CompanyRole extends Model
{
    /**
     * The modules whose permissions a company role can grant.
     *
     * @var list<string>
     */
    public const FEATURES = ['employees', 'attendance', 'leave', 'hr-setup', 'payroll', 'payroll-setup', 'accounting', 'tasks'];

    protected $fillable = ['company_id', 'name', 'permissions'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function allows(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * The permissions a company role can hold, grouped by module.
     *
     * @return array<string, array{label: array{bn: string, en: string}, actions: list<string>}>
     */
    public static function grantable(): array
    {
        $labels = Features::all();

        return collect(self::FEATURES)
            ->mapWithKeys(fn (string $feature) => [$feature => ['label' => $labels[$feature] ?? ['bn' => $feature, 'en' => $feature], 'actions' => Permissions::actionsFor($feature)]])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        return collect(static::grantable())->flatMap(fn (array $group, string $feature) => array_map(fn (string $action) => "{$feature}.{$action}", $group['actions']))->values()->all();
    }
}
