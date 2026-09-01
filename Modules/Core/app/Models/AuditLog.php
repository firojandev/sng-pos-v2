<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToShop;

class AuditLog extends Model
{
    use BelongsToShop;

    const UPDATED_AT = null;

    protected $fillable = [
        'shop_id', 'user_id', 'auditable_type', 'auditable_id', 'action', 'old_values', 'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Bengali/English labels for each audit action.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function actionLabels(): array
    {
        return [
            'created' => ['bn' => 'তৈরি', 'en' => 'Created'],
            'updated' => ['bn' => 'হালনাগাদ', 'en' => 'Updated'],
            'deleted' => ['bn' => 'বাতিল/মুছে ফেলা', 'en' => 'Deleted'],
            'restored' => ['bn' => 'পুনরুদ্ধার', 'en' => 'Restored'],
        ];
    }

    public function actionLabel(): array
    {
        return static::actionLabels()[$this->action] ?? ['bn' => $this->action, 'en' => $this->action];
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
