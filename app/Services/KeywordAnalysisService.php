<?php

namespace App\Services;

use DonatelloZa\RakePlus\RakePlus;
use Symfony\Component\DomCrawler\Crawler;

class KeywordAnalysisService
{
    protected array $stopWords = [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
        'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need',
        'dare', 'ought', 'used', 'it', 'its', 'this', 'that', 'these', 'those',
        'i', 'you', 'he', 'she', 'we', 'they', 'what', 'which', 'who', 'whom',
        'whose', 'where', 'when', 'why', 'how', 'all', 'each', 'every', 'both',
        'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not',
        'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'about',
        'above', 'below', 'between', 'into', 'through', 'during', 'before',
        'after', 'up', 'down', 'out', 'off', 'over', 'under', 'again', 'further',
        'then', 'once', 'here', 'there', 'any', 'if', 'because', 'as', 'until',
        'while', 's', 't', 'd', 'm', 're', 've', 'll', 'don', 'ain', 'aren',
        'couldn', 'didn', 'doesn', 'hadn', 'hasn', 'haven', 'isn', 'mightn',
        'mustn', 'needn', 'shan', 'shouldn', 'wasn', 'weren', 'won', 'wouldn',
    ];

    public function analyzePage(string $html, ?string $targetKeyword = null): array
    {
        $crawler = new Crawler($html);

        $bodyText = $this->extractBodyText($crawler);
        $titleText = $this->extractTitle($crawler);
        $h1Text = $this->extractH1($crawler);
        $metaDescription = $this->extractMetaDescription($crawler);
        $headings = $this->extractAllHeadings($crawler);

        $rakeKeywords = $this->extractKeywords($bodyText);
        $densityData = $this->calculateDensity($bodyText);

        $keywordPlacement = [];
        if ($targetKeyword) {
            $keywordPlacement = $this->checkKeywordPlacement(
                $targetKeyword,
                $titleText,
                $h1Text,
                $metaDescription,
                $headings,
                $bodyText
            );
        }

        $topKeywords = array_slice($rakeKeywords, 0, 20, true);

        return [
            'target_keyword' => $targetKeyword,
            'keyword_placement' => $keywordPlacement,
            'top_keywords' => $topKeywords,
            'density' => $densityData,
            'body_text' => $bodyText,
            'content_stats' => [
                'word_count' => str_word_count($bodyText),
                'title_length' => mb_strlen($titleText),
                'h1_text' => $h1Text,
                'meta_description_length' => mb_strlen($metaDescription),
            ],
        ];
    }

    public function extractKeywords(string $text, int $limit = 50): array
    {
        if (empty(trim($text))) {
            return [];
        }

        try {
            $rake = RakePlus::create($text, 'en_US', 3);
            $keywords = $rake->get();

            $filtered = [];
            foreach ($keywords as $index => $phrase) {
                $cleanPhrase = trim(strtolower($phrase));
                
                // Skip phrases that are too long or too short
                if (mb_strlen($cleanPhrase) < 3 || mb_strlen($cleanPhrase) > 100) {
                    continue;
                }

                $words = explode(' ', $cleanPhrase);
                
                // Skip phrases with too many words (likely navigation/footer noise)
                if (count($words) > 6) {
                    continue;
                }

                // Skip phrases containing UI/navigation symbols
                if (preg_match('/[•·|\/\\\\→←↑↓]/', $cleanPhrase)) {
                    continue;
                }

                // Skip phrases that are mostly symbols or numbers
                if (preg_match('/^[\d\s\W]+$/', $cleanPhrase)) {
                    continue;
                }

                $hasMeaningfulWord = false;
                foreach ($words as $word) {
                    if (!in_array($word, $this->stopWords) && mb_strlen($word) > 2) {
                        $hasMeaningfulWord = true;
                        break;
                    }
                }

                if ($hasMeaningfulWord) {
                    $filtered[$cleanPhrase] = count($words) * 2;
                }
            }

            arsort($filtered);
            return array_slice($filtered, 0, $limit, true);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function calculateDensity(string $text): array
    {
        $words = preg_split('/\s+/', strtolower(trim($text)));
        $words = array_filter($words, function ($word) {
            return mb_strlen($word) > 2 && !in_array($word, $this->stopWords);
        });

        $totalWords = count($words);
        if ($totalWords === 0) {
            return ['total_words' => 0, 'unique_words' => 0, 'density_map' => []];
        }

        $frequency = array_count_values($words);
        arsort($frequency);

        $densityMap = [];
        foreach (array_slice($frequency, 0, 50, true) as $word => $count) {
            $densityMap[$word] = [
                'count' => $count,
                'density' => round(($count / $totalWords) * 100, 2),
            ];
        }

        // Store total words for phrase density calculation later
        $densityMap['_total_words'] = $totalWords;

        return [
            'total_words' => $totalWords,
            'unique_words' => count(array_unique($words)),
            'density_map' => $densityMap,
        ];
    }

    public function calculatePhraseDensity(string $phrase, string $text, int $totalWords): array
    {
        $phraseLower = strtolower(trim($phrase));
        $textLower = strtolower($text);
        
        $count = substr_count($textLower, $phraseLower);
        $density = $totalWords > 0 ? round(($count / $totalWords) * 100, 2) : 0;
        
        return [
            'count' => $count,
            'density' => $density,
        ];
    }

    public function checkKeywordPlacement(
        string $keyword,
        string $title,
        string $h1,
        string $metaDescription,
        array $headings,
        string $bodyText
    ): array {
        $keywordLower = strtolower(trim($keyword));

        $titleContains = stripos($title, $keywordLower) !== false;
        $h1Contains = stripos($h1, $keywordLower) !== false;
        $metaContains = stripos($metaDescription, $keywordLower) !== false;

        $headingContains = false;
        foreach ($headings as $heading) {
            if (stripos($heading['text'], $keywordLower) !== false) {
                $headingContains = true;
                break;
            }
        }

        $bodyCount = substr_count(strtolower($bodyText), $keywordLower);
        $bodyWordCount = str_word_count($bodyText);
        $keywordDensity = $bodyWordCount > 0 ? round(($bodyCount / $bodyWordCount) * 100, 2) : 0;

        $firstParagraph = $this->getFirstParagraph($bodyText);
        $inFirstParagraph = stripos($firstParagraph, $keywordLower) !== false;

        $lastParagraph = $this->getLastParagraph($bodyText);
        $inLastParagraph = stripos($lastParagraph, $keywordLower) !== false;

        $imageAltContains = $this->checkImageAltForKeyword($bodyText, $keywordLower);

        $urlContains = false;

        $score = $this->calculatePlacementScore(
            $titleContains,
            $h1Contains,
            $metaContains,
            $headingContains,
            $inFirstParagraph,
            $inLastParagraph,
            $imageAltContains,
            $keywordDensity
        );

        return [
            'in_title' => $titleContains,
            'in_h1' => $h1Contains,
            'in_meta_description' => $metaContains,
            'in_headings' => $headingContains,
            'in_first_paragraph' => $inFirstParagraph,
            'in_last_paragraph' => $inLastParagraph,
            'in_image_alt' => $imageAltContains,
            'in_url' => $urlContains,
            'occurrences' => $bodyCount,
            'density' => $keywordDensity,
            'score' => $score,
        ];
    }

    protected function extractBodyText(Crawler $crawler): string
    {
        try {
            // Remove non-content elements first
            $crawler->filter('script, style, noscript, iframe, nav, footer, header, aside, form, button, input, select, textarea, svg, path, link[rel="stylesheet"], meta')->each(function ($node) {
                foreach ($node as $domNode) {
                    if ($domNode->parentNode) {
                        $domNode->parentNode->removeChild($domNode);
                    }
                }
            });

            // Remove donation banners and fundraising content
            $crawler->filterXPath('//*[@class[contains(., "banner") or contains(., "donate") or contains(., "fundraiser") or contains(., "centralnotice")] or @id[contains(., "banner") or contains(., "donate") or contains(., "fundraiser") or contains(., "centralnotice")]]')->each(function ($node) {
                foreach ($node as $domNode) {
                    if ($domNode->parentNode) {
                        $domNode->parentNode->removeChild($domNode);
                    }
                }
            });

            // Try to find main content area first
            $mainContent = null;
            foreach (['main', 'article', '#content', '.content', '.main-content', '#main', '.post', '.entry', '.article-body', '#mw-content-text', '.mw-parser-output', '#bodyContent'] as $selector) {
                try {
                    $content = $crawler->filter($selector);
                    if ($content->count() > 0) {
                        $mainContent = $content;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // If no main content found, use body
            if (!$mainContent) {
                $mainContent = $crawler->filter('body');
            }

            if ($mainContent->count() === 0) {
                return '';
            }

            $text = $mainContent->text();
            return $this->cleanText($text);
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function extractTitle(Crawler $crawler): string
    {
        if ($crawler->filter('title')->count() > 0) {
            return trim($crawler->filter('title')->text());
        }
        return '';
    }

    protected function extractH1(Crawler $crawler): string
    {
        if ($crawler->filter('h1')->count() > 0) {
            return trim($crawler->filter('h1')->first()->text());
        }
        return '';
    }

    protected function extractMetaDescription(Crawler $crawler): string
    {
        $node = $crawler->filter('meta[name="description"]');
        if ($node->count() > 0) {
            return trim($node->attr('content') ?? '');
        }
        return '';
    }

    protected function extractAllHeadings(Crawler $crawler): array
    {
        $headings = [];
        foreach (range(1, 6) as $level) {
            $crawler->filter("h{$level}")->each(function ($node) use (&$headings, $level) {
                $headings[] = [
                    'tag' => "h{$level}",
                    'text' => trim($node->text()),
                ];
            });
        }
        return $headings;
    }

    protected function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    protected function getFirstParagraph(string $text): string
    {
        $paragraphs = preg_split('/\n\s*\n/', $text);
        foreach ($paragraphs as $paragraph) {
            $clean = trim($paragraph);
            if (str_word_count($clean) > 10) {
                return $clean;
            }
        }
        return '';
    }

    protected function getLastParagraph(string $text): string
    {
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $paragraphs = array_reverse($paragraphs);
        foreach ($paragraphs as $paragraph) {
            $clean = trim($paragraph);
            if (str_word_count($clean) > 10) {
                return $clean;
            }
        }
        return '';
    }

    protected function checkImageAltForKeyword(string $html, string $keyword): bool
    {
        $crawler = new Crawler($html);
        $found = false;
        $crawler->filter('img')->each(function ($node) use ($keyword, &$found) {
            $alt = $node->attr('alt') ?? '';
            if (stripos($alt, $keyword) !== false) {
                $found = true;
            }
        });
        return $found;
    }

    protected function calculatePlacementScore(
        bool $inTitle,
        bool $inH1,
        bool $inMeta,
        bool $inHeadings,
        bool $inFirstParagraph,
        bool $inLastParagraph,
        bool $inImageAlt,
        float $density
    ): int {
        $score = 0;

        if ($inTitle) $score += 25;
        if ($inH1) $score += 20;
        if ($inMeta) $score += 15;
        if ($inHeadings) $score += 10;
        if ($inFirstParagraph) $score += 10;
        if ($inLastParagraph) $score += 5;
        if ($inImageAlt) $score += 5;

        if ($density >= 1.0 && $density <= 3.0) {
            $score += 10;
        } elseif ($density > 0 && $density < 1.0) {
            $score += 5;
        } elseif ($density > 3.0) {
            $score += 2;
        }

        return min(100, $score);
    }
}
