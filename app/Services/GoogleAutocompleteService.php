<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAutocompleteService
{
    protected static ?float $lastRequestTime = null;

    protected const MIN_INTERVAL_MS = 200;

    public function suggest(string $query, string $country = 'ma', string $language = 'fr'): array
    {
        if (empty(trim($query))) return [];

        $this->throttle();

        try {
            $url = 'https://suggestqueries.google.com/complete/search?client=firefox&q='
                . urlencode($query)
                . '&gl=' . strtoupper($country)
                . '&hl=' . $language;

            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get($url);

            if ($response->header('Retry-After')) {
                $seconds = (int) $response->header('Retry-After');
                Log::warning("Google Suggest rate-limited, Retry-After: {$seconds}s");
                if ($seconds > 0 && $seconds <= 60) {
                    usleep($seconds * 1_000_000);
                }
                return [];
            }

            if (!$response->successful()) return [];

            $body = (string) $response->body();
            $data = json_decode($body, true);

            if (!is_array($data) || !isset($data[1])) return [];

            return array_filter($data[1], fn($s) => is_string($s) && !empty(trim($s)));
        } catch (\Throwable $e) {
            Log::warning("Google Autocomplete failed for '{$query}': {$e->getMessage()}");
            return [];
        }
    }

    protected function throttle(): void
    {
        if (self::$lastRequestTime !== null) {
            $elapsedMs = (microtime(true) - self::$lastRequestTime) * 1000;
            $neededMs = self::MIN_INTERVAL_MS - $elapsedMs;
            if ($neededMs > 0) {
                usleep((int) ($neededMs * 1000));
            }
        }
        self::$lastRequestTime = microtime(true);
    }

    public function expandSeeds(array $seeds, string $country = 'ma', string $language = 'fr', int $maxTotal = 60): array
    {
        $all = [];
        $seen = [];

        foreach ($seeds as $seed) {
            $suggestions = $this->suggest($seed, $country, $language);
            foreach ($suggestions as $s) {
                $normalized = trim(mb_strtolower($s));
                if (!isset($seen[$normalized])) {
                    $seen[$normalized] = true;
                    $all[] = $s;
                }
            }
            if (count($all) >= $maxTotal) break;
        }

        if (count($all) < count($seeds) * 2) {
            foreach ($seeds as $seed) {
                $normalized = trim(mb_strtolower($seed));
                if (!isset($seen[$normalized])) {
                    $seen[$normalized] = true;
                    $all[] = $seed;
                }
            }
        }

        return array_slice($all, 0, $maxTotal);
    }

    /**
     * Alphabet expansion trick: append each letter a–z to the seed keyword
     * and collect all unique suggestions. This turns one seed into 200+
     * real keyword ideas from Google Autocomplete.
     *
     * Example: "seo tools" → "seo tools a", "seo tools b", ... → hundreds of suggestions
     *
     * @param  string  $seed      The seed keyword
     * @param  string  $country   Two-letter country code (e.g. 'ma', 'fr', 'us')
     * @param  string  $language  Two-letter language code (e.g. 'fr', 'en')
     * @param  int     $maxTotal  Maximum number of unique suggestions to return
     * @return array<int, string> Unique keyword suggestions
     */
    public function alphabetExpand(string $seed, string $country = 'ma', string $language = 'fr', int $maxTotal = 250): array
    {
        $all = [];
        $seen = [];

        // Also include the base seed first
        $baseSuggestions = $this->suggest($seed, $country, $language);
        foreach ($baseSuggestions as $s) {
            $normalized = trim(mb_strtolower($s));
            if ($normalized === '' || isset($seen[$normalized])) continue;
            if (trim(mb_strtolower($s)) === trim(mb_strtolower($seed))) continue;
            $seen[$normalized] = true;
            $all[] = $s;
        }

        // Iterate a–z
        foreach (range('a', 'z') as $letter) {
            if (count($all) >= $maxTotal) break;

            $query = $seed . ' ' . $letter;
            $suggestions = $this->suggest($query, $country, $language);

            foreach ($suggestions as $s) {
                $normalized = trim(mb_strtolower($s));
                if ($normalized === '' || isset($seen[$normalized])) continue;
                // Skip exact match with the seed itself
                if ($normalized === trim(mb_strtolower($seed))) continue;
                $seen[$normalized] = true;
                $all[] = $s;
            }
        }

        return array_slice($all, 0, $maxTotal);
    }
}
