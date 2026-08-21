<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccuracyAuditSample extends Model
{
    use HasFactory;

    public const STATUS_BELUM = 'Belum Diaudit';

    public const STATUS_BENAR = 'Benar';

    public const STATUS_SALAH = 'Salah';

    public const STATUS_PERLU_VERIFIKASI = 'Perlu Verifikasi';

    protected $fillable = [
        'alumni_id',
        'sample_order',
        'status_audit',
        'catatan_audit',
        'audited_by',
        'audited_at',
    ];

    protected function casts(): array
    {
        return [
            'sample_order' => 'integer',
            'audited_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BENAR,
            self::STATUS_SALAH,
            self::STATUS_PERLU_VERIFIKASI,
        ];
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'audited_by'
        );
    }
}