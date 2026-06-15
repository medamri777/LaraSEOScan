<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'seo_page_id',
        'src',
        'alt',
        'has_alt',
        'is_empty_alt',
        'loading',
        'width',
        'height',
    ];

    public function page()
    {
        return $this->belongsTo(SeoPage::class, 'seo_page_id');
    }
}
