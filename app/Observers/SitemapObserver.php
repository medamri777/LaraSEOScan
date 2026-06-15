<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class SitemapObserver
{
    protected array $relevantFields = [
        'loc', 'changefreq', 'priority', 'lastmod', 'is_active', 'image_url',
    ];

    public function created(Model $model): void
    {
        $this->touchSitemap();
    }

    public function updated(Model $model): void
    {
        if ($this->hasRelevantChanges($model)) {
            $this->touchSitemap();
        }
    }

    public function deleted(Model $model): void
    {
        $this->touchSitemap();
    }

    protected function hasRelevantChanges(Model $model): bool
    {
        if (!method_exists($model, 'getDirty')) return true;
        $dirty = $model->getDirty();
        foreach ($this->relevantFields as $field) {
            if (array_key_exists($field, $dirty)) return true;
        }
        return false;
    }

    protected function touchSitemap(): void
    {
        if (app()->runningInConsole()) return;
        try {
            app(\App\Services\SitemapService::class)->clearCache();
        } catch (\Throwable $e) {
        }
    }
}
