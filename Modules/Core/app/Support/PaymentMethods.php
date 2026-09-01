<?php

namespace Modules\Core\Support;

class PaymentMethods
{
    /**
     * @return array<string, array{bn: string, en: string}>
     */
    public static function all(): array
    {
        return [
            'cash' => ['bn' => 'নগদ', 'en' => 'Cash'],
            'bank' => ['bn' => 'ব্যাংক', 'en' => 'Bank'],
            'mobile_banking' => ['bn' => 'মোবাইল ব্যাংকিং', 'en' => 'Mobile Banking'],
            'card' => ['bn' => 'কার্ড', 'en' => 'Card'],
            'other' => ['bn' => 'অন্যান্য', 'en' => 'Other'],
        ];
    }

    /**
     * @return string[]
     */
    public static function keys(): array
    {
        return array_keys(static::all());
    }
}
