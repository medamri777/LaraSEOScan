<?php

return [
    'robots' => [
        'cache_ttl' => env('ROBOTS_CACHE_TTL', 86400),
        'default_sitemap_url' => env('APP_URL') . '/sitemap.xml',
    ],

    'sitemap' => [
        'cache_ttl' => env('SITEMAP_CACHE_TTL', 3600),
        'gzip' => env('SITEMAP_GZIP', false),
        'ping_on_generate' => env('SITEMAP_PING', true),
        'models' => [
            // 'model_class' => ['changefreq' => 'weekly', 'priority' => 0.8],
        ],
        'default_changefreq' => 'weekly',
        'default_priority' => 0.5,
    ],

    'rules' => [
        \App\Seo\Rules\MissingTitleRule::class            => true,
        \App\Seo\Rules\MetaDescriptionRule::class         => true,
        \App\Seo\Rules\H1Rule::class                      => true,
        \App\Seo\Rules\CanonicalRule::class               => true,
        \App\Seo\Rules\HttpsRule::class                   => true,
        \App\Seo\Rules\ContentLengthRule::class            => true,
        \App\Seo\Rules\BrokenLinkRule::class              => true,
        \App\Seo\Rules\ImageOptimizationRule::class       => true,
        \App\Seo\Rules\InternalLinkingRule::class         => true,
        \App\Seo\Rules\OpenGraphRule::class               => true,
        \App\Seo\Rules\HreflangRule::class                => true,
        \App\Seo\Rules\JsonLdValidatorRule::class         => true,
        \App\Seo\Rules\KeywordDensityRule::class          => true,
        \App\Seo\Rules\MobileViewportRule::class          => true,
        \App\Seo\Rules\RedirectChainRule::class           => true,
        \App\Seo\Rules\RobotsTxtValidationRule::class    => true,
        \App\Seo\Rules\ShingleDuplicateRule::class        => true,
    ],
];
