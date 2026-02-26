<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model ContentCategory (Kategori Konten Zenner Club).
 *
 * Kategori hierarkis untuk konten Zenner Club
 * (Marketing Kit, Academy, Leaderboard, Rules & FAQ, dll).
 *
 * @property int $id
 * @property int|null $parent_id Kategori induk
 * @property string $name Nama kategori
 * @property string $slug URL-friendly identifier
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ContentCategory extends BaseModel
{
    use HasFactory;

    protected $table = 'contents_category';

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
    ];

    /**
     * Kategori induk.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Sub-kategori.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Konten dalam kategori ini.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'category_id');
    }
}
