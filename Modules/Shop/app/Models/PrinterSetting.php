<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToShop;

class PrinterSetting extends Model
{
    use BelongsToShop;

    public const TYPE_A4 = 'a4';

    public const TYPE_A5 = 'a5';

    public const TYPE_THERMAL = 'thermal';

    public const ORIENTATION_PORTRAIT = 'portrait';

    public const ORIENTATION_LANDSCAPE = 'landscape';

    public const UNIT_MM = 'mm';

    public const UNIT_INCH = 'inch';

    /**
     * Standard predefined paper dimensions in millimeters.
     */
    public const PREDEFINED_DIMENSIONS = [
        self::TYPE_A4 => [
            'width' => 210.0,
            'height' => 297.0,
            'unit' => self::UNIT_MM,
        ],
        self::TYPE_A5 => [
            'width' => 148.0,
            'height' => 210.0,
            'unit' => self::UNIT_MM,
        ],
    ];

    /**
     * Common thermal paper width presets.
     */
    public const THERMAL_WIDTH_PRESETS = [
        58 => '58 mm',
        80 => '80 mm',
        100 => '100 mm',
    ];

    /**
     * @var string
     */
    protected $table = 'printer_settings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shop_id',
        'printer_type',
        'orientation',
        'paper_width',
        'paper_height',
        'unit',
        'page_margin',
        'auto_print',
        'show_header_logo',
        'show_shop_info',
        'show_customer_due',
        'show_footer_note',
        'print_copies',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'paper_width' => 'float',
        'paper_height' => 'float',
        'page_margin' => 'float',
        'auto_print' => 'boolean',
        'show_header_logo' => 'boolean',
        'show_shop_info' => 'boolean',
        'show_customer_due' => 'boolean',
        'show_footer_note' => 'boolean',
        'print_copies' => 'integer',
    ];

    /**
     * Relationship to Shop.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    /**
     * Check if printer is thermal.
     */
    public function isThermal(): bool
    {
        return $this->printer_type === self::TYPE_THERMAL;
    }

    /**
     * Check if printer is A4.
     */
    public function isA4(): bool
    {
        return $this->printer_type === self::TYPE_A4;
    }

    /**
     * Check if printer is A5.
     */
    public function isA5(): bool
    {
        return $this->printer_type === self::TYPE_A5;
    }

    /**
     * Check if orientation is landscape.
     */
    public function isLandscape(): bool
    {
        return $this->orientation === self::ORIENTATION_LANDSCAPE;
    }

    /**
     * Get or build default printer settings for a shop.
     */
    public static function getDefaultForShop(int $shopId): self
    {
        $setting = static::where('shop_id', $shopId)->first();

        if ($setting) {
            return $setting;
        }

        return static::create([
            'shop_id' => $shopId,
            'printer_type' => self::TYPE_A4,
            'orientation' => self::ORIENTATION_PORTRAIT,
            'paper_width' => 210.0,
            'paper_height' => 297.0,
            'unit' => self::UNIT_MM,
            'page_margin' => 8.0,
            'auto_print' => false,
            'show_header_logo' => true,
            'show_shop_info' => true,
            'show_customer_due' => true,
            'show_footer_note' => true,
            'print_copies' => 1,
        ]);
    }

    /**
     * Generate CSS @page size string for print stylesheet.
     */
    public function getCssPageSize(): string
    {
        $unit = $this->unit ?: self::UNIT_MM;

        if ($this->isA4()) {
            return $this->isLandscape() ? '297mm 210mm landscape' : '210mm 297mm portrait';
        }

        if ($this->isA5()) {
            return $this->isLandscape() ? '210mm 148mm landscape' : '148mm 210mm portrait';
        }

        // Thermal Printer
        $widthStr = ($this->paper_width ?: 80).$unit;
        $heightStr = $this->paper_height ? ($this->paper_height.$unit) : 'auto';

        return "{$widthStr} {$heightStr}";
    }

    /**
     * Get CSS width for sheet container preview or print.
     */
    public function getCssPaperWidth(): string
    {
        $unit = $this->unit ?: self::UNIT_MM;

        if ($this->isA4()) {
            return $this->isLandscape() ? '297mm' : '210mm';
        }

        if ($this->isA5()) {
            return $this->isLandscape() ? '210mm' : '148mm';
        }

        return ($this->paper_width ?: 80).$unit;
    }
}
