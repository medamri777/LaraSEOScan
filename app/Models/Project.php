<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'name', 'url', 'description'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scans()
    {
        return $this->hasMany(SeoScan::class);
    }

    public function keywords()
    {
        return $this->hasMany(Keyword::class);
    }

    public function rankCheckBatches()
    {
        return $this->hasMany(RankCheckBatch::class);
    }

    public function competitors()
    {
        return $this->hasMany(ProjectCompetitor::class);
    }

    public function gscConnection()
    {
        return $this->hasOne(GscConnection::class);
    }
}
