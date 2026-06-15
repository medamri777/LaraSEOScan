# Free & Open-Source Tools for 5 Core SEO Features — LaraSEOScan Research Report

This report provides a comprehensive analysis of free and open-source tools, libraries, and APIs that can be used to build a full-featured SEO SaaS application, "LaraSEOScan," on the PHP/Laravel framework. The research is broken down by five core SEO features, evaluating each tool's viability, cost, and implementation difficulty within a Laravel environment.

---

## Feature 1: Site Audit

A site audit tool needs to crawl a website and check for a wide range of technical and on-page SEO factors. Key checks include broken links, meta tags, heading structure (H1), server response times, redirects, canonical tags, `robots.txt` compliance, sitemap presence, page speed metrics, and image alt text.

The following open-source tools are viable options for building this feature.

![Laravel SEO Scanner Dashboard](https://user-images.githubusercontent.com/10845460/210797960-d65e260e-d543-4aec-aca8-1d9cca3aee96.png)

| Tool / Library | Stars | Language | License | Cost | Key Features & Notes | Laravel Integration |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **backstagephp/laravel-seo-scanner** [^1](https://github.com/backstagephp/laravel-seo-scanner) | 266 [^1](https://github.com/backstagephp/laravel-seo-scanner) | PHP | MIT [^1](https://github.com/backstagephp/laravel-seo-scanner) | Fully Free | Performs 24 automated checks (meta, H1, H2, canonicals, robots, sitemap, speed hints, alts). Scans Laravel routes or external URLs. Supports SPA rendering via Puppeteer [^1](https://github.com/backstagephp/laravel-seo-scanner). Requires Laravel 9+, PHP 8.1+. Last updated Nov 2025 [^1](https://github.com/backstagephp/laravel-seo-scanner). | **Native** |
| **spatie/crawler** [^2](https://github.com/spatie/crawler) | ~3,800 [^2](https://github.com/spatie/crawler) | PHP | MIT [^2](https://github.com/spatie/crawler) | Fully Free | A powerful, general-purpose crawling engine. Not SEO-specific; you must build custom `CrawlObserver` classes for SEO checks. Supports concurrent requests and respects `robots.txt` [^2](https://github.com/spatie/crawler). | **Excellent** |
| **SEOnaut** [^3](https://github.com/StJudeWasHere/seonaut) | ~280 [^3](https://github.com/StJudeWasHere/seonaut) | Go | AGPLv3 [^3](https://github.com/StJudeWasHere/seonaut) | Fully Free | Checks title, meta, H1, canonicals, OG tags, structured data, response codes, redirects, images, and links [^3](https://github.com/StJudeWasHere/seonaut). Self-hosted via Docker and includes a web UI [^4](https://www.reddit.com/r/webdev/comments/1ej62lm/i_built_and_open_source_seo_auditing_tool/). | **Indirect** |
| **SiteOne Crawler** [^5](https://github.com/janreges/siteone-crawler) | 757 [^5](https://github.com/janreges/siteone-crawler) | PHP CLI | MIT [^5](https://github.com/janreges/siteone-crawler) | Fully Free | Checks DNS, headers, meta tags, H1, broken links, redirects, and sitemaps. Outputs to CLI and HTML reports. Rewritten and updated in 2026 [^5](https://github.com/janreges/siteone-crawler). | **Moderate** |
| **LibreCrawl** [^6](https://github.com/PhialsBasement/LibreCrawl) | 671 [^6](https://github.com/PhialsBasement/LibreCrawl) | Python | MIT [^6](https://github.com/PhialsBasement/LibreCrawl) | Fully Free | A "Screaming Frog alternative" [^7](https://www.reddit.com/r/SEO_Digital_Marketing/comments/1qdxxmp/librecrawl_free_seo_crawler_that_surpasses/). Checks titles, metas, H1s, canonicals, robots, redirect chains, page size, status codes, and image alts [^6](https://github.com/PhialsBasement/LibreCrawl). | **Low** |
| **python-seo-analyzer** [^8](https://github.com/sethblack/python-seo-analyzer) | ~1,500 [^8](https://github.com/sethblack/python-seo-analyzer) | Python | MIT [^8](https://github.com/sethblack/python-seo-analyzer) | Fully Free | Analyzes over 30 factors, including keyword presence in title/meta/H1, image alt text, internal links, and schema validation [^8](https://github.com/sethblack/python-seo-analyzer). | **Low** |

### Best Picks for LaraSEOScan (Site Audit)

*   **Best Laravel-Native Solution:** `backstagephp/laravel-seo-scanner` is the top choice for ease of integration. It can be installed via Composer and provides 24 essential SEO checks out of the box [^1](https://github.com/backstagephp/laravel-seo-scanner).
*   **Best Crawl Engine:** `spatie/crawler` is the most flexible and robust option. With over 3,800 stars, it is the industry standard for building custom crawling solutions in the Laravel ecosystem [^2](https://github.com/spatie/crawler).
*   **Best Complete Standalone Tool:** `SEOnaut` offers a full-featured, self-hosted solution with a dedicated web UI. It is best run as a separate Docker service and integrated with LaraSEOScan via its REST API [^3](https://github.com/StJudeWasHere/seonaut).
*   **Recommended Combo:** Use `spatie/crawler` as the core engine for its power and flexibility, and incorporate the specific checks and logic from `laravel-seo-scanner` to build a custom, high-performance auditor.

---

## Feature 2: Position Tracking (SERP Rank Tracker)

This feature requires tracking keyword positions in Google, providing daily or weekly updates, historical performance charts, and segmenting data by mobile and desktop devices.

![SerpBear UI GIF](https://camo.githubusercontent.com/5c2abaefc3f02ee7c43c9b25b83e3b9c3876bba2e1e3d3918184cb7ffd189f9f/68747470733a2f2f73657270626561722e622d63646e2e6e65742f73657270626561725f726561646d655f76322e676966)

| Tool / API | Stars | Tech Stack | License | Cost Model | Key Features & Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **SerpBear** [^9](https://github.com/towfiqi/serpbear) | ~2,300 [^9](https://github.com/towfiqi/serpbear) | TypeScript, Next.js | MIT [^9](https://github.com/towfiqi/serpbear) | Free (requires SERP API) | Self-hosted Docker app using an SQLite database. Features daily/weekly tracking, charts, mobile/desktop, tags, and notifications [^9](https://github.com/towfiqi/serpbear). Integrates with Google Search Console [^10](https://docs.serpbear.com/). ⚠️ Reddit users on r/localseo report the developer has become less active [^11](https://www.reddit.com/r/localseo/comments/1otz9oa/serpbear_simple_keyword_rank_tracking/). |
| **Serposcope** [^12](https://github.com/serphacker/serposcope) | ~1,100 [^12](https://github.com/serphacker/serposcope) | Java | Apache 2.0 [^12](https://github.com/serphacker/serposcope) | Fully Free | Scrapes Google directly without a paid API. Supports unlimited keywords, scheduling, and location tracking [^13](https://www.serposcope.com/en/). ⚠️ Confirmed by the developer on r/bigseo to be in "survival mode" and is no longer actively maintained [^14](https://www.reddit.com/r/bigseo/comments/hv0c1c/looking_for_open_source_rank_tracking_tool/). |
| **SEO Panel** [^15](https://github.com/seopanel/seo-panel) | ~850 [^15](https://github.com/seopanel/seo-panel) | PHP | GPLv2 [^15](https://github.com/seopanel/seo-panel) | Fully Free | A self-hosted PHP dashboard that includes rank tracking, keyword management, and backlink checking features, making it compatible with a standard Laravel hosting environment [^15](https://github.com/seopanel/seo-panel). |
| **Google Search Console API** [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome) | N/A | REST API | N/A | Fully Free | Provides exact impression, click, and position data for keywords on websites you own. The official, 100% free source for first-party data. Does not work for competitor analysis [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome). |

### SERP API Comparison for Competitor Tracking

| API | Free Tier | Paid Tier | Best For |
| :--- | :--- | :--- | :--- |
| **Serper.dev** [^9](https://github.com/towfiqi/serpbear) | 2,500 queries/month | $1.00 / 1,000 queries | Cheapest reliable option |
| **ScrapingAnt** [^9](https://github.com/towfiqi/serpbear) | 10,000 requests/month | $19 / month | Most generous free tier |
| **ValueSERP** [^9](https://github.com/towfiqi/serpbear) | None | $2.50 / 1,000 queries | High accuracy |
| **Google Search Console** [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome) | Unlimited (own site only) | Free | Own site positions |
| **SerpAPI** [^9](https://github.com/towfiqi/serpbear) | 100 / month | $50 / month | Most features |

### Best Picks for LaraSEOScan (Position Tracking)

*   **For User's Own Sites:** Integrate the **Google Search Console API**. It is completely free, official, and provides the most accurate data (impressions, clicks, average position) [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome).
*   **For Competitor/General Keywords:** Use the architecture of **SerpBear** as a reference. For the data source, integrate with **Serper.dev**, which offers the best value at just $1 per 1,000 queries after a generous free tier [^9](https://github.com/towfiqi/serpbear).
*   **Avoid:** `Serposcope` is not recommended for a new project due to being abandoned and its technology aging [^14](https://www.reddit.com/r/bigseo/comments/hv0c1c/looking_for_open_source_rank_tracking_tool/).

---

## Feature 3: Keyword Magic Tool (Ideas & Suggestions)

This feature takes a single "seed" keyword and generates hundreds of related long-tail keywords, ideally providing metrics like search volume, difficulty, and CPC.

| Tool / API | Stars | Language | License | Cost | Key Features & Notes | Laravel Integration |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **rmak78/keyword-suggest-tool** [^17](https://github.com/rmak78/keyword-suggest-tool) | ~50 [^17](https://github.com/rmak78/keyword-suggest-tool) | PHP | MIT [^17](https://github.com/rmak78/keyword-suggest-tool) | Fully Free | A simple PHP library that scrapes Google Autocomplete suggestions for a seed keyword. Returns about 10 related terms per query but does not provide volume data [^17](https://github.com/rmak78/keyword-suggest-tool). | **Very Easy** |
| **chukhraiartur/seo-keyword-research-tool** [^18](https://github.com/chukhraiartur/seo-keyword-research-tool) | 154 [^18](https://github.com/chukhraiartur/seo-keyword-research-tool) | Python | MIT [^18](https://github.com/chukhraiartur/seo-keyword-research-tool) | Free (needs API for volume) | Extracts keywords from Google Autocomplete, People Also Ask, and Related Searches. Uses a paid SerpAPI key to fetch volume data but provides suggestions for free [^18](https://github.com/chukhraiartur/seo-keyword-research-tool). | **Low** |
| **eliasdabbas/advertools** [^19](https://github.com/eliasdabbas/advertools) | 1,400 [^19](https://github.com/eliasdabbas/advertools) | Python | MIT [^19](https://github.com/eliasdabbas/advertools) | Fully Free | A comprehensive library for keyword generation, SERP analysis, and GSC integration. Can pull real volume data via GSC, but only for keywords the user's site already ranks for [^19](https://github.com/eliasdabbas/advertools). | **Low** |
| **Google Suggest Endpoint** [^20](https://importsem.com/query-google-suggestions-api-with-python/) | N/A | API | N/A | Fully Free | The undocumented API powering Google search suggestions. Endpoint: `https://suggestqueries.google.com/complete/search?output=toolbar&q={keyword}&hl=en`. Returns 10 suggestions per call with no authentication. Use the "alphabet trick" (appending `a`, `b`, `c`...) to the query to generate hundreds of ideas [^20](https://importsem.com/query-google-suggestions-api-with-python/). No volume data. | **Very Easy** |
| **every-app/open-seo** [^21](https://github.com/every-app/open-seo) | 2,100 [^21](https://github.com/every-app/open-seo) | TypeScript | MIT [^21](https://github.com/every-app/open-seo) | Reference | A complete open-source SaaS that demonstrates how to build a full keyword research tool using the DataForSEO API. The best reference codebase available [^21](https://github.com/every-app/open-seo). | **N/A** |

### Search Volume & Difficulty Data Options

| API | Cost | Volume | Difficulty | CPC | Free Tier |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **DataForSEO Labs `/keyword_ideas/live`** [^22](https://dataforseo.com/pricing) | ~$0.60 / 1,000 keywords | ✅ | ✅ | ✅ | $1 test credit [^23](https://dataforseo.com/help-center/how-does-your-free-unlimited-trial-work) |
| **Keywords Everywhere API** [^24](https://keywordseverywhere.com/) | $27/year for 100k lookups | ✅ | ✅ | ✅ | None |
| **Google Keyword Planner (Ads API)** [^25](https://ads.google.com/home/tools/keyword-planner/) | Free with Ads account | ✅ (range) | ❌ | ✅ | Free but requires complex OAuth setup |
| **Google Suggest Endpoint** [^20](https://importsem.com/query-google-suggestions-api-with-python/) | Free | ❌ | ❌ | ❌ | Always free |

### Best Picks for LaraSEOScan (Keyword Magic)

*   **Phase 1 (Free MVP):** Combine the `rmak78/keyword-suggest-tool` PHP library with direct calls to the Google Suggest endpoint [^20](https://importsem.com/query-google-suggestions-api-with-python/) to provide a robust keyword idea generator without volume data. This is fast, free, and easy to implement.
*   **Phase 2 (Paid Upgrade):** Integrate the **DataForSEO** `keyword_ideas/live` endpoint. At approximately $0.60 per 1,000 keywords, it is the most affordable way to enrich the suggestions with search volume, keyword difficulty, and CPC data [^22](https://dataforseo.com/pricing).
*   **Reference Codebase:** Study the `every-app/open-seo` repository to understand how a professional keyword magic tool is built on top of the DataForSEO API [^21](https://github.com/every-app/open-seo).

---

## Feature 4: Keyword Overview (Single Keyword Analysis)

This feature provides a deep dive into a single keyword, showing its monthly search volume, a keyword difficulty score, CPC, a snapshot of the current top 10 SERP, and a trend graph.

| Tool / API | Stars | Tech Stack | License | Cost Model | Key Features & Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **DataForSEO Labs API** [^26](https://dataforseo.com/) | N/A | API | N/A | Paid | The most affordable all-in-one solution. Use `/search_volume/live` for volume/CPC, `/keyword_info/live` for difficulty, and `/serp/.../live` for a SERP snapshot [^26](https://dataforseo.com/). A PHP SDK is available [^27](https://github.com/dataforseo/php-client). $1 free starting credit [^23](https://dataforseo.com/help-center/how-does-your-free-unlimited-trial-work). |
| **Keywords Everywhere API** [^24](https://keywordseverywhere.com/) | N/A | API | N/A | Paid | Very affordable for volume, CPC, and trend data at $27/year for 100,000 lookups. It does not provide a keyword difficulty score [^24](https://keywordseverywhere.com/). |
| **pytrends** [^28](https://github.com/GeneralMills/pytrends) | 3,500 [^28](https://github.com/GeneralMills/pytrends) | Python | MIT [^28](https://github.com/GeneralMills/pytrends) | Fully Free | A Python library for the Google Trends API. Provides *relative* search interest over time, not absolute search volume. Excellent for trend graphs [^28](https://github.com/GeneralMills/pytrends). |
| **DIY Difficulty Estimator** | N/A | API Mashup | N/A | ~$0.001/keyword | A free approach to estimate difficulty: 1. Fetch the top 10 SERP results for a keyword using Serper.dev ($0.001). 2. Get the Page Authority score for each of the 10 URLs using the free OpenPageRank API. 3. Average the scores to create a difficulty estimate. |
| **Google Search Console API** [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome) | N/A | API | N/A | Fully Free | Perfect for a "My Keywords" overview. Provides exact impressions, clicks, CTR, and average position for keywords on the user's own site [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome). |
| **every-app/open-seo** [^21](https://github.com/every-app/open-seo) | 2,100 [^21](https://github.com/every-app/open-seo) | TypeScript | MIT [^21](https://github.com/every-app/open-seo) | Reference | The best open-source example of how to implement a complete Keyword Overview feature using DataForSEO as the backend [^21](https://github.com/every-app/open-seo). |

### Best Picks for LaraSEOScan (Keyword Overview)

*   **For User's Own Keywords:** The **Google Search Console API** is the best choice. It's free and provides exact performance data [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome).
*   **For Any Keyword (Best Value):** **DataForSEO** is the cheapest option that provides all necessary data points: volume, difficulty, CPC, and SERP snapshot, for about $0.60 per 1,000 keywords [^26](https://dataforseo.com/). Reddit community consensus on r/TechSEO confirms it is the best affordable data API [^29](https://www.reddit.com/r/TechSEO/comments/1rn4e18/dataforseo_api_for_automated_keyword_volume/).
*   **For Trend Data:** Use **pytrends** for free relative trend graphs by calling it from a Python microservice [^28](https://github.com/GeneralMills/pytrends).
*   **For DIY Difficulty:** The **Serper.dev + OpenPageRank API** mashup is a cost-effective way to create a proprietary difficulty score for just ~$0.001 per keyword.

---

## Feature 5: Domain Overview (Competitor Analysis)

This feature allows a user to enter any domain and receive an overview of its estimated organic traffic, top ranking keywords, a domain authority score, and backlink count. This is the most difficult feature to replicate with free tools due to the reliance on massive, proprietary datasets.

### The Hard Truth

Organic traffic estimation and top keyword data for competitor domains cannot be reliably obtained for free. This data is the primary value proposition of services like Ahrefs and Semrush, who invest millions in crawling the web. However, excellent free and low-cost partial solutions exist.

| Tool / API | Stars | Tech Stack | Cost Model | Traffic | DA Score | Top Keywords | Backlinks |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **OpenPageRank API** [^30](https://www.domcop.com/openpagerank/) | N/A | API | **Fully Free** | ❌ | ✅ (0-10) | ❌ | ❌ |
| **DataForSEO Labs** [^31](https://dataforseo.com/apis/domain-analytics-api) | N/A | API | ~$1/1k domains | ✅ | ✅ | ✅ | ✅ |
| **every-app/open-seo** [^21](https://github.com/every-app/open-seo) | 2,100 | TypeScript | Reference | ✅ | ✅ | ✅ | ✅ |
| **Moz Link API** [^32](https://moz.com/products/api) | N/A | API | 50 free/month | ❌ | ✅ (DA) | ❌ | ✅ |
| **SEO Panel** [^15](https://github.com/seopanel/seo-panel) | 850 | PHP | **Fully Free** | ❌ | ✅ (basic) | ❌ | ✅ (basic) |
| **GSC API** [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome) | N/A | API | **Fully Free** | ✅ (exact) | ❌ | ✅ (own) | ❌ |

### Analysis of Domain Overview Options

*   **OpenPageRank API:** The best *free* solution for domain authority. It provides a 0-10 PageRank-style score for any domain via a simple API call [^30](https://www.domcop.com/openpagerank/). Implementation in PHP is trivial.
*   **DataForSEO Labs `/domain_overview/live`:** The best *paid* solution. For approximately $1 per 1,000 domains, it provides a comprehensive overview including organic traffic estimates, top keywords, an authority score, and backlink counts [^31](https://dataforseo.com/apis/domain-analytics-api). This is essential for building a true competitor analysis tool.
*   **every-app/open-seo:** This 2,100-star repository is the best reference to study how to build a full Domain Overview feature on top of the DataForSEO API [^21](https://github.com/every-app/open-seo).
*   **Moz Link Explorer API:** While it provides the industry-standard "Domain Authority" (DA) score, the free tier of 50 requests/month is too limited for a production SaaS [^32](https://moz.com/products/api).
*   **SEO Panel:** A useful PHP-based project that includes some basic backlink and authority metrics, which could be used as a free building block [^15](https://github.com/seopanel/seo-panel).
*   **Ahrefs/SEMrush APIs:** These are the gold standard but are prohibitively expensive for a new SaaS, with API access often costing over $500/month.

### Best Picks for LaraSEOScan (Domain Overview)

*   **For Domain Authority:** Implement the **OpenPageRank API**. It's free, unlimited, and trivial to add, providing immediate value [^30](https://www.domcop.com/openpagerank/).
*   **For Full Competitor Analysis:** The **DataForSEO `domain_overview`** endpoint is the recommended paid solution. Community discussions on r/TechSEO confirm there is no viable free alternative for this level of data [^29](https://www.reddit.com/r/TechSEO/comments/1rn4e18/dataforseo_api_for_automated_keyword_volume/).
*   **For User's Own Domain:** Use the **Google Search Console API** to provide users with exact, free traffic and keyword data for their own connected sites [^16](https://developers.google.com/webmaster-tools/search-console-api/v1/welcome).

---

## Overall Recommended Stack & Cost Analysis

| Feature | Free Tier Implementation | Paid Tier (Best Value) | Est. Monthly Cost (10k user actions) |
| :--- | :--- | :--- | :--- |
| **Site Audit** | `spatie/crawler` + `backstagephp/laravel-seo-scanner` | N/A (fully free) | **$0** |
| **Position Tracking** | GSC API for user's own site | Serper.dev at $1 / 1,000 queries | **$10 - $50** |
| **Keyword Magic** | Google Suggest via `rmak78/keyword-suggest-tool` | DataForSEO at $0.60 / 1,000 keywords | **$6 - $60** |
| **Keyword Overview** | GSC API + pytrends + DIY difficulty | DataForSEO at $0.60 / 1,000 keywords | **$6 - $60** |
| **Domain Overview** | OpenPageRank API (authority score only) | DataForSEO at $1.00 / 1,000 domains | **$10 - $100** |

**Single Best Reference Codebase to Study:** The `every-app/open-seo` repository on GitHub is the most valuable resource for this project. It is a complete, open-source SaaS that implements all five features using DataForSEO, providing a clear blueprint for architecture and implementation [^21](https://github.com/every-app/open-seo).

---

## GitHub Repositories Quick Reference

| Repo | Stars | Language | Feature | Free? | GitHub Link |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **backstagephp/laravel-seo-scanner** | 266 | PHP | Site Audit | ✅ | [Link](https://github.com/backstagephp/laravel-seo-scanner) [^1](https://github.com/backstagephp/laravel-seo-scanner) |
| **spatie/crawler** | 3,800 | PHP | Site Audit | ✅ | [Link](https://github.com/spatie/crawler) [^2](https://github.com/spatie/crawler) |
| **StJudeWasHere/seonaut** | 280 | Go | Site Audit | ✅ | [Link](https://github.com/StJudeWasHere/seonaut) [^3](https://github.com/StJudeWasHere/seonaut) |
| **janreges/siteone-crawler** | 757 | PHP | Site Audit | ✅ | [Link](https://github.com/janreges/siteone-crawler) [^5](https://github.com/janreges/siteone-crawler) |
| **PhialsBasement/LibreCrawl** | 671 | Python | Site Audit | ✅ | [Link](https://github.com/PhialsBasement/LibreCrawl) [^6](https://github.com/PhialsBasement/LibreCrawl) |
| **sethblack/python-seo-analyzer** | 1,500 | Python | Site Audit | ✅ | [Link](https://github.com/sethblack/python-seo-analyzer) [^8](https://github.com/sethblack/python-seo-analyzer) |
| **towfiqi/serpbear** | 2,300 | TypeScript | Position Tracking | ⚠️ | [Link](https://github.com/towfiqi/serpbear) [^9](https://github.com/towfiqi/serpbear) |
| **serphacker/serposcope** | 1,100 | Java | Position Tracking | ✅ | [Link](https://github.com/serphacker/serposcope) [^12](https://github.com/serphacker/serposcope) |
| **seopanel/seo-panel** | 850 | PHP | Multi-Feature | ✅ | [Link](https://github.com/seopanel/seo-panel) [^15](https://github.com/seopanel/seo-panel) |
| **rmak78/keyword-suggest-tool** | 50 | PHP | Keyword Magic | ✅ | [Link](https://github.com/rmak78/keyword-suggest-tool) [^17](https://github.com/rmak78/keyword-suggest-tool) |
| **chukhraiartur/seo-keyword-research-tool** | 154 | Python | Keyword Magic | ✅ | [Link](https://github.com/chukhraiartur/seo-keyword-research-tool) [^18](https://github.com/chukhraiartur/seo-keyword-research-tool) |
| **eliasdabbas/advertools** | 1,400 | Python | Keyword Magic | ✅ | [Link](https://github.com/eliasdabbas/advertools) [^19](https://github.com/eliasdabbas/advertools) |
| **GeneralMills/pytrends** | 3,500 | Python | Keyword Overview | ✅ | [Link](https://github.com/GeneralMills/pytrends) [^28](https://github.com/GeneralMills/pytrends) |
| **dataforseo/php-client** | varies | PHP | Multiple Features | ❌ | [Link](https://github.com/dataforseo/php-client) [^27](https://github.com/dataforseo/php-client) |
| **every-app/open-seo** | 2,100 | TypeScript | All Features | ⚠️ | [Link](https://github.com/every-app/open-seo) [^21](https://github.com/every-app/open-seo) |

---

## Reddit Sources & Community Insights

*   **r/selfhosted:** The launch thread for `SerpBear` was met with positive reception, highlighting strong community interest in self-hosted SEO tools [^33](https://www.reddit.com/r/selfhosted/comments/z8t26k/i_built_an_open_source_search_engine_position/).
*   **r/localseo:** Recent discussions suggest that the developer behind `SerpBear` may be less active, causing some users to seek alternatives due to a lack of updates [^11](https://www.reddit.com/r/localseo/comments/1otz9oa/serpbear_simple_keyword_rank_tracking/).
*   **r/bigseo:** The developer of `Serposcope` confirmed the project is in "survival mode" and is not actively maintained, making it a risky choice for new projects [^14](https://www.reddit.com/r/bigseo/comments/hv0c1c/looking_for_open_source_rank_tracking_tool/).
*   **r/TechSEO:** `DataForSEO` is consistently recommended as the most affordable and reliable API for keyword and domain data, representing the "best bang for your buck" according to the community [^29](https://www.reddit.com/r/TechSEO/comments/1rn4e18/dataforseo_api_for_automated_keyword_volume/). Python tools like `advertools` and `pytrends` are also frequently praised.
*   **r/SEO_Digital_Marketing:** A January 2026 thread compares `LibreCrawl` favorably to the paid tool Screaming Frog, establishing it as a strong free alternative for site auditing [^7](https://www.reddit.com/r/SEO_Digital_Marketing/comments/1qdxxmp/librecrawl_free_seo_crawler_that_surpasses/).
*   **r/webdev:** The launch discussion for `SEOnaut` showed positive feedback from the developer community for its Go-based architecture and comprehensive audit features [^4](https://www.reddit.com/r/webdev/comments/1ej62lm/i_built_and_open_source_seo_auditing_tool/).

---

## Recommended Build Order for LaraSEOScan

To maximize user value while minimizing initial cost and complexity, the following build order is recommended:

1.  **Core Free Features (Phase 1):**
    *   **Site Audit:** Implement this first using the `spatie/crawler` engine and checks from `laravel-seo-scanner`. It is 100% free, PHP-native, and provides immediate, tangible value to users.
    *   **GSC Integration:** Add Google Search Console connectivity to provide users with free, accurate data on their own sites for Position Tracking and Keyword Overview.
    *   **Keyword Suggestions (No Volume):** Use the `rmak78/keyword-suggest-tool` or a simple cURL call to the Google Suggest endpoint to offer a free keyword idea generator.
    *   **Domain Authority:** Integrate the `OpenPageRank` API to provide a free domain authority score.

2.  **Value-Add Paid Features (Phase 2):**
    *   **Competitor Position Tracking:** Add SERP tracking for any keyword using the **Serper.dev** API. This is a high-demand feature with a low marginal cost.
    *   **Full Keyword Overview:** Enhance the keyword tools with volume, CPC, and difficulty data from the **DataForSEO** API.
    *   **Full Domain Overview:** Introduce the complete competitor analysis feature using the **DataForSEO** domain overview endpoint. This will be a key driver for higher-tier subscriptions.

## How this report was produced
This report was generated by a multi-agent AI system. An initial planning agent broke down the research task into five distinct feature categories. A web research agent then executed parallel searches on Google, GitHub, and Reddit to find relevant open-source tools, APIs, and community discussions for each feature. A data extraction agent processed the search results to pull specific details like GitHub stars, programming languages, license information, API pricing, and key features from the source URLs. Finally, this report was compiled by a writing agent, which synthesized all the structured data from the previous steps into a comprehensive and well-organized document, ensuring all data points were included and properly cited according to the initial request.