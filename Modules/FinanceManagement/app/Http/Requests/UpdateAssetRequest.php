<?php

namespace Modules\FinanceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        if (! $this->has('depreciation_type') || ! in_array($this->depreciation_type, ['flat', 'percentage'], true)) {
            $merge['depreciation_type'] = 'flat';
        }
        if ($this->has('depreciation') && ($this->depreciation === null || $this->depreciation === '')) {
            $merge['depreciation'] = 0;
        }
        if ($this->has('validity') && ($this->validity === null || $this->validity === '')) {
            $merge['validity'] = null;
        } elseif ($this->has('useful_life') && ! $this->has('validity')) {
            $merge['validity'] = ($this->useful_life === null || $this->useful_life === '') ? null : $this->useful_life;
        }
        $validityUnit = $this->input('validity_unit', $this->input('useful_life_unit', 'year'));
        if (! in_array($validityUnit, ['year', 'month', 'day'], true)) {
            $validityUnit = 'year';
        }
        $merge['validity_unit'] = $validityUnit;
        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'depreciation_type' => ['required', 'in:flat,percentage'],
            'depreciation' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $type = $this->input('depreciation_type', 'flat');
                    if ($type === 'percentage') {
                        if ((float) $value > 100) {
                            $fail('অবচয় শতকরা ১০০% এর বেশি হতে পারে না / Depreciation percentage cannot exceed 100%.');
                        }
                    } else {
                        $amount = (float) $this->input('amount', 0);
                        if ((float) $value > $amount) {
                            $fail('অবচয় সম্পদের পরিমাণের চেয়ে বেশি হতে পারে না / Depreciation cannot exceed asset amount.');
                        }
                    }
                },
            ],
            'validity' => ['nullable', 'numeric', 'min:0'],
            'validity_unit' => ['nullable', 'in:year,month,day'],
            'useful_life' => ['nullable', 'numeric', 'min:0'],
            'useful_life_unit' => ['nullable', 'in:year,month,day'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'সম্পদের নাম / Asset Name',
            'amount' => 'পরিমাণ / Amount',
            'depreciation_type' => 'অবচয়ের ধরন / Depreciation Type',
            'depreciation' => 'অবচয় / Depreciation',
            'validity' => 'মেয়াদ / Validity',
            'validity_unit' => 'মেয়াদের একক / Validity Unit',
            'note' => 'নোট / Note',
        ];
    }

    public function messages(): array
    {
        return [
            'depreciation_type.in' => 'অবচয়ের ধরন অবশ্যই ফ্ল্যাট বা শতাংশ হতে হবে / Depreciation type must be flat or percentage.',
            'validity.numeric' => 'মেয়াদ একটি সংখ্যা হতে হবে / Validity must be a number.',
            'validity.min' => 'মেয়াদ ০ বা তার বেশি হতে হবে / Validity must be at least 0.',
        ];
    }
}
