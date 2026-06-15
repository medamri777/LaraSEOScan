<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiKeywordGenerator
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

    public function generate(string $domain, string $topic, string $ville, string $language = 'fr', ?string $modifiers = null): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $modifiers = $modifiers ?? '';
        $domainName = strtolower(preg_replace('/^(https?:\/\/)?(www\.)?/', '', explode('/', $domain)[0]));
        $brand = explode('.', $domainName)[0];

        $prompt = $this->buildPrompt($domain, $brand, $topic, $ville, $language, $modifiers);

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
                            'content' => 'You are an expert SEO keyword strategist specializing in Moroccan markets. Always return valid JSON only, no markdown, no explanation.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 4000,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                Log::error("Groq AI keyword generation failed: {$response->status()} - {$response->body()}");
                return [];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            $parsed = json_decode($content, true);
            if (!$parsed || !isset($parsed['keywords'])) {
                Log::error("Groq AI returned invalid JSON: {$content}");
                return [];
            }

            // Normalize keys for frontend compatibility
            return [
                'summary' => $parsed['seo_summary'] ?? '',
                'keywords' => $parsed['keywords'] ?? [],
                'avoid' => $parsed['keywords_to_avoid'] ?? [],
                'content_ideas' => $parsed['content_strategy'] ?? [],
                'quick_wins' => $parsed['quick_wins'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error("Groq AI keyword generation exception: {$e->getMessage()}");
            return [];
        }
    }

    protected function buildPrompt(string $domain, string $brand, string $topic, string $ville, string $language, string $modifiers = ''): string
    {
        $modifierInstruction = '';
        if (!empty($modifiers)) {
            $modifierInstruction = "\n\n## CUSTOM MODIFIERS (MUST INCLUDE):\nIncorporate these specific modifiers into many of the generated keywords: {$modifiers}\nExample: 'meilleur service {$topic} {$ville}', '{$topic} pas cher {$ville}', etc.";
        }

        return <<<PROMPT
You are a senior SEO strategist with 10+ years of experience in the Moroccan digital market.
You specialize in French, Arabic (Fusha), and Moroccan Darija search behavior.

## YOUR MISSION:
Generate the most effective and realistic SEO keywords for the following website.
Think like a real Moroccan user sitting at their phone or computer — what would they ACTUALLY type in Google?

## WEBSITE INFORMATION:
- Website: {$domain}
- Brand name: {$brand}
- Topic/Niche: {$topic}
- Target city/region: {$ville}
- Target country: Morocco
- Preferred language: {$language}
{$modifierInstruction}

## STEP 1 — THINK BEFORE YOU GENERATE:
Before writing keywords, answer these questions internally:
1. Who is the typical user of this website? (age, need, urgency)
2. What problem does this website solve for Moroccans?
3. What language do Moroccans in {$ville} use when searching for {$topic}?
4. What are the top 3 moments when someone needs this website? (ex: when they move, when they need a job, when they want to buy...)
5. What would make someone choose THIS website over a competitor?

Use your answers to guide the keywords you generate.

## STEP 2 — GENERATE KEYWORDS IN THESE 7 CATEGORIES:

### Category 1: CORE keywords (what the website is about)
- 3 to 5 keywords
- Direct, clear, and match the website topic exactly
- Mix: 1 in Arabic, 1 in Darija, rest in French

### Category 2: LOCAL keywords (topic + city/region)
- 4 to 6 keywords
- Always combine the topic with "{$ville}" and/or "Maroc"
- Include: "[topic] {$ville}", "meilleur [topic] {$ville}", "[topic] proche de {$ville}"
- In Arabic: "[موضوع] [مدينة]", "أفضل [موضوع] في [مدينة]"

### Category 3: QUESTION keywords (what users ask on Google)
- 5 to 7 keywords
- Start with: comment, pourquoi, où trouver, combien, quel, كيفاش, فين, بشحال, كيف, أين, ما هو
- These are GOLD for blog content and FAQs
- Must sound like a real human asking, not a robot

### Category 4: COMMERCIAL keywords (user is ready to buy/contact/hire)
- 4 to 5 keywords
- Include words like: prix, tarif, pas cher, devis, acheter, commander, contacter, réservation, ثمن, بسعر مناسب
- These bring users who have MONEY INTENT

### Category 5: PROBLEM keywords (user has a problem to solve)
- 3 to 4 keywords
- User is frustrated or stuck and needs a solution
- Ex: "comment réparer...", "problème avec...", "solution pour..."

### Category 6: BRANDED keywords (website name in search)
- 4 to 5 keywords
- Use the brand name: "{$brand}"
- Examples: "{$brand} {$ville}", "site {$brand}", "{$brand} avis", "أحسن موقع {$topic} {$ville}"
- These are the EASIEST to rank for — only YOUR site is relevant

### Category 7: LONG-TAIL OPPORTUNITY keywords (3-5 words, low competition)
- 4 to 5 keywords
- Very specific phrases that bigger sites ignore
- These are easy to rank for fast
- Must be realistic and not made-up

## STEP 3 — FOR EACH KEYWORD, PROVIDE:
- `keyword`: the exact search term
- `language`: french / arabic / darija / mixed
- `category`: core / local / question / commercial / problem / branded / longtail
- `difficulty`: easy / medium / hard
  * easy = a new website can rank in 3-6 months
  * medium = needs 6-12 months and good content
  * hard = dominated by big brands, very difficult
- `monthly_searches_estimate`: low (<100) / medium (100-1000) / high (>1000)
- `intent`: informational / commercial / transactional / navigational
- `why_valuable`: one sentence explaining why this keyword brings value to THIS specific website

## STEP 4 — ALSO PROVIDE:
### quick_wins: 
List 5 keywords with difficulty=easy AND intent=transactional or commercial
These are the keywords the user should target FIRST to get results fast.

### keywords_to_avoid:
List 4-5 keywords that seem relevant but are BAD choices. Explain why.
(Ex: too competitive, wrong audience, no search volume in Morocco, dominated by Wikipedia/Amazon/big brands)

### content_strategy:
Give 3 specific content/page ideas based on the best keywords.
Format: { "page_title": "...", "target_keyword": "...", "content_type": "article/landing page/FAQ", "why": "..." }

### seo_summary:
One paragraph (3-4 sentences) explaining the overall keyword strategy for this website.
What language mix to use, which category to prioritize, and what is the realistic timeline to see results.

## RULES:
- NEVER generate keywords that are just one word (too generic, impossible to rank for)
- NEVER generate English keywords (Moroccan users search in French/Arabic/Darija)
- NEVER generate fake or made-up search terms that no one actually searches
- ALWAYS think about what a MOROCCAN user specifically would type
- ALWAYS include at least 3 Darija keywords (this is what most Moroccans actually type)
- Total keywords generated: minimum 28, maximum 35

## OUTPUT FORMAT:
Return ONLY valid JSON. No explanation text outside the JSON.
{
  "seo_summary": "...",
  "keywords": [
    {
      "keyword": "...",
      "language": "french|arabic|darija|mixed",
      "category": "core|local|question|commercial|problem|branded|longtail",
      "difficulty": "easy|medium|hard",
      "monthly_searches_estimate": "low|medium|high",
      "intent": "informational|commercial|transactional|navigational",
      "why_valuable": "..."
    }
  ],
  "quick_wins": ["kw1", "kw2", "kw3", "kw4", "kw5"],
  "keywords_to_avoid": [{"keyword": "...", "reason": "..."}],
  "content_strategy": [{"page_title": "...", "target_keyword": "...", "content_type": "...", "why": "..."}]
}
PROMPT;
    }
}
