<?php
namespace App\Seo\Rules;

class Registry
{
    public static function all(): array
    {
        $rules = config('seo.rules', []);
        $enabled = [];

        foreach ($rules as $class => $active) {
            if ($active && class_exists($class)) {
                $enabled[] = app($class);
            }
        }

        return $enabled;
    }
}
