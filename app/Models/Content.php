<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Content (Konten Zenner Club).
 *
 * Konten edukasi dan marketing untuk member Zenner Club.
 * Terorganisir dalam kategori hierarkis.
 *
 * @property int $id
 * @property int|null $category_id Kategori konten
 * @property string $title Judul konten
 * @property string $slug URL-friendly identifier
 * @property string|null $content Konten HTML
 * @property string|null $file Path file lampiran
 * @property string|null $vlink Video link (YouTube, dll)
 * @property string|null $status Status (published|draft)
 * @property int|null $created_by User yang membuat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Content extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'file',
        'vlink',
        'status',
        'created_by',
    ];

    /**
     * Kategori konten.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    /**
     * User admin yang membuat konten.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
