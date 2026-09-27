<?php

namespace Modules\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantRules;
use Modules\Employee\Models\Attendance;

class SaveAttendanceSheetRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'rows' => ['required', 'array'],
            'rows.*.employee_id' => ['required', 'integer', TenantRules::companyExists('employees')],
            'rows.*.check_in' => ['nullable', 'date_format:H:i'],
            'rows.*.check_out' => ['nullable', 'date_format:H:i'],
            'rows.*.status' => ['nullable', Rule::in(Attendance::STATUSES)],
            'rows.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->shop_id;
    }
}
