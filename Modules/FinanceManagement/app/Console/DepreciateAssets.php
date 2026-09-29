<?php

namespace Modules\FinanceManagement\Console;

use Illuminate\Console\Command;
use Modules\FinanceManagement\Services\AssetDepreciationService;

class DepreciateAssets extends Command
{
    protected $signature = 'assets:depreciate {--shop= : Only this shop}';

    protected $description = 'Record (and post) straight-line depreciation for every month up to last month';

    public function handle(AssetDepreciationService $depreciation): int
    {
        $count = $depreciation->recordUpTo(now()->subMonthNoOverflow(), $this->option('shop') ? (int) $this->option('shop') : null);

        $this->info("{$count} month(s) of depreciation recorded.");

        return self::SUCCESS;
    }
}
