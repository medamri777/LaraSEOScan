<?php
namespace App\Seo\Rules;

use App\Models\SeoPage;
use GuzzleHttp\Client;

class OpenGraphRule implements SeoRule
{
    public function key(): string { return 'og.basic_presence'; }
    public function title(): string { return 'Open Graph & Twitter card checks'; }
    public function category(): string { return 'og'; }

    public function check(SeoPage $page, \DOMDocument $dom, \DOMXPath $xpath): array
    {
        $issues = [];

        $required = ['og:title', 'og:description', 'og:image'];
        foreach ($required as $prop) {
            $node = $xpath->query("//meta[@property='{$prop}']")->item(0);
            if ($node instanceof \DOMElement) {
                if (trim($node->getAttribute('content')) === '') {

                    $issues[] = [
                        'rule' => $this->key(),
                        'severity' => 'warning',
                        'message' => "{$prop} is missing or empty.",
                        'selector' => "meta[property={$prop}]",
                        'context' => [],
                    ];
                }
            }
        }

        $imgNode = $xpath->query("//meta[@property='og:image']")->item(0);
        if ($imgNode instanceof \DOMElement) {
            $imgUrl = $imgNode->getAttribute('content');
            if ($imgUrl) {
                try {
                    $client = new Client(['timeout' => 5.0, 'verify' => false]);
                    $res = $client->request('GET', $imgUrl, ['stream' => true]);
                    $tmp = tmpfile();
                    $meta = stream_get_meta_data($tmp);
                    $tmpPath = $meta['uri'];
                    while (!$res->getBody()->eof()) {
                        fwrite($tmp, $res->getBody()->read(1024));
                    }
                    rewind($tmp);
                    $imgSize = @getimagesize($tmpPath);
                    if ($imgSize && is_array($imgSize)) {
                        [$width, $height] = [$imgSize[0], $imgSize[1]];
                        if ($width < 600 || $height < 315) {
                            $issues[] = [
                                'rule' => $this->key(),
                                'severity' => 'warning',
                                'message' => "og:image is small ({$width}x{$height}). Recommended 1200×630 or at least 600×315.",
                                'selector' => "meta[property='og:image']",
                                'context' => ['width' => $width, 'height' => $height, 'url' => $imgUrl],
                            ];
                        }
                    } else {
                        $issues[] = [
                            'rule' => $this->key(),
                            'severity' => 'info',
                            'message' => 'Could not determine og:image dimensions.',
                            'selector' => "meta[property='og:image']",
                            'context' => ['url' => $imgUrl],
                        ];
                    }
                    fclose($tmp);
                } catch (\Throwable $e) {
                    $issues[] = [
                        'rule' => $this->key(),
                        'severity' => 'info',
                        'message' => 'Failed to fetch og:image for size check: '.$e->getMessage(),
                        'selector' => "meta[property='og:image']",
                        'context' => ['url' => $imgUrl],
                    ];
                }
            }
        }

        $ogNodes = $xpath->query("//meta[starts-with(@property,'og:')]");
        $seen = [];
        foreach ($ogNodes as $node) {
            if ($node instanceof \DOMElement) {
                $prop = $node->getAttribute('property');
                if (isset($seen[$prop])) {
                    $issues[] = [
                        'rule' => $this->key(),
                        'severity' => 'warning',
                        'message' => "Multiple meta tags for {$prop} found.",
                        'selector' => "meta[property='{$prop}']",
                        'context' => [],
                    ];
                }
            }
            $seen[$prop] = true;
        }

        return $issues;
    }
}
