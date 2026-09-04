<?php

namespace Modules\Core\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class BanglaNumber
{
    /**
     * Number to Bengali word representation (0 to 99).
     *
     * @var array<int, string>
     */
    public static array $bnWords = [
        0 => 'শূন্য',
        1 => 'এক',
        2 => 'দুই',
        3 => 'তিন',
        4 => 'চার',
        5 => 'পাঁচ',
        6 => 'ছয়',
        7 => 'সাত',
        8 => 'আট',
        9 => 'নয়',
        10 => 'দশ',
        11 => 'এগারো',
        12 => 'বারো',
        13 => 'তেরো',
        14 => 'চৌদ্দ',
        15 => 'পনেরো',
        16 => 'ষোলো',
        17 => 'সতেরো',
        18 => 'আঠারো',
        19 => 'উনিশ',
        20 => 'বিশ',
        21 => 'একুশ',
        22 => 'বাইশ',
        23 => 'তেইশ',
        24 => 'চব্বিশ',
        25 => 'পঁচিশ',
        26 => 'ছাব্বিশ',
        27 => 'সাতাশ',
        28 => 'আঠাশ',
        29 => 'উনত্রিশ',
        30 => 'ত্রিশ',
        31 => 'একত্রিশ',
        32 => 'বত্রিশ',
        33 => 'তেত্রিশ',
        34 => 'চৌত্রিশ',
        35 => 'পঁয়ত্রিশ',
        36 => 'ছত্রিশ',
        37 => 'সাঁইত্রিশ',
        38 => 'আটত্রিশ',
        39 => 'উনচল্লিশ',
        40 => 'চল্লিশ',
        41 => 'একচল্লিশ',
        42 => 'বিয়াল্লিশ',
        43 => 'তেতাল্লিশ',
        44 => 'চুয়াল্লিশ',
        45 => 'পঁয়তাল্লিশ',
        46 => 'ছেচল্লিশ',
        47 => 'সাতচল্লিশ',
        48 => 'আটচল্লিশ',
        49 => 'উনপঞ্চাশ',
        50 => 'পঞ্চাশ',
        51 => 'একান্ন',
        52 => 'বায়ান্ন',
        53 => 'তিপ্পান্ন',
        54 => 'চুয়ান্ন',
        55 => 'পঞ্চান্ন',
        56 => 'ছাপ্পান্ন',
        57 => 'সাতান্ন',
        58 => 'আটান্ন',
        59 => 'উনষাট',
        60 => 'ষাট',
        61 => 'একষট্টি',
        62 => 'বাষট্টি',
        63 => 'তেষট্টি',
        64 => 'চৌষট্টি',
        65 => 'পঁয়ষট্টি',
        66 => 'ছেষট্টি',
        67 => 'সাতষট্টি',
        68 => 'আটষট্টি',
        69 => 'উনসত্তর',
        70 => 'সত্তর',
        71 => 'একাত্তর',
        72 => 'বাহাত্তর',
        73 => 'তিয়াত্তর',
        74 => 'চুয়াত্তর',
        75 => 'পঁচাত্তর',
        76 => 'ছিয়াত্তর',
        77 => 'সাতাত্তর',
        78 => 'আটাত্তর',
        79 => 'উনাশি',
        80 => 'আশি',
        81 => 'একাশি',
        82 => 'বিরাশি',
        83 => 'তিরাশি',
        84 => 'চুরাশি',
        85 => 'পঁচাশি',
        86 => 'ছিয়াশি',
        87 => 'সাতাশি',
        88 => 'আটাশি',
        89 => 'ঊননব্বই',
        90 => 'নব্বই',
        91 => 'একানব্বই',
        92 => 'বানব্বই',
        93 => 'তিরানব্বই',
        94 => 'চুরানব্বই',
        95 => 'পঁচানব্বই',
        96 => 'ছিয়ানব্বই',
        97 => 'সাতানব্বই',
        98 => 'আটানব্বই',
        99 => 'নিরানব্বই',
    ];

    /**
     * Short Bengali month names.
     *
     * @var array<int, string>
     */
    public static array $bnMonths = [
        1 => 'জানু',
        2 => 'ফেব্রু',
        3 => 'মার্চ',
        4 => 'এপ্রিল',
        5 => 'মে',
        6 => 'জুন',
        7 => 'জুলাই',
        8 => 'আগস্ট',
        9 => 'সেপ্ট',
        10 => 'অক্টো',
        11 => 'নভে',
        12 => 'ডিসে',
    ];

    /**
     * Convert english digits to Bengali numerals.
     */
    public static function toBn(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

        return str_replace($en, $bn, (string) $value);
    }

    /**
     * Format money with commas and Bengali digits (e.g. 81,750 => ৮১,৭৫০).
     */
    public static function toBnMoney(float|int|string|null $amount): string
    {
        $val = (float) ($amount ?? 0);
        $abs = abs($val);

        $hasDecimals = round($abs - floor($abs), 2) > 0;
        $formatted = $hasDecimals ? number_format($abs, 2) : number_format($abs, 0);

        $bn = self::toBn($formatted);

        return $val < 0 ? '-'.$bn : $bn;
    }

    /**
     * Convert monetary amount to Bengali words (e.g. 13500 => "তেরো হাজার পাঁচ শত টাকা").
     */
    public static function toBnWords(float|int|string|null $amount): string
    {
        $val = round((float) ($amount ?? 0), 2);
        if ($val == 0) {
            return 'শূন্য টাকা';
        }

        $abs = abs($val);
        $whole = (int) floor($abs);
        $fraction = (int) round(($abs - $whole) * 100);

        if ($fraction === 100) {
            $whole += 1;
            $fraction = 0;
        }

        $words = self::convertIntegerToWords($whole);
        $res = $words ? $words.' টাকা' : '';

        if ($fraction > 0) {
            $fractionWords = self::$bnWords[$fraction] ?? self::convertIntegerToWords($fraction);
            $res = ($res ? $res.' ' : '').$fractionWords.' পয়সা';
        }

        return $val < 0 ? 'মাইনাস '.$res : $res;
    }

    /**
     * Helper to recursively convert integer numbers to Bengali words.
     */
    public static function convertIntegerToWords(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        $parts = [];

        // কোটি (Crore = 1,00,00,000)
        if ($number >= 10000000) {
            $crore = intdiv($number, 10000000);
            $parts[] = self::convertIntegerToWords($crore).' কোটি';
            $number %= 10000000;
        }

        // লক্ষ (Lakh = 1,00,000)
        if ($number >= 100000) {
            $lakh = intdiv($number, 100000);
            $parts[] = (self::$bnWords[$lakh] ?? (string) $lakh).' লক্ষ';
            $number %= 100000;
        }

        // হাজার (Thousand = 1,000)
        if ($number >= 1000) {
            $thousand = intdiv($number, 1000);
            $parts[] = (self::$bnWords[$thousand] ?? (string) $thousand).' হাজার';
            $number %= 1000;
        }

        // শত (Hundred = 100)
        if ($number >= 100) {
            $hundred = intdiv($number, 100);
            $parts[] = (self::$bnWords[$hundred] ?? (string) $hundred).' শত';
            $number %= 100;
        }

        // 1 to 99
        if ($number > 0) {
            $parts[] = self::$bnWords[$number] ?? (string) $number;
        }

        return implode(' ', $parts);
    }

    /**
     * Format a DateTime object or string to Bengali format.
     * E.g. "08 সেপ্ট ২০২৬, ০৪:৩৯ PM"
     */
    public static function toBnDateTime(CarbonInterface|DateTimeInterface|string|null $date): string
    {
        if (! $date) {
            return '—';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        $day = str_pad((string) $carbon->day, 2, '0', STR_PAD_LEFT);
        $month = self::$bnMonths[$carbon->month] ?? $carbon->format('M');
        $year = self::toBn($carbon->year);

        $timeHour = self::toBn(str_pad((string) $carbon->format('h'), 2, '0', STR_PAD_LEFT));
        $timeMin = self::toBn($carbon->format('i'));
        $ampm = $carbon->format('A');

        return "{$day} {$month} {$year}, {$timeHour}:{$timeMin} {$ampm}";
    }
}
