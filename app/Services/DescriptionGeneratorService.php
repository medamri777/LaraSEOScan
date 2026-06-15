<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DescriptionGeneratorService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
        $this->apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model = 'llama-3.1-8b-instant';
    }

    public function generate(string $name, string $type, string $language = 'french'): string
    {
        if (empty($this->apiKey) || empty($name)) {
            return '';
        }

        $name = ucwords(strtolower($name));

        $systemPrompt = <<<SYSTEM
You are a senior SEO copywriter specialized in writing factual website descriptions.

OUTPUT RULES (HIGHEST PRIORITY — NEVER BREAK THESE):
- Output ONLY one sentence
- Maximum 30 words
- No quotes, no explanations, no extra text, no formatting
- Return the sentence directly, nothing else

LANGUAGE RULES:
- Write in the language specified in the input
- If language is french: use natural conversational French, not legal or administrative language
- If language is arabic: use clear Modern Standard Arabic with correct grammar
- If language is darija: use Moroccan Darija mixed with French
- Always respect gender agreement in French (un plat = masculine, une cuisine = feminine)

CONTENT RULES:
- Factual and neutral tone only
- Use only ONE main verb per sentence
- Use only information implied by the name and type
- Do NOT add fake features or assumptions

FORBIDDEN WORDS (NEVER USE):
vast, huge, massive, powerful, leading, best, amazing, excellent, premium, unique, exceptionnel, haute qualité, meilleur, top, national (unless stated in input)

FRENCH VOCABULARY GUIDE:
- Restaurant/Café → use "proposant", "servant", never "établissement de service de restauration"
- Ecommerce → "boutique en ligne proposant", "site de vente de"
- LocalBusiness → "spécialisé dans", "prestataire de services"
- Blog → "publiant des articles sur"

TYPE BEHAVIOR:
- Restaurant/Cafe → food and drink service establishment
- Ecommerce → online store for products only (NOT travel/booking)
- Streaming → video or audio streaming platform
- Blog → article publishing website
- SaaS → web-based software tool
- Museum → cultural institution for exhibitions
- Clinic/Medical → healthcare services provider
- Agency → professional services agency
- LocalBusiness → local service provider
- Unknown → neutral website description

ARABIC GRAMMAR RULES:
- Use singular "موقع إلكتروني" never plural "مواقع"
- Use "هو" for masculine (موقع), "هي" for feminine (منصة، خدمة)
- Correct إضافة: "سباقات السيارات" NOT "السباقات السيارات"
- Use active verbs: يوفر، يقدم، يعرض، يتيح

ANTI-HALLUCINATION: Only use what is implied by the name and type. Never add details from your training data about famous places.

EXAMPLES (FORMAT ONLY — DO NOT COPY):
Input: Netflix | Streaming | english
Output: Netflix is a video streaming platform for watching movies and series online.

Input: L'Amiral Café | Restaurant | french
Output: L'Amiral Café est un restaurant proposant une sélection de plats et boissons servis sur place.

Input: Jumia | Ecommerce | french
Output: Jumia est une boutique en ligne proposant des produits livrés dans plusieurs pays.

Input: عيادة الأمل | Clinic | arabic
Output: عيادة الأمل مركز طبي يقدم خدمات الرعاية الصحية والاستشارات للمرضى.
SYSTEM;

        $userMessage = "Input: {$name} | {$type} | {$language}\nOutput:";

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 80,
                ]);

            if (!$response->successful()) {
                Log::error("Groq DescriptionGenerator failed: {$response->status()} - {$response->body()}");
                return '';
            }

            $data = $response->json();
            return trim($data['choices'][0]['message']['content'] ?? '');
        } catch (\Exception $e) {
            Log::error("Groq DescriptionGenerator exception: {$e->getMessage()}");
            return '';
        }
    }
}
