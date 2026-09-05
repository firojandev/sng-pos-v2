<?php

namespace Modules\Core\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Modules\Core\Support\BanglaNumber;

class ConvertBengaliNumbers extends TransformsRequest
{
    /**
     * The attributes that should not be converted.
     *
     * @var array<int, string>
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        // Only transform if string contains at least one Bengali numeral or Bengali punctuation
        if (! preg_match('/[\x{09E6}-\x{09EF}।]/u', $value)) {
            return $value;
        }

        // Check if the attribute name matches numeric, money, quantity, measurement, or contact terms
        $isNumericKey = (bool) preg_match(
            '/(price|qty|quantity|amount|discount|tax|vat|rate|cost|charge|due|balance|total|subtotal|alert|factor|duration|phone|mobile|date|year|month|day|min|max|step|code|sku|barcode|otp|pin|opening_due|wholesale|page|per_page)/i',
            $key
        );

        // Check if the string consists exclusively of numbers, Bengali numerals, and common number symbols
        $isPureNumericString = (bool) preg_match('/^[\s\+\-\(\)\.\,\d\x{09E6}-\x{09EF}।]+$/u', $value);

        if ($isNumericKey || $isPureNumericString) {
            return BanglaNumber::toEn($value);
        }

        return $value;
    }
}
