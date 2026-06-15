<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seo_page_id',
        'rule_key',
        'severity',
        'message',
        'selector',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(SeoPage::class, 'seo_page_id');
    }
}
