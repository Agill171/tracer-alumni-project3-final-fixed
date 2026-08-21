<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelacakanKandidat extends Model
{
    use HasFactory;

    public const STATUS_KANDIDAT =
        'Kandidat';

    public const STATUS_KUAT =
        'Kemungkinan Kuat';

    public const STATUS_REVIEW =
        'Perlu Verifikasi';

    public const STATUS_TIDAK_COCOK =
        'Tidak Cocok';


    protected $fillable = [
        'pelacakan_batch_id',
        'pelacakan_batch_item_id',
        'alumni_id',
        'pelacakan_query_id',

        'provider',
        'source_key',
        'rank',

        'title',
        'url',
        'url_hash',
        'snippet',
        'domain',

        'identity_signals',

        'base_confidence_score',
        'confidence_score',

        'kategori_kecocokan',
        'project4_candidates',

        'status',
        'searched_at',
    ];


    protected function casts(): array
    {
        return [
            'rank' =>
                'integer',

            'base_confidence_score' =>
                'integer',

            'confidence_score' =>
                'integer',

            'identity_signals' =>
                'array',

            'project4_candidates' =>
                'array',

            'searched_at' =>
                'datetime',
        ];
    }


    public function batch(): BelongsTo
    {
        return $this->belongsTo(
            PelacakanBatch::class,
            'pelacakan_batch_id'
        );
    }


    public function batchItem(): BelongsTo
    {
        return $this->belongsTo(
            PelacakanBatchItem::class,
            'pelacakan_batch_item_id'
        );
    }


    public function alumni(): BelongsTo
    {
        return $this->belongsTo(
            Alumni::class
        );
    }


    public function pelacakanQuery(): BelongsTo
    {
        return $this->belongsTo(
            PelacakanQuery::class,
            'pelacakan_query_id'
        );
    }
}