<?php

namespace Modules\Task\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;

/**
 * A task reported by one user and assigned to another (or to themself: a
 * personal to-do). Completing it records when it was finished. Only for
 * companies (see isAvailableTo).
 */
class Task extends Model
{
    use BelongsToCompany;

    public const STATUSES = ['todo', 'in_progress', 'completed', 'canceled'];

    public const OPEN_STATUSES = ['todo', 'in_progress'];

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        static::saving(function (Task $task) {
            if ($task->isDirty('status') || ! $task->exists) {
                $task->finished_at = $task->status === 'completed' ? ($task->finished_at ?? now()) : null;
            }
        });
    }

    protected $fillable = ['company_id', 'shop_id', 'title', 'description', 'status', 'reported_by', 'assigned_to', 'deadline', 'finished_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['deadline' => 'date', 'finished_at' => 'datetime'];
    }

    /**
     * @return array<string, array{bn: string, en: string, color: string}>
     */
    public static function statusLabels(): array
    {
        return [
            'todo' => ['bn' => 'করণীয়', 'en' => 'To Do', 'color' => 'grey'],
            'in_progress' => ['bn' => 'চলমান', 'en' => 'In Progress', 'color' => 'blue'],
            'completed' => ['bn' => 'সম্পন্ন', 'en' => 'Completed', 'color' => 'green'],
            'canceled' => ['bn' => 'বাতিল', 'en' => 'Canceled', 'color' => 'red'],
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Tasks the user may see: assigned to or reported by them, or all with
     * the "manage" permission.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (static::canManage($user)) {
            return;
        }

        $query->where(fn ($inner) => $inner->where('tasks.assigned_to', $user->id)->orWhere('tasks.reported_by', $user->id));
    }

    /**
     * Task management is for companies: the company owner and admins and the
     * company's staff, when the company's plan includes it. Standalone shop
     * owners and the super admin don't get it.
     */
    public static function isAvailableTo(?User $user): bool
    {
        if (! $user || $user->isSuperAdmin() || ! $user->shop) {
            return false;
        }

        return (bool) $user->shop->company?->isBusiness() && $user->shop->hasFeature('tasks');
    }

    public static function canManage(User $user): bool
    {
        return $user->isShopAdmin() || $user->can('tasks.manage');
    }

    public static function canAssign(User $user): bool
    {
        return static::canManage($user) || $user->can('tasks.assign');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->deadline && $this->deadline->lt(now()->startOfDay());
    }

    public function isPersonal(): bool
    {
        return (int) $this->reported_by === (int) $this->assigned_to;
    }

    /**
     * The reporter (or a manager) edits and deletes a task.
     */
    public function canBeEditedBy(User $user): bool
    {
        return static::canManage($user) || (int) $this->reported_by === (int) $user->id;
    }

    /**
     * The assignee can also move it along.
     */
    public function canChangeStatusBy(User $user): bool
    {
        return $this->canBeEditedBy($user) || (int) $this->assigned_to === (int) $user->id;
    }
}
