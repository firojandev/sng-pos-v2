<?php

namespace Modules\Customer\Console;

use Illuminate\Console\Command;
use Modules\Customer\Services\LoyaltyService;

class ExpireLoyaltyPoints extends Command
{
    protected $signature = 'loyalty:expire-points';

    protected $description = 'Expire unspent loyalty points whose expiry date has passed.';

    public function handle(LoyaltyService $loyalty): int
    {
        $expired = $loyalty->expirePoints();

        $this->info("Expired {$expired} loyalty points.");

        return self::SUCCESS;
    }
}
