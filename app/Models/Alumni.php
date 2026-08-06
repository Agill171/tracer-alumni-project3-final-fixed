<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumni extends Model
{
    use HasFactory;

    public const STATUS_BELUM_DILACAK = 'Belum Dilacak';

    public const STATUS_TERIDENTIFIKASI = 'Teridentifikasi dari Sumber Publik';

    public const STATUS_PERLU_VERIFIKASI = 'Perlu Verifikasi Manual';

    public const STATUS_TIDAK_DITEMUKAN = 'Belum Ditemukan di Sumber Publik';

    public const KATEGORI_PNS = 'PNS';

    public const KATEGORI_SWASTA = 'Swasta';

    public const KATEGORI_WIRAUSAHA = 'Wirausaha';

    protected $fillable = [
        'nama',
        'nim',
        'prodi',
        'angkatan',
        'tahun_lulus',
        'linkedin',
        'instagram',
        'facebook',
        'tiktok',
        'email',
        'no_hp',
        'tempat_bekerja',
        'alamat_bekerja',
        'posisi',
        'kategori_pekerjaan',
        'sosmed_tempat_bekerja',
        'status_verifikasi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tahun_lulus' => 'integer',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BELUM_DILACAK,
            self::STATUS_TERIDENTIFIKASI,
            self::STATUS_PERLU_VERIFIKASI,
            self::STATUS_TIDAK_DITEMUKAN,
        ];
    }

    public static function kategoriPekerjaanOptions(): array
    {
        return [
            self::KATEGORI_PNS,
            self::KATEGORI_SWASTA,
            self::KATEGORI_WIRAUSAHA,
        ];
    }

    public function hasilPelacakans(): HasMany
    {
        return $this->hasMany(HasilPelacakan::class);
    }

    public function queryPelacakans(): HasMany
    {
        return $this->hasMany(PelacakanQuery::class);
    }
}
