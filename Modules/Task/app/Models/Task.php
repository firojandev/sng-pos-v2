<?php

namespace Modules\Task\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Company\Models\Company;
use Modules\Core\Observers\AuditObserver;

/**
 * A task reported by one user and assigned to another (or to themself: a
 * personal to-do). Completing it records when it was finished.
 *
 * Tasks belong to a company (see companyFor): a real company, or the
 * Default Company for its admins and staff. The company's plan must
 * include Tasks. Standalone shops and the super admin don't get them.
 */
class Task extends Model
{
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
     * Tasks of the user's task company they may see: assigned to or
     * reported by them, or all of them when they manage tasks.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->where('tasks.company_id', static::companyFor($user)?->id ?? 0);

        if (! static::canManage($user)) {
            $query->where(fn ($inner) => $inner->where('tasks.assigned_to', $user->id)->orWhere('tasks.reported_by', $user->id));
        }
    }

    /**
     * The company whose tasks the user works with: the company of their
     * current shop when it is a real company; otherwise the company they
     * work for at company level (a company, or the Default Company — even
     * while its admin is inside a standalone shop).
     */
    public static function companyFor(?User $user): ?Company
    {
        if (! $user || $user->isSuperAdmin()) {
            return null;
        }

        $shopCompany = $user->shop?->company;
        if ($shopCompany?->isBusiness()) {
            return $shopCompany;
        }

        return $user->companyLevelCompany();
    }

    public static function isAvailableTo(?User $user): bool
    {
        return (bool) static::companyFor($user)?->hasFeature('tasks');
    }

    /**
     * The company's owner and admins, and shop admins or users with the
     * "manage" permission in one of the company's shops, see and manage
     * every task of the company.
     */
    public static function canManage(User $user): bool
    {
        $company = static::companyFor($user);

        if (! $company) {
            return false;
        }

        if ($company->isAdministeredBy($user)) {
            return true;
        }

        return (int) $user->shop?->company_id === (int) $company->id && ($user->isShopAdmin() || $user->can('tasks.manage'));
    }

    public static function canAssign(User $user): bool
    {
        return static::canManage($user) || ((int) $user->shop?->company_id === (int) static::companyFor($user)?->id && $user->can('tasks.assign'));
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
