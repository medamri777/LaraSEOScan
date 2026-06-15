<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSchemaGenerator
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
        $this->apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model = config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function generate(string $url, string $name, string $topic, string $description, string $city, string $language = 'fr', array $extras = []): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $prompt = $this->buildPrompt($url, $name, $topic, $description, $city, $language, $extras);

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a Schema.org and Google Structured Data expert. Always return valid JSON only, no markdown, no explanation.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 4000,
                ]);

            if (!$response->successful()) {
                Log::error("Groq Schema Generator failed: {$response->status()} - {$response->body()}");
                return [];
            }

            $data = $response->json();
            $content = trim($data['choices'][0]['message']['content'] ?? '');

            $parsed = json_decode($content, true);
            if (!$parsed) {
                Log::error("Groq Schema returned invalid JSON: {$content}");
                return [];
            }

            $parsed = $this->fixSchema($parsed, $url);

            return $this->wrapSchema($parsed, $name, $topic);
        } catch (\Exception $e) {
            Log::error("Groq Schema Generator exception: {$e->getMessage()}");
            return [];
        }
    }

    protected function wrapSchema(array $schema, string $name, string $topic): array
    {
        $localTypes = ['LocalBusiness', 'Restaurant', 'Store', 'Hotel', 'BeautySalon', 'RealEstateAgent', 'ProfessionalService', 'MedicalBusiness', 'Attorney', 'LegalService', 'FoodEstablishment', 'LodgingBusiness'];

        $schemaType = 'WebSite';
        $isLocal = false;

        if (isset($schema[0]['@type'])) {
            $schemaType = $schema[0]['@type'];
            foreach ($schema as $s) {
                $st = $s['@type'] ?? '';
                if (in_array($st, $localTypes)) {
                    $schemaType = $st;
                    $isLocal = true;
                    break;
                }
                if (isset($s['address']) || isset($s['telephone']) || isset($s['geo']) || isset($s['openingHours'])) {
                    $isLocal = true;
                }
            }
        } elseif (isset($schema['@type'])) {
            $t = $schema['@type'];
            $schemaType = is_array($t) ? ($t[1] ?? $t[0]) : $t;
            $isLocal = in_array($schemaType, $localTypes)
                || isset($schema['address'])
                || isset($schema['telephone'])
                || isset($schema['geo'])
                || isset($schema['openingHours']);
        }

        $richResults = $isLocal ? [
            'Knowledge Panel in Google Search',
            'Business hours shown directly in search results',
            'Google Maps integration with location and directions',
        ] : [
            'Sitelinks Search Box in Google Search',
            'Knowledge Panel in Google Search',
            'Brand information in Knowledge Graph',
        ];

        $tips = $isLocal ? [
            'Add high-resolution photos to appear in Google Image results',
            'Collect Google Reviews to show star ratings in search',
            'Keep your opening hours up to date for accuracy',
        ] : [
            'Submit your sitemap to Google Search Console for faster indexing',
            'Add high-quality images with ImageObject for better rich results',
            'Verify your site with Google Search Console to monitor schema errors',
        ];

        $warning = $isLocal
            ? 'Consider adding: image, menu, sameAs (social media links)'
            : 'Consider adding: contactPoint, foundingDate, areaServed (if applicable)';

        return [
            'schema' => $schema,
            'schema_type' => $schemaType,
            'rich_results_unlocked' => $richResults,
            'improvement_tips' => $tips,
            'missing_fields_warning' => $warning,
        ];
    }

    protected function fixSchema(array $schema, string $url): array
    {
        $schemas = isset($schema[0]) ? $schema : [$schema];

        foreach ($schemas as $i => $s) {
            if (!isset($s['@type'])) continue;

            // Fix 1: SearchAction placeholder must be {search_term_string}
            if (isset($s['potentialAction']['@type']) && $s['potentialAction']['@type'] === 'SearchAction') {
                $target = &$schemas[$i]['potentialAction']['target'];
                if (is_string($target)) {
                    $target = preg_replace('/\{[^}]+\}/', '{search_term_string}', $target);
                } elseif (is_array($target) && isset($target['urlTemplate'])) {
                    $target['urlTemplate'] = preg_replace('/\{[^}]+\}/', '{search_term_string}', $target['urlTemplate']);
                }
                if (isset($schemas[$i]['potentialAction']['query-input'])) {
                    $schemas[$i]['potentialAction']['query-input'] = 'required name=search_term_string';
                }
            }

            // Fix 2: Logo string → ImageObject
            if (isset($s['logo']) && is_string($s['logo'])) {
                $logoUrl = $s['logo'];
                $schemas[$i]['logo'] = [
                    '@type' => 'ImageObject',
                    'url' => $logoUrl,
                ];
            }

            // Fix 3: URL consistency for known domains
            $host = parse_url($url, PHP_URL_HOST);
            if (is_string($host) && str_contains($host, 'wikipedia.org')) {
                $schemaUrl = (string) ($s['url'] ?? '');
                if ($s['@type'] === 'Organization' && str_contains($schemaUrl, 'wikipedia.org')) {
                    $schemas[$i]['url'] = 'https://www.wikimedia.org/';
                    if (!isset($schemas[$i]['name']) || $schemas[$i]['name'] === 'Wikipedia') {
                        $schemas[$i]['name'] = 'Wikimedia Foundation';
                    }
                }
            }
        }

        return isset($schema[0]) ? $schemas : $schemas[0];
    }

    public function generateDescription(string $topic, string $city): string
    {
        if (!$this->isConfigured() || empty($topic)) {
            return '';
        }

        $prompt = <<<PROMPT
You are a professional SEO copywriter. Generate a short description (2-3 sentences, max 150 words) 
in French for a business based on the following information:

- Topic/Type: {$topic}
- City: {$city}
- Country: Morocco

The description should:
- Be professional, natural and persuasive
- Include the city name naturally
- Be written in French
- NOT start with a generic phrase like "We offer..." or "Our business..."
- Sound like a real business description on a website

Return ONLY the description text, no JSON, no explanation.
PROMPT;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional SEO copywriter. Return only the description text, no extra formatting.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 300,
                ]);

            if (!$response->successful()) {
                Log::error("Groq Description generation failed: {$response->status()} - {$response->body()}");
                return '';
            }

            $data = $response->json();
            $content = trim($data['choices'][0]['message']['content'] ?? '');

            return $content;
        } catch (\Exception $e) {
            Log::error("Groq Description exception: {$e->getMessage()}");
            return '';
        }
    }

    public function generateMetaDescription(string $name, string $topic, string $city, string $language): array
    {
        if (!$this->isConfigured() || empty($name)) {
            return [];
        }

        $prompt = $this->buildMetaPrompt($name, $topic, $city, $language);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an SEO copywriter expert. Return only valid JSON, no explanation.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 500,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                Log::error("Groq Meta Description failed: {$response->status()} - {$response->body()}");
                return [];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            $parsed = json_decode($content, true);
            if (!$parsed || !isset($parsed['description'])) {
                Log::error("Groq Meta Description returned invalid JSON: {$content}");
                return [];
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error("Groq Meta Description exception: {$e->getMessage()}");
            return [];
        }
    }

    protected function buildPrompt(string $url, string $name, string $topic, string $description, string $city, string $language, array $extras = []): string
    {
        $langName = match ($language) {
            'french' => 'Français',
            'darija' => 'Darija',
            'arabic' => 'Arabe',
            default => 'Français',
        };

        $optionalFields = '';
        $extraLabels = [
            'telephone' => 'Telephone',
            'street_address' => 'Street Address',
            'postal_code' => 'Postal Code',
            'opening_hours' => 'Opening Hours',
            'image' => 'Logo/Image URL',
            'cuisine' => 'Cuisine Type',
            'price_range' => 'Price Range',
            'rating' => 'Rating',
            'review_count' => 'Review Count',
        ];
        foreach ($extras as $key => $val) {
            if ($val !== '' && isset($extraLabels[$key])) {
                $optionalFields .= "- {$extraLabels[$key]}: {$val}\n";
            }
        }

        return <<<PROMPT
You are an expert SEO + structured data (schema.org) generator.

Your task is to generate accurate, context-aware JSON-LD for ANY website based on real-world consistency between the URL and provided metadata.

CRITICAL RULE:
You MUST NOT blindly trust user input fields if they conflict with real-world website identity.

You must treat the WEBSITE URL as the PRIMARY SOURCE OF TRUTH.

-----------------------------------
INPUT DATA:
- Website URL: {$url}
- Website Name: {$name}
- User Suggested Type: {$topic}
- User Description: {$description}
- Language: {$langName}
- City: {$city}
- Optional SEO Fields:
{$optionalFields}
-----------------------------------
STEP 1 — REALITY VALIDATION (VERY IMPORTANT):
Analyze the website URL and determine its REAL nature.

Examples:
- wikipedia.org → Encyclopedia (NOT blog)
- youtube.com → Video platform
- amazon.com → E-commerce marketplace
- netflix.com → Streaming platform
- github.com → Code hosting platform

If user input conflicts with real-world identity:
→ IGNORE user input
→ Use correct inferred type

-----------------------------------
STEP 2 — SITE CLASSIFICATION:
Automatically classify the website into ONE correct category:
- WebSite
- Organization
- Blog
- NewsMediaOrganization
- OnlineStore
- LocalBusiness
- SaaS
- Encyclopedia
- Forum
- Portfolio
- StreamingPlatform

If uncertain → choose WebSite + Organization only (safe fallback)

-----------------------------------
STEP 3 — DESCRIPTION RULES:
- Generate a natural SEO description based on REAL classification
- Do NOT reuse generic phrases like:
  "articles de blog", "actualités", "visitez-nous"
- Do NOT hallucinate content types
- Keep it factual and consistent with site type

-----------------------------------
STEP 4 — SEARCHACTION RULE:
Only add SearchAction if:
- The website clearly has a search feature
- OR the URL structure supports search (e.g. /search, ?q=)

Otherwise OMIT it.

-----------------------------------
STEP 5 — LOGO RULE:
- Only use: {$url}logo.png
- Do NOT assume official logo URLs exist beyond that pattern

-----------------------------------
STEP 6 — OUTPUT RULES:
- Return ONLY valid JSON-LD
- No explanations
- No markdown
- No comments
- Must be production-ready and clean

-----------------------------------
OUTPUT STRUCTURE:
Return a single JSON object or an array depending on the CONTEXT-AWARE RULES below:
- Local business → single object (Restaurant, Store, LocalBusiness)
- Wikipedia → array with WebSite + Organization
- Famous platform → single WebSite object

-----------------------------------
CONTEXT-AWARE RULES:
- If URL contains "wikipedia.org" → generate WebSite + Organization with publisher
- If URL is a LOCAL business domain (.ma, .fr, regional) → generate ONLY the business type (Restaurant, Store, LocalBusiness)
- If URL is a FAMOUS platform (amazon.com, netflix.com) → generate WebSite type
- If URL is UNKNOWN or local → do NOT add publisher or organization unless explicitly provided

-----------------------------------
CRITICAL — USE PROVIDED DESCRIPTION verbatim:
If the user provides a "description" field in the INPUT DATA above:
  → Use it EXACTLY as provided — put it in the JSON "description" field verbatim
  → Do NOT regenerate it
  → Do NOT add marketing language
  → Do NOT modify or change it
  → Do NOT rewrite it
Only generate a NEW description if the user leaves it EMPTY (blank).

-----------------------------------
REFERENCE EXAMPLES — Different types (format only, DO NOT apply Wikipedia to non-Wikipedia).
In ALL examples below, "description" is a PLACEHOLDER — you MUST replace it with the user's exact "description" from INPUT DATA:

Example 1 — Local Restaurant (user inputs: "L'Amiral Café", type: Restaurant, url: lamiral.ma):
{
  "@context": "https://schema.org",
  "@type": "Restaurant",
  "url": "https://lamiral.ma/",
  "name": "L'Amiral Café",
  "description": "<USER DESCRIPTION HERE>",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Nador",
    "addressCountry": "MA"
  }
}

Example 2 — Local E-commerce (user inputs: "Nador Auto", type: Store, url: nadorauto.ma):
{
  "@context": "https://schema.org",
  "@type": ["LocalBusiness", "Store"],
  "url": "https://nadorauto.ma/",
  "name": "Nador Auto",
  "description": "<USER DESCRIPTION HERE>",
  "areaServed": "Nador, Maroc"
}

Example 3 — Wikipedia ONLY (ONLY IF URL is wikipedia.org):
[
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "url": "https://www.wikipedia.org/",
    "name": "Wikipedia",
    "description": "<USER DESCRIPTION HERE>",
    "inLanguage": "fr",
    "publisher": {
      "@type": "Organization",
      "name": "Wikimedia Foundation",
      "url": "https://www.wikimedia.org/"
    },
    "potentialAction": {
      "@type": "SearchAction",
      "target": "https://www.wikipedia.org/w/index.php?search={search_term_string}",
      "query-input": "required name=search_term_string"
    }
  }
]

CRITICAL: The Wikipedia example with Wikimedia Foundation is ONLY for wikipedia.org.
For ANY other website (especially local businesses), ignore the Wikipedia example completely.

-----------------------------------
LOCAL SEO BOOST FIELDS (use the exact values from Optional SEO Fields above when provided):
These fields dramatically improve Google Search visibility. Incorporate them into the schema when the user provided them:
- telephone → enables click-to-call button in search results
- address (street, postalCode, city) → shows business on Google Maps
- openingHours → displays hours directly in search results
- image (logo URL) → shows logo in Knowledge Panel
- servesCuisine → helps with restaurant search filters
- priceRange → enables budget filter in local search ($/$$/$$$)
- aggregateRating (ratingValue + ratingCount) → shows star ratings in search

Rule: If the user provided a value in Optional SEO Fields above, include it in the schema.
If a field is empty or not provided, OMIT it entirely — never hallucinate.

FIELD FORMATTING (STRICT):
- telephone: ALWAYS convert to international format: "+212" + remaining digits, NO SPACES
  Example: "0620291545" → "+212620291545"
- openingHours: ALWAYS array format, NEVER string
  Example: ["Mo-Fr 09:00-18:00", "Sa-Su 10:00-19:00"]
- streetAddress: capitalize first letter of each word
  Example: "mohamed V" → "Mohamed V"
- description: use user input EXACTLY, do NOT regenerate it
- ratingCount: use ratingCount field name, NEVER reviewCount
- aggregateRating must contain BOTH ratingValue AND ratingCount, or omit entirely

IMPORTANT FORMAT NOTES:
- "target" inside SearchAction is a DIRECT URL STRING, NOT an object
- Organization schema is MINIMAL (url + name only unless more data is known)
- "publisher" inside WebSite is a simple object with type, name, url
- No "sameAs" unless real social URLs are provided
- No "logo" unless a real logo URL is known
- Keep schemas clean and compact

ANTI-HALLUCINATION RULE:
CRITICAL: Only use information explicitly provided in the user input.
Never add details from your training data about famous places or businesses.
Never guess or assume: location, city, region, founder, parent company, hours, phone, email, address
If location/city is not provided, omit those fields entirely.
Only generate ONE schema based on the @type input provided.
Do NOT generate WebSite if type is Restaurant — generate ONLY Restaurant.
Do NOT generate Organization unless explicitly requested.

-----------------------------------
FINAL GOAL:
Generate accurate structured data that reflects REAL website identity, not user assumptions.
PROMPT;
    }

    protected function buildMetaPrompt(string $name, string $topic, string $city, string $language): string
    {
        return <<<PROMPT
You are an SEO copywriter expert. Your job is to write the perfect meta description 
for a website based on its topic and information.

## WEBSITE INFORMATION:
- Website name: {$name}
- Topic/Niche: {$topic}
- City: {$city}
- Country: Morocco
- Language: {$language}
- Target audience: Moroccan users

## STRICT SEO RULES YOU MUST FOLLOW:
1. Length: Between 140 and 155 characters EXACTLY — count every character before returning
2. Include the main keyword from the topic naturally (not forced)
3. Include the city name "{$city}" if it's a local business
4. End with a clear call to action (ex: "Contactez-nous", "Découvrez", "Commandez", "Visitez-nous")
5. Write in the language Moroccan users prefer for this topic
6. NO keyword stuffing — the text must read naturally
7. NO quotes inside the text (Google ignores meta descriptions with quotes)
8. NO ALL CAPS words
9. Must make the user WANT to click — think like an ad copywriter
10. Must describe what the user will FIND on the website, not what the website IS

## LANGUAGE RULES FOR MOROCCO:
- French → for professional, medical, legal, tech, education topics
- Arabic (Fusha) → for news, government, formal content
- Darija mix → for e-commerce, food, local services, casual topics
- Use the language specified: {$language}

## WHAT MAKES A GREAT META DESCRIPTION:
- Answers the question: "Why should I click THIS result and not the others?"
- Contains a benefit (what the user gains)
- Contains urgency or value (meilleur prix, rapide, professionnel, gratuit, livraison)
- Feels human, not robotic

## EXAMPLES OF BAD vs GOOD:

BAD: "Site web de vente de voitures à Nador Maroc avec des voitures et des véhicules."
(Too generic, no CTA, keyword stuffed)

GOOD: "Achetez ou vendez votre voiture d'occasion à Nador au meilleur prix. Large choix de véhicules vérifiés. Contactez-nous aujourd'hui !"
(Clear benefit, local keyword, CTA, 152 characters)

BAD: "مرحبا بكم في موقعنا الذي يقدم خدمات متنوعة في مجال السيارات"
(Too vague, no location, no CTA)

GOOD: "اشتري أو بع سيارتك في ناضور بأفضل الأسعار. مئات السيارات المستعملة الموثوقة بانتظارك. تواصل معنا الآن!"
(Specific, local, CTA, compelling)

## OUTPUT FORMAT — Return ONLY this JSON:
{
  "description": "the meta description text here",
  "character_count": 152,
  "language_used": "french",
  "main_keyword_used": "voiture occasion Nador",
  "cta_used": "Contactez-nous aujourd'hui",
  "seo_score": "excellent",
  "alternative": "a slightly different version as backup option"
}

IMPORTANT: Count the characters in "description" manually before returning. 
It MUST be between 140 and 155 characters. If it's too long or too short, rewrite it.
PROMPT;
    }
}
