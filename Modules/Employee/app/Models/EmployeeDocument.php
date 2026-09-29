<?php

namespace Modules\Employee\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A file kept on an employee's record (NID copy, certificates, contract).
 * Files are stored privately and downloaded through the app.
 */
class EmployeeDocument extends Model
{
    use BelongsToCompany;

    public const DISK = 'local';

    /**
     * @var array<string, string>
     */
    public const TYPES = [
        'nid' => 'জাতীয় পরিচয়পত্র (NID)',
        'photo' => 'ছবি (Photo)',
        'certificate' => 'সনদ (Certificate)',
        'contract' => 'নিয়োগপত্র / চুক্তি (Appointment / Contract)',
        'cv' => 'জীবনবৃত্তান্ত (CV)',
        'other' => 'অন্যান্য (Other)',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (EmployeeDocument $document) => Storage::disk(self::DISK)->delete($document->file_path));
    }

    protected $fillable = ['company_id', 'employee_id', 'title', 'type', 'file_path', 'original_name', 'mime_type', 'size', 'expires_on', 'uploaded_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['expires_on' => 'date', 'size' => 'integer'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
