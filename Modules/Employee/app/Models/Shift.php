<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * Working hours. weekend_days holds ISO weekdays (1 = Monday … 7 = Sunday);
 * in Bangladesh the weekend is usually Friday (5).
 */
class Shift extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'start_time', 'end_time', 'grace_minutes', 'weekend_days', 'is_default'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['weekend_days' => 'array', 'is_default' => 'boolean', 'grace_minutes' => 'integer'];
    }

    public function isWeekend(Carbon $date): bool
    {
        return in_array($date->isoWeekday(), array_map('intval', $this->weekend_days ?? []), true);
    }

    public function startsOn(Carbon $date): Carbon
    {
        return $date->copy()->setTimeFromTimeString($this->start_time);
    }

    /**
     * The shift's end on the given day (the next day for an overnight shift).
     */
    public function endsOn(Carbon $date): Carbon
    {
        $end = $date->copy()->setTimeFromTimeString($this->end_time);

        return $end->lessThanOrEqualTo($this->startsOn($date)) ? $end->addDay() : $end;
    }

    public function lengthInMinutes(): int
    {
        $today = now()->startOfDay();

        return (int) $this->startsOn($today)->diffInMinutes($this->endsOn($today));
    }
}
