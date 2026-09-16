<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Shop\Models\PrinterSetting;

class UpdatePrinterSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->shop_id !== null || auth()->user()->isSuperAdmin());
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->input('printer_type');

        if ($type === PrinterSetting::TYPE_A4) {
            $this->merge([
                'paper_width' => PrinterSetting::PREDEFINED_DIMENSIONS[PrinterSetting::TYPE_A4]['width'],
                'paper_height' => PrinterSetting::PREDEFINED_DIMENSIONS[PrinterSetting::TYPE_A4]['height'],
                'unit' => PrinterSetting::UNIT_MM,
            ]);
        } elseif ($type === PrinterSetting::TYPE_A5) {
            $this->merge([
                'paper_width' => PrinterSetting::PREDEFINED_DIMENSIONS[PrinterSetting::TYPE_A5]['width'],
                'paper_height' => PrinterSetting::PREDEFINED_DIMENSIONS[PrinterSetting::TYPE_A5]['height'],
                'unit' => PrinterSetting::UNIT_MM,
            ]);
        } elseif ($type === PrinterSetting::TYPE_THERMAL) {
            $this->merge([
                'orientation' => PrinterSetting::ORIENTATION_PORTRAIT,
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'printer_type' => ['required', 'string', 'in:'.PrinterSetting::TYPE_A4.','.PrinterSetting::TYPE_A5.','.PrinterSetting::TYPE_THERMAL],
            'orientation' => ['required', 'string', 'in:'.PrinterSetting::ORIENTATION_PORTRAIT.','.PrinterSetting::ORIENTATION_LANDSCAPE],
            'paper_width' => ['required', 'numeric', 'min:30', 'max:500'],
            'paper_height' => ['nullable', 'numeric', 'min:20', 'max:2000'],
            'unit' => ['required', 'string', 'in:'.PrinterSetting::UNIT_MM.','.PrinterSetting::UNIT_INCH],
            'page_margin' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'auto_print' => ['nullable', 'boolean'],
            'show_header_logo' => ['nullable', 'boolean'],
            'show_shop_info' => ['nullable', 'boolean'],
            'show_customer_due' => ['nullable', 'boolean'],
            'show_footer_note' => ['nullable', 'boolean'],
            'print_copies' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'printer_type.required' => 'প্রিন্টারের ধরন নির্বাচন করুন (Printer type is required)।',
            'printer_type.in' => 'অকার্যকর প্রিন্টার টাইপ নির্বাচিত হয়েছে (Invalid printer type)।',
            'orientation.required' => 'ওরিয়েন্টেশন নির্বাচন করুন (Orientation is required)।',
            'paper_width.required' => 'কাগজের প্রস্থ (Paper Width) আবশ্যক।',
            'paper_width.numeric' => 'কাগজের প্রস্থ অবশ্যই সংখ্যায় হতে হবে।',
            'paper_width.min' => 'কাগজের প্রস্থ ন্যূনতম ৩০ হতে হবে।',
            'paper_height.numeric' => 'কাগজের উচ্চতা অবশ্যই সংখ্যায় হতে হবে।',
            'unit.required' => 'পরিমাপের একক (Unit) নির্বাচন করুন।',
            'unit.in' => 'একক শুধুমাত্র mm অথবা inch হতে পারবে।',
        ];
    }
}
