<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoKeyword extends Model
{
    protected $fillable = [
        'seo_page_id',
        'seo_scan_id',
        'keyword',
        'occurrences',
        'density',
        'rake_score',
        'in_title',
        'in_h1',
        'in_meta_description',
        'in_headings',
        'in_first_paragraph',
        'in_image_alt',
        'placement_score',
        'type',
    ];

    protected $casts = [
        'occurrences' => 'integer',
        'density' => 'decimal:2',
        'rake_score' => 'integer',
        'in_title' => 'boolean',
        'in_h1' => 'boolean',
        'in_meta_description' => 'boolean',
        'in_headings' => 'boolean',
        'in_first_paragraph' => 'boolean',
        'in_image_alt' => 'boolean',
        'placement_score' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SeoPage::class, 'seo_page_id');
    }
}
