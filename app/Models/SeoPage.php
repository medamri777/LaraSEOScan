<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoPage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'seo_scan_id',
        'url',
        'title',
        'description',
        'canonical',
        'robots',
        'headings',
        'status_code',
        'word_count',
        'shingle_signature',
        'structured_data',
        'fetched_at',
        'keyword_density',
        'image_total_size',
        'image_unoptimized_count',
        'keyword_analysis',
        'target_keyword',
        'redirect_history',
        'og_tags',
        'twitter_tags',
        'hreflangs',
        'content_type',
        'server',
        'x_powered_by',
        'content_length',
        'lang',
        'viewport',
        'favicon',
        'author',
        'generator',
        'x_robots_tag',
        'discovery_source',
        'response_time_ms',
        'depth',
    ];

    protected $casts = [
        'headings' => 'array',
        'structured_data' => 'array',
        'keyword_density' => 'array',
        'keyword_analysis' => 'array',
        'redirect_history' => 'array',
        'og_tags' => 'array',
        'twitter_tags' => 'array',
        'hreflangs' => 'array',
        'fetched_at' => 'datetime',
    ];

    public function scan()
    {
        return $this->belongsTo(SeoScan::class, 'seo_scan_id');
    }
    public function seoscan()
    {
        return $this->belongsTo(SeoScan::class, 'seo_scan_id');
    }

    public function links()
    {
        return $this->hasMany(SeoLink::class);
    }

    public function images()
    {
        return $this->hasMany(SeoImage::class);
    }

    public function issues()
    {
        return $this->hasMany(SeoIssue::class, 'seo_page_id');
    }

    public function keywords()
    {
        return $this->hasMany(SeoKeyword::class, 'seo_page_id');
    }
}
