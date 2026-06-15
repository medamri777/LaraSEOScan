<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = [
        'page_id', 'rule_key', 'severity', 'message', 'selector', 'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function seoPage()
    {
        return $this->belongsTo(SeoPage::class, 'page_id');
    }
}
