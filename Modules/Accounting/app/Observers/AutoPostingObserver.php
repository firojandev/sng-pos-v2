<?php

namespace Modules\Accounting\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Services\AutoPosting;

/**
 * Re-syncs a record's journal entry whenever it is saved, deleted or
 * restored, once the surrounding transaction has committed (so a sale's
 * lines and costs exist when it is posted).
 */
class AutoPostingObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private AutoPosting $posting) {}

    public function saved(Model $record): void
    {
        $this->posting->sync($record);
    }

    public function deleted(Model $record): void
    {
        $this->posting->sync($record);
    }

    public function restored(Model $record): void
    {
        $this->posting->sync($record);
    }
}
