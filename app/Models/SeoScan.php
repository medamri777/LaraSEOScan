<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoScan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'url', 'status', 'user_id', 'has_robots_txt', 'has_sitemap_xml', 'uuid', 'project_id',
        'score_total', 'score_technical', 'score_on_page', 'score_local', 'score_mobile', 'score_speed',
        'pagespeed_performance', 'pagespeed_seo', 'pagespeed_accessibility', 'pagespeed_best_practices',
        'core_web_vitals', 'pagespeed_opportunities', 'time_elapsed', 'total_urls_found', 'crawled_metrics',
        'crawl_config',
    ];

    protected $casts = [
        'core_web_vitals' => 'array',
        'pagespeed_opportunities' => 'array',
        'crawled_metrics' => 'array',
        'crawl_config' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($scan) {
            if (empty($scan->uuid)) {
                $scan->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function pages()
    {
        return $this->hasMany(SeoPage::class, 'seo_scan_id');
    }

    public function scopeTodayByUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
