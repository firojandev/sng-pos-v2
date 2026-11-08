<?php

namespace Modules\Employee\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Core\Support\TenantContext;
use Modules\Shop\Models\Shop;

/**
 * An employee of the company. shop_id is where they work.
 *
 * The department and designation text (used by the quick add/edit form) and
 * the linked department/designation records are kept in step.
 */
class Employee extends Model
{
    use BelongsToCompany;

    public const STATUSES = ['active', 'inactive', 'resigned', 'terminated'];

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        static::creating(function (Employee $employee) {
            if (empty($employee->employee_code) && $employee->company_id) {
                $employee->employee_code = static::nextCode($employee->company_id);
            }
        });

        // `saving` runs before `creating`, so the company is resolved here.
        static::saving(function (Employee $employee) {
            $employee->shop_id ??= app(TenantContext::class)->shopId();
            $employee->company_id ??= $employee->shop_id
                ? DB::table('shops')->where('id', $employee->shop_id)->value('company_id')
                : app(TenantContext::class)->companyId();

            $employee->syncNamedRecord('department', Department::class);
            $employee->syncNamedRecord('designation', Designation::class);
        });
    }

    protected $fillable = [
        'company_id', 'employee_code', 'shop_id', 'user_id', 'name', 'phone', 'email',
        'designation', 'designation_id', 'department', 'department_id', 'shift_id', 'employment_type',
        'salary', 'joining_date', 'confirmation_date', 'address', 'permanent_address', 'status',
        'gender', 'date_of_birth', 'blood_group', 'nid', 'tin', 'tax_category', 'tax_location', 'father_name', 'mother_name', 'marital_status',
        'emergency_contact_name', 'emergency_contact_phone', 'bank_name', 'bank_account_no', 'mfs_number',
        'device_user_id', 'separation_date', 'separation_reason',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'date_of_birth' => 'date',
        'separation_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public static function nextCode(int $companyId): string
    {
        $count = static::withoutGlobalScopes()->where('company_id', $companyId)->count();

        do {
            $code = 'EMP-'.str_pad((string) ++$count, 4, '0', STR_PAD_LEFT);
        } while (static::withoutGlobalScopes()->where('company_id', $companyId)->where('employee_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function departmentRecord(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designationRecord(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->latest();
    }

    /**
     * The user's own employee record: nobody edits or deletes their own.
     */
    public function isRecordOf(?User $user): bool
    {
        return $user !== null && $this->user_id !== null && (int) $this->user_id === (int) $user->id;
    }

    /**
     * Whether payslips, loans or a settlement refer to the employee, so the
     * record must be kept (set inactive instead of deleting).
     */
    public function hasPayrollHistory(): bool
    {
        foreach (['payslips', 'employee_loans', 'final_settlements', 'payroll_payments'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('employee_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Employees working at the given shop (defaults to the current shop; the
     * whole company in the company workspace).
     */
    #[Scope]
    protected function workingAtShop(Builder $query, ?int $shopId = null): void
    {
        $shopId ??= app(TenantContext::class)->shopId();

        // In the company workspace (no shop): every shop of the company, which
        // the company scope already limits to.
        if ($shopId) {
            $query->where('employees.shop_id', $shopId);
        }
    }

    /**
     * The shift that applies: the employee's own, or the company's default.
     */
    public function effectiveShift(): ?Shift
    {
        return $this->shift ?? Shift::withoutGlobalScopes()
            ->where('company_id', $this->company_id)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    /**
     * Keep a text column and its linked record in step: a chosen record sets
     * the text; new text finds or creates the record.
     *
     * @param  class-string<Model>  $recordClass
     */
    private function syncNamedRecord(string $attribute, string $recordClass): void
    {
        $idAttribute = $attribute.'_id';

        if ($this->isDirty($idAttribute) && $this->{$idAttribute}) {
            $this->{$attribute} = $recordClass::withoutGlobalScopes()->whereKey($this->{$idAttribute})->value('name');

            return;
        }

        $name = trim((string) $this->{$attribute});

        if (($this->isDirty($attribute) || ! $this->exists) && $this->company_id) {
            $this->{$idAttribute} = $name === '' ? null : $recordClass::withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $this->company_id, 'name' => $name],
            )->id;
        }
    }
}
