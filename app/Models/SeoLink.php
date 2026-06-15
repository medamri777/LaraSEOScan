<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoLink extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'seo_page_id',
        'href',
        'anchor_text',
        'status_code',
        'is_internal',
        'rel',
        'is_nofollow',
        'redirect_chain',
    ];

    protected $casts = [
        'redirect_chain' => 'array',
        'is_internal' => 'boolean',
        'is_nofollow' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(SeoPage::class, 'seo_page_id');
    }
}
