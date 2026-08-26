<?php

namespace Modules\Core\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\AuditLog;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created', null, $this->presentable($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = array_intersect_key($model->getOriginal(), $changes);

        $this->log($model, 'updated', $this->presentable($old), $this->presentable($changes));
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', null, null);
    }

    public function restored(Model $model): void
    {
        $this->log($model, 'restored', null, null);
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    private function log(Model $model, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    /**
     * Strip noisy/internal columns before persisting a snapshot.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function presentable(array $attributes): array
    {
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);

        return $attributes;
    }
}
