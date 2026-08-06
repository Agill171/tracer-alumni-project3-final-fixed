<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilPelacakan extends Model
{
    use HasFactory;

    public const KATEGORI_KUAT = 'Kemungkinan Kuat';

    public const KATEGORI_VERIFIKASI = 'Perlu Verifikasi';

    public const KATEGORI_TIDAK_COCOK = 'Tidak Cocok';

    protected $fillable = [
        'alumni_id',
        'user_id',
        'status_pelacakan',
        'judul_temuan',
        'sumber_temuan',
        'link_bukti',
        'query_pencarian',
        'ringkasan_hasil',
        'tanggal_ditemukan',
        'confidence_score',
        'kategori_kecocokan',
        'sinyal_identitas',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_ditemukan' => 'date',
            'confidence_score' => 'integer',
            'sinyal_identitas' => 'array',
        ];
    }

    public static function kategoriKecocokanOptions(): array
    {
        return [
            self::KATEGORI_KUAT,
            self::KATEGORI_VERIFIKASI,
            self::KATEGORI_TIDAK_COCOK,
        ];
    }

    public static function classify(?int $score): ?string
    {
        if ($score === null) {
            return null;
        }

        return match (true) {
            $score >= 80 => self::KATEGORI_KUAT,
            $score >= 50 => self::KATEGORI_VERIFIKASI,
            default => self::KATEGORI_TIDAK_COCOK,
        };
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
