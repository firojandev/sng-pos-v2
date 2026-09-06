<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Shop\Models\Shop;

class AuditLog extends Model
{
    use BelongsToShop;

    const UPDATED_AT = null;

    protected $fillable = [
        'shop_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Map of auditable model classes to bilingual labels, icons, and theme colors.
     *
     * @return array<string, array{bn: string, en: string, icon: string, color: string}>
     */
    public static function auditableModels(): array
    {
        return [
            'Modules\Sales\Models\Sale' => [
                'bn' => 'বিক্রয়',
                'en' => 'Sale',
                'icon' => 'shopping-cart',
                'color' => 'teal',
            ],
            'Modules\Sales\Models\SaleReturn' => [
                'bn' => 'বিক্রয় ফেরত',
                'en' => 'Sale Return',
                'icon' => 'rotate-ccw',
                'color' => 'gold',
            ],
            'Modules\Purchase\Models\Purchase' => [
                'bn' => 'ক্রয়',
                'en' => 'Purchase',
                'icon' => 'shopping-bag',
                'color' => 'blue',
            ],
            'Modules\Purchase\Models\PurchaseReturn' => [
                'bn' => 'ক্রয় ফেরত',
                'en' => 'Purchase Return',
                'icon' => 'rotate-left',
                'color' => 'gold',
            ],
            'Modules\Purchase\Models\PurchaseDeliveryOrder' => [
                'bn' => 'ডেলিভারি অর্ডার (DO)',
                'en' => 'Delivery Order',
                'icon' => 'truck',
                'color' => 'blue',
            ],
            'Modules\Purchase\Models\PurchaseDeliveryReceipt' => [
                'bn' => 'চালান প্রাপ্তি',
                'en' => 'Delivery Receipt',
                'icon' => 'file-check',
                'color' => 'teal',
            ],
            'Modules\Finance\Models\Income' => [
                'bn' => 'আয়',
                'en' => 'Income',
                'icon' => 'trending-up',
                'color' => 'green',
            ],
            'Modules\Finance\Models\Expense' => [
                'bn' => 'ব্যয়',
                'en' => 'Expense',
                'icon' => 'trending-down',
                'color' => 'red',
            ],
            'Modules\Finance\Models\Account' => [
                'bn' => 'ব্যাংক/অ্যাকাউন্ট',
                'en' => 'Account',
                'icon' => 'credit-card',
                'color' => 'blue',
            ],
            'Modules\Finance\Models\AccountTransfer' => [
                'bn' => 'ফান্ড স্থানান্তর',
                'en' => 'Fund Transfer',
                'icon' => 'repeat',
                'color' => 'teal',
            ],
            'Modules\Finance\Models\AccountTransaction' => [
                'bn' => 'অ্যাকাউন্ট লেনদেন',
                'en' => 'Account Transaction',
                'icon' => 'arrow-left-right',
                'color' => 'grey',
            ],
            'Modules\Cashbox\Models\CashTransaction' => [
                'bn' => 'ক্যাশ লেনদেন',
                'en' => 'Cash Transaction',
                'icon' => 'dollar-sign',
                'color' => 'gold',
            ],
            'Modules\Product\Models\StockTransfer' => [
                'bn' => 'স্টক স্থানান্তর',
                'en' => 'Stock Transfer',
                'icon' => 'refresh-cw',
                'color' => 'purple',
            ],
            'Modules\Product\Models\Product' => [
                'bn' => 'পণ্য',
                'en' => 'Product',
                'icon' => 'package',
                'color' => 'teal',
            ],
            'Modules\Customer\Models\Customer' => [
                'bn' => 'গ্রাহক',
                'en' => 'Customer',
                'icon' => 'user',
                'color' => 'green',
            ],
            'Modules\Supplier\Models\Supplier' => [
                'bn' => 'সরবরাহকারী',
                'en' => 'Supplier',
                'icon' => 'briefcase',
                'color' => 'blue',
            ],
            'Modules\Employee\Models\Employee' => [
                'bn' => 'কর্মচারী',
                'en' => 'Employee',
                'icon' => 'user-check',
                'color' => 'purple',
            ],
            'App\Models\User' => [
                'bn' => 'ইউজার অ্যাকাউন্ট',
                'en' => 'User Account',
                'icon' => 'users',
                'color' => 'teal',
            ],
        ];
    }

    /**
     * Get label, icon, and color configuration for this log's auditable model.
     *
     * @return array{bn: string, en: string, icon: string, color: string}
     */
    public function modelMeta(): array
    {
        $models = static::auditableModels();

        return $models[$this->auditable_type] ?? [
            'bn' => class_basename($this->auditable_type),
            'en' => class_basename($this->auditable_type),
            'icon' => 'file-text',
            'color' => 'grey',
        ];
    }

    /**
     * Bengali/English labels for each audit action.
     *
     * @return array<string, array{bn: string, en: string, color: string}>
     */
    public static function actionLabels(): array
    {
        return [
            'created' => ['bn' => 'নতুন তৈরি', 'en' => 'Created', 'color' => 'green'],
            'updated' => ['bn' => 'হালনাগাদ', 'en' => 'Updated', 'color' => 'gold'],
            'deleted' => ['bn' => 'মুছে ফেলা', 'en' => 'Deleted', 'color' => 'danger'],
            'restored' => ['bn' => 'পুনরুদ্ধার', 'en' => 'Restored', 'color' => 'teal'],
        ];
    }

    public function actionLabel(): array
    {
        return static::actionLabels()[$this->action] ?? ['bn' => $this->action, 'en' => $this->action, 'color' => 'grey'];
    }

    /**
     * Parse human-readable browser & device info from User Agent string.
     *
     * @return array{browser: string, platform: string, is_mobile: bool}
     */
    public function browserAndDevice(): array
    {
        $ua = $this->user_agent;
        if (! $ua) {
            return ['browser' => 'System / Unknown', 'platform' => 'Unknown', 'is_mobile' => false];
        }

        $platform = 'Unknown OS';
        if (preg_match('/windows|win32/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $platform = 'iOS';
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        }

        $browser = 'Unknown Browser';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $ua)) {
            $browser = 'Opera';
        }

        $isMobile = (bool) preg_match('/mobile|android|touch|silk|kindle/i', $ua);

        return [
            'browser' => $browser,
            'platform' => $platform,
            'is_mobile' => $isMobile,
        ];
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
