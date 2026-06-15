<?php

namespace App\Services\Seo;

class KeywordDensityService
{
    public function analyze(string $html, int $minDensity = 0, int $maxDensity = 100): array
    {
        // Strip tags
        $text = strip_tags($html);
        
        // Improve text extraction - maybe remove scripts/styles before strip_tags
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
        $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $text);
        $text = strip_tags($text);
        
        // Normalize
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/u', '', $text); // Remove punctuation
        
        $words = str_word_count($text, 1);
        $totalWords = count($words);
        
        if ($totalWords === 0) {
            return [];
        }

        // Remove stop words (basic list)
        $stopWords = ['the', 'and', 'a', 'to', 'of', 'in', 'i', 'is', 'that', 'it', 'on', 'you', 'this', 'for', 'but', 'with', 'are', 'have', 'be', 'at', 'or', 'as', 'was', 'so', 'if', 'out', 'not'];
        $filteredWords = array_diff($words, $stopWords);
        
        $counts = array_count_values($filteredWords);
        arsort($counts);
        
        $results = [];
        foreach (array_slice($counts, 0, 20) as $word => $count) {
            $density = ($count / $totalWords) * 100;
            $results[$word] = [
                'count' => $count,
                'density' => round($density, 2)
            ];
        }
        
        return [
            'total_words' => $totalWords,
            'keywords' => $results
        ];
    }
}
