<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;

class JsonLdValidatorRule implements SeoRule
{
    public function key(): string { return 'structured.jsonld'; }
    public function title(): string { return 'JSON-LD validation (schema.org)'; }
    public function category(): string { return 'structured'; }

    protected $supported = ['Article','Product','BreadcrumbList','FAQPage','HowTo','WebSite','Organization','Person'];

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];
        $scriptNodes = $xpath->query("//script[@type='application/ld+json']");

        $structured = [];
        for ($i = 0; $i < $scriptNodes->length; $i++) {
            $node = $scriptNodes->item($i);
            $jsonText = trim($node->textContent);
            if ($jsonText === '') continue;
            try {
                $decoded = json_decode($jsonText, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $issues[] = [
                    'rule' => $this->key(),
                    'severity' => 'error',
                    'message' => 'Invalid JSON-LD syntax: '.$e->getMessage(),
                    'selector' => 'script[type=application/ld+json]',
                    'context' => ['snippet' => substr($jsonText, 0, 200)],
                ];
                continue;
            }

            $objs = [];
            if (isset($decoded[0]) && is_array($decoded)) {
                $objs = $decoded;
            } else {
                $objs = [$decoded];
            }

            foreach ($objs as $obj) {
                $type = $obj['@type'] ?? $obj['type'] ?? null;
                if (!$type) {
                    $issues[] = [
                        'rule' => $this->key(),
                        'severity' => 'warning',
                        'message' => 'JSON-LD entity missing @type.',
                        'selector' => 'script[type=application/ld+json]',
                        'context' => ['snippet' => substr(json_encode($obj), 0, 200)],
                    ];
                    continue;
                }

                if (is_array($type)) $type = $type[0];

                if (!in_array($type, $this->supported)) {
                    $issues[] = [
                        'rule' => $this->key(),
                        'severity' => 'info',
                        'message' => "JSON-LD uses unsupported or custom @type: {$type}.",
                        'selector' => 'script[type=application/ld+json]',
                        'context' => ['type' => $type],
                    ];
                } else {
                    $missing = [];
                    if ($type === 'Article') {
                        foreach (['headline'] as $f) {
                            if (empty($obj[$f])) $missing[] = $f;
                        }
                        if (!empty($missing)) {
                            $issues[] = [
                                'rule' => $this->key(),
                                'severity' => 'warning',
                                'message' => "Article JSON-LD missing fields: ".implode(', ', $missing),
                                'selector' => 'script[type=application/ld+json]',
                                'context' => ['type' => $type, 'missing' => $missing],
                            ];
                        }
                    } elseif ($type === 'Product') {
                        foreach (['name', 'offers'] as $f) {
                            if (empty($obj[$f])) $missing[] = $f;
                        }
                        if (!empty($missing)) {
                            $issues[] = [
                                'rule' => $this->key(),
                                'severity' => 'warning',
                                'message' => "Product JSON-LD missing fields: ".implode(', ', $missing),
                                'selector' => 'script[type=application/ld+json]',
                                'context' => ['type' => $type, 'missing' => $missing],
                            ];
                        }
                    } elseif ($type === 'BreadcrumbList') {
                        if (empty($obj['itemListElement'])) {
                            $issues[] = [
                                'rule' => $this->key(),
                                'severity' => 'warning',
                                'message' => 'BreadcrumbList missing itemListElement.',
                                'selector' => 'script[type=application/ld+json]',
                                'context' => ['type' => $type],
                            ];
                        }
                    }
                }

                $structured[] = $obj;
            }
        }

        $page->structured_data = $structured;
        $page->save();

        return $issues;
    }
}
