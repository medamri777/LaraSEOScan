# Comprehensive Technical Audit: WebCrawlPro

This report provides a comprehensive technical analysis of the WebCrawlPro SEO audit tool available at `https://webcrawl.gap3.co/audit` [^1](https://webcrawl.gap3.co/audit). The findings are based on direct interaction with the tool, a complete 1,000-page crawl of a sample website (`books.toscrape.com`), navigation of all UI sections, and analysis of the application's underlying JavaScript source code.

## Tool Overview

*   **Name:** WebCrawlPro [^1](https://webcrawl.gap3.co/audit)
*   **URL:** `https://webcrawl.gap3.co/audit` [^1](https://webcrawl.gap3.co/audit)
*   **Developer:** GAP3, Ahmedabad, India [^2](https://gap3.co)
*   **Type:** Free, no-login required, browser-based technical SEO crawler and audit tool [^1](https://webcrawl.gap3.co/audit)
*   **Privacy Policy:** No crawl data stored server-side; all data is stored in the user's browser localStorage [^3](https://webcrawl.gap3.co/privacy)
*   **Footer Credit:** "Made by gap3" link is present in the footer of all pages [^1](https://webcrawl.gap3.co/audit)
*   **Page Title:** "SEO Audit Dashboard | WebCrawlPro" [^1](https://webcrawl.gap3.co/audit)
*   **Favicon:** A custom blue globe icon representing the WebCrawlPro brand [^1](https://webcrawl.gap3.co/audit)

## Sidebar Navigation Structure

The application features a comprehensive sidebar navigation menu with 33 primary sections organized into 7 categories [^1](https://webcrawl.gap3.co/audit). Section badges indicate the number of items or issues found during the crawl.

### OVERVIEW (3 sections)
1.  Dashboard
2.  Site Information
3.  Crawl Statistics

### CONTENT & ON-PAGE (6 sections)
4.  All URLs (badge: 1000)
5.  Page Titles (badge: 543)
6.  Meta Descriptions (badge: 997)
7.  Headings (H1) (badge: 66)
8.  Content Analysis
9.  Duplicate Content

### LINKS (6 sections)
10. All Links
11. Anchor Text (badge: 6825)
12. Broken Links
13. Orphan Pages
14. Redirects
15. Redirect Chains

### RESOURCES (1 section)
16. Images

### TECHNICAL (10 sections)
17. URL Structure
18. Directives
19. Canonical Audit (badge: 1000)
20. robots.txt
21. Hreflang
22. Structured Data
23. Tech Stack (badge: 1000)
24. Discovery Sources
25. Performance
26. PageSpeed Audit

### VISUALIZATIONS (3 sections)
27. Site Tree Map
28. Crawl Graph
29. Issue Heatmap

### TOOLS (4+ sections)
30. Source Code Search
31. Custom Extraction
32. JS Discovery Test
33. Sitemap Generator
*(Additional tools like Crawl Comparison and Save/Load are also available but not always present in the main sidebar.)*

A "← Back to Services" button at the bottom of the sidebar links to the main gap3 services page [^2](https://gap3.co).

**Note:** Open Graph and Security sections were successfully navigated to and documented during the test crawl, but they are not listed in the permanent sidebar structure, suggesting they may be dynamically displayed based on crawl state or findings [^1](https://webcrawl.gap3.co/audit).

## Top Header Bar

The main header provides primary controls for initiating and managing a crawl [^1](https://webcrawl.gap3.co/audit).

![The top header bar of the WebCrawlPro interface, showing the logo, URL input field, and control buttons.](files/screenshot_2024-06-03_at_17.03.11.png)

*   **WebCrawlPro Logo:** Top-left blue globe icon [^1](https://webcrawl.gap3.co/audit).
*   **Session ID Badge:** A unique identifier for the current crawl session (e.g., "3eb1e647") [^1](https://webcrawl.gap3.co/audit).
*   **URL Input Field:** Placeholder text "Enter a site URL, e.g. example.com" [^1](https://webcrawl.gap3.co/audit).
*   **Proxy Offline Indicator:** An orange warning indicating the status of the backend CORS proxy [^1](https://webcrawl.gap3.co/audit).
*   **▷ Start Crawl Button:** The primary blue call-to-action to begin an audit [^1](https://webcrawl.gap3.co/audit).
*   **⚙ Config Button:** Opens the crawl configuration modal [^1](https://webcrawl.gap3.co/audit).
*   **⬇ Export XLSX Button:** Downloads a 24-tab Excel workbook of the crawl data [^1](https://webcrawl.gap3.co/audit).

## Live Stats Bar

A live statistics bar is displayed below the header during and after the crawl, providing real-time metrics [^1](https://webcrawl.gap3.co/audit).

![Live stats bar showing the final metrics for the completed crawl of books.toscrape.com.](files/screenshot_2024-06-03_at_17.06.30.png)

| Metric      | Final Value (books.toscrape.com) | Description                               |
|-------------|----------------------------------|-------------------------------------------|
| CRAWLED     | 1000                             | Number of pages successfully fetched      |
| FOUND       | 1000                             | Total number of unique URLs discovered    |
| ISSUES      | 0                                | Critical errors or crawl-halting issues   |
| ERROR PAGES | 0                                | Pages with 4xx or 5xx HTTP status codes |
| REDIRECTS   | 0                                | Pages with 3xx HTTP status codes          |
| ELAPSED     | 960s                             | Total time taken for the crawl (16 min)   |
| URLS/SEC    | 1.0                              | The average crawl speed                   |

A status indicator in the bottom footer bar confirms the final state: `Complete | URLs: 1000` [^1](https://webcrawl.gap3.co/audit).

## Crawl Configuration Modal

The "Config" button opens a modal with 15 distinct settings across three categories, allowing for detailed customization of the crawl behavior [^1](https://webcrawl.gap3.co/audit).

![The Crawl Configuration modal with settings for General, URL Discovery, and JavaScript Discovery.](files/screenshot_2024-06-03_at_17.03.22.png)

### General Settings
1.  **Max Crawl Depth:** Default value is 5.
2.  **Max URLs:** Default is 0, which signifies an unlimited number of URLs.
3.  **Concurrent Requests:** Default is 2.
4.  **Delay Between Batches ms:** Default is 500 milliseconds.
5.  **Request Timeout ms:** Default is 15000 milliseconds.
6.  **User Agent:** Default string is "WebCrawlPro/1.0 (Technical SEO Spider)".

### URL Discovery
7.  **Enable Sitemap Discovery:** This is a toggle, set to ON by default. An optional field allows for a custom sitemap URL.
8.  **Note:** The system uses a heuristic fallback for shared hosting environments automatically.

### JavaScript Discovery
9.  **Enable JS Rendering:** Toggle set to ON by default.
10. **Enable Infinite Scroll Detection:** Toggle set to ON by default.
11. **Continue Until No New Pages:** Toggle set to ON by default.
12. **Max JS Pages:** Default is 25 (enabled when JS Rendering is on).
13. **Max Clicks:** Default is 2 (enabled when JS Rendering is on).
14. **Max Scroll Steps:** Default is 3 (enabled when Infinite Scroll is on).
15. **Max Listing Pages:** Default is 25 (enabled when JS Rendering is on).

---

## Section-by-Section Documentation

The following is a detailed breakdown of each section in the sidebar, based on the completed crawl of `books.toscrape.com`.

### 1. Dashboard (Overview)

The dashboard provides a high-level summary of the completed crawl [^1](https://webcrawl.gap3.co/audit).

![The final dashboard view after the 1000-page crawl, showing summary cards, Core Web Vitals, and Top Issues.](files/screenshot_2024-06-03_at_17.07.03.png)

**Components:**
*   **4 top-level summary cards:**
    *   **Total URLs:** 1000 (1000 indexable)
    *   **Errors:** 0 (0 broken | 0 on-page)
    *   **Warnings:** 1611 (breakdown: Meta 997, Title 543, Content 72, across 1000 pages)
    *   **Avg Response:** 230ms (Fastest: 220ms)
*   **Homepage Core Web Vitals panel:** Mobile PSI 98/100, Desktop PSI 100/100, Mobile LCP 2.5s, Mobile INP 95ms, Mobile CLS 0.
*   **Status Code Distribution:** A donut chart indicating all 1,000 pages returned a 2xx status code.
*   **Issue Breakdown:** A pie/donut chart visualizing the distribution of issue types.
*   **Sitemap Discovery Coverage:** A small card summarizing sitemap URL findings.
*   **Top Issues list** (color-coded):
    *   Broken Pages (4xx/5xx): 0 issues ✅
    *   Missing Page Titles: 0 issues ✅
    *   Missing Meta Descriptions: 104 issues ⚠️
    *   Missing H1 Tags: 0 issues ✅
    *   Duplicate Titles: 66 issues ⚠️
    *   Redirect Chains: 0 issues ✅
    *   Images Missing Alt: 0 issues ✅
    *   Slow Pages (>1s): 0 issues ✅

### 2. Site Information (Overview)

This section details metadata and SERP appearance for the primary crawl target [^1](https://webcrawl.gap3.co/audit).

![The Site Information section, displaying a SERP preview and metadata for the homepage.](files/screenshot_2024-06-03_at_17.07.19.png)

**Components:**
*   **4 summary cards:** PAGES ANALYSED (1000), TRACKING CODES (0), SCHEMA GROUPS (0), PREVIEW ISSUES (12).
*   **PRIMARY PAGE SNAPSHOT** (homepage analysis):
    *   Fields: URL, Final URL, Canonical, Server, Language, Generator, Favicon.
    *   Favicon URL shown: `https://books.toscrape.com/static/oscar/favicon.ico`.
*   **SERP PREVIEW:** A visual simulation of how the page appears in Google search results.
    *   Shows warnings for "Missing meta description" and "Missing canonical".
*   **Issue badges list:** Missing meta description, Missing canonical, Missing og:title, og:description, og:image, twitter:card, twitter:title, twitter:description, twitter:image.
*   **TRACKING CODES table:** This table was empty for `books.toscrape.com`.
    *   Columns: Tracking Code, Category, ID/Key, Pages, Hits, Sample Page.

### 3. Crawl Statistics (Overview)

This section offers a deep dive into the crawl metrics with numerous charts and data points [^1](https://webcrawl.gap3.co/audit).

![The Crawl Statistics page, featuring multiple bar and donut charts for various metrics.](files/screenshot_2024-06-03_at_17.07.45.png)

**9 Charts documented:**

| Chart # | Chart Name                   | Type          | Key Data                                               |
|---------|------------------------------|---------------|--------------------------------------------------------|
| 1       | Status Code Distribution     | Donut         | 2xx: 1000 (100% healthy)                               |
| 2       | Response Time Distribution   | Bar           | Peak in the 200-500ms bucket (~950 URLs)               |
| 3       | Page Size Distribution       | Bar           | Distribution across various page size buckets          |
| 4       | Word Count Distribution      | Bar           | Range from 0 to over 800 words                         |
| 5       | Issues by Category           | Bar           | Title 543, Meta 997, H1 66, Content 204, Canonical 1000 |
| 6       | Pages by Crawl Depth         | Bar           | L0:1, L1:7, L2:512, L3:214, L4:129, L5:71               |
| 7       | Internal Links per Page      | Bar           | Distribution of internal link counts per page          |
| 8       | Response Time vs Page Size   | Scatter Plot  | Visualizes the correlation between response time and size |
| 9       | Content Types                | Bar           | html: 1000 (all discovered pages were HTML documents)  |

**8 Quick Stats Cards:**
*   Total URLs: 1000
*   Indexable Pages: 1000
*   Total Links: 14,912
*   Total Images: 7,723
*   Total Issues: 2,810
*   Average Response Time: 230ms
*   Average Word Count: 365
*   Images without Alt Text: 0

### 4. All URLs (Content & On-Page)

This section provides a master table of all discovered URLs with key metrics [^1](https://webcrawl.gap3.co/audit).
**12 Columns:** URL, Discovery, Status, Page Title, Title Len, Words, Size, Time, Int Links, Ext Links, Depth, Issues.
**Features:** Status code filter tabs, rows per page selector (25/50/100), pagination, and a text filter input.

### 5. Page Titles (Content & On-Page)

This section analyzes the `<title>` tags of all pages [^1](https://webcrawl.gap3.co/audit).
**5 Tabs:** All (543), Missing, Duplicate, Too Long, Too Short.
**4 Columns:** URL, Page Title, Length, Status.

### 6. Meta Descriptions (Content & On-Page)

Analyzes meta description tags across the site [^1](https://webcrawl.gap3.co/audit).
**5 Tabs:** All (997), Missing, Duplicate, Too Long, Too Short.
**4 Columns:** URL, Meta Description, Length, Status.

### 7. Headings H1 (Content & On-Page)

Focuses on the presence and structure of `<h1>` tags [^1](https://webcrawl.gap3.co/audit).
**3 Tabs:** All (66), Missing, Multiple.
**4 Columns:** URL, H1 Tags, Count, H2s, Status.

### 8. Content Analysis (Content & On-Page)

Provides insights into the textual content of pages [^1](https://webcrawl.gap3.co/audit).
**3 Tabs:** All (1000), Thin Content (31), Low Content (34).
**5 Columns:** URL, Words, Readability, Level, Sentences.

### 9. Duplicate Content (Content & On-Page)

This section uses the SimHash algorithm to identify both exact and near-duplicate content [^1](https://webcrawl.gap3.co/audit).

![The Duplicate Content section showing an overview of exact and near-duplicate findings.](files/screenshot_2024-06-03_at_17.06.01.png)

**3 Tabs:** Overview, Exact Duplicates (1 group), Near Duplicates (568 pairs).
**Summary Cards:** 1 Exact Duplicate Group, 2 Exact Duplicate Pages, 568 Near-Duplicate Pairs, 97% Max Similarity.

### 10. All Links (Links)

A comprehensive log of all hyperlinks found during the crawl [^1](https://webcrawl.gap3.co/audit).
**2 Tabs:** Internal (6,308), External (0).
**5 Columns:** Source Page, Target URL, Anchor Text, Nofollow, Rel.

### 11. Anchor Text (Links)

Analyzes the distribution and usage of anchor text for internal links [^1](https://webcrawl.gap3.co/audit).
**4 Tabs:** Distribution (55), All Links (7,211), Empty (2,664), Generic (0).
**6 Columns:** Anchor Text, Usage Count, Internal, External, Unique Targets, Nofollow.

### 12. Broken Links (Links)

This section reports on links pointing to pages with 4xx or 5xx status codes [^1](https://webcrawl.gap3.co/audit).
**State for `books.toscrape.com`:** "No broken links or errors found."

### 13. Orphan Pages (Links)

Identifies pages that have no incoming internal links [^1](https://webcrawl.gap3.co/audit).

![The Orphan Pages section showing metrics and a table of pages with low incoming link counts.](files/screenshot_2024-06-03_at_17.06.12.png)

**6 Summary Cards:** Total Crawled, Analyzed 2xx, Excluded, Orphan Pages (0), Low Link Pages ≤2 (25), Avg Incoming Links (15.6).
**4 Columns:** URL, Incoming Links, Outgoing Links, Title.
**Result:** 0 orphan pages found.

### 14. Redirects (Links)

Lists all URLs that returned a 3xx status code [^1](https://webcrawl.gap3.co/audit).
**State for `books.toscrape.com`:** "No redirects found."

### 15. Redirect Chains (Links)

Identifies sequences of multiple redirects [^1](https://webcrawl.gap3.co/audit).
**3 Tabs:** All Chains (0), Loops (0), Long Chains 3+ (0).
**State:** "No redirect chains detected."

### 16. Images (Resources)

Provides a detailed audit of all image resources [^1](https://webcrawl.gap3.co/audit).
**4 Tabs:** All Occurrences (4,090), Grouped Usage (1,175), Missing Alt (0), Heavy Assets (0).
**5 Summary Cards:** 4,090 Occurrences, 1,175 Unique Assets, 516 Reused Assets, 0 Missing Alt, 287.6 MB Estimated Weight.
**6 Columns:** Page, Image Source, Format, Est. Weight, Has Alt, Loading.

### 17. URL Structure (Technical)

Analyzes the structure, depth, and syntax of URLs [^1](https://webcrawl.gap3.co/audit).
**5 Tabs:** Overview, All URLs (625), Issues (620), With Parameters (0), Long URLs (156).
**4 Summary Stats:** 627 Total URLs, 88 chars Avg Length, 5 Max Depth, 622 With Issues.
**2 Charts:** URL Depth Distribution and File Extensions.

### 18. Directives (Technical)

Examines meta robots and canonical directives on each page [^1](https://webcrawl.gap3.co/audit).
**6 Columns:** URL, Meta Robots, Canonical, Self Canon., Indexable, Nofollow.
**Data from crawl:** All pages showed `NOARCHIVE,NOCACHE` meta robots directives.

### 19. Canonical Audit (Technical)

Focuses specifically on the implementation of `rel="canonical"` tags [^1](https://webcrawl.gap3.co/audit).
**5 Tabs:** Overview, All (1000), Missing (577), Non-Self (0), Issues (0).
**4 Summary Cards:** With Canonical (0), Missing (577), Self-Referencing (577), Non-Self (0).

### 20. robots.txt (Technical)

Analyzes the `robots.txt` file and provides simulation tools [^1](https://webcrawl.gap3.co/audit).
**5 Tabs:** Overview, Rules (0), Blocked (0), URL Tester, Custom robots.txt.
**3 Summary Cards:** ROBOTS.TXT STATUS (Not found or not fetched), RULES COUNT (0), BLOCKED URLS (0 of 1000).

### 21. Hreflang (Technical)

Audits `hreflang` tags for international SEO [^1](https://webcrawl.gap3.co/audit).
**State for `books.toscrape.com`:** "No hreflang tags found in the crawled pages."

### 22. Structured Data (Technical)

Validates JSON-LD and other structured data formats [^1](https://webcrawl.gap3.co/audit).
**State for `books.toscrape.com`:** "No valid schema types were detected yet."

### 23. Open Graph & Social Media (Technical)

Analyzes Open Graph and Twitter Card tags for social media sharing [^1](https://webcrawl.gap3.co/audit).
**4 Tabs:** Overview, All Pages (837), Missing OG (837), Preview Issues (837).
**4 Summary Cards:** Pages With OG (0), Pages Missing OG (837), Pages With Twitter Tags (0), Preview Issues (837).
**3 Preview Panels:** Search Preview, Facebook / LinkedIn, X / Twitter.

### 24. Security (Technical)

Audits key security headers and HTTPS implementation [^1](https://webcrawl.gap3.co/audit).

![The Security section table, showing HTTPS and HSTS as implemented, but other headers as missing.](files/screenshot_2024-06-03_at_17.06.21.png)

**7 Columns:** URL, HTTPS, HSTS, CSP, X-Frame, X-Content, Server.
**Data from crawl:** HTTPS: Yes ✅, HSTS: Yes ✅, CSP: No ❌, X-Frame: No ❌, X-Content: No ❌.

### 25. Tech Stack (Technical)

Identifies the technologies used on the crawled website [^1](https://webcrawl.gap3.co/audit).

![The Tech Stack section, identifying Bootstrap and jQuery on the target site.](files/screenshot_2024-06-03_at_17.06.26.png)

**4 Summary Cards:** Detected Technologies (3), Categories Hit (3), Outdated Signals (0), Pages Analysed (1000).
**Detected on `books.toscrape.com`:** Bootstrap, jQuery, Strict-Transport-Security.
**Tables:** Technology Overview (7 Columns) and Per-Page Technology (6 Columns).

### 26. Discovery Sources (Technical)

Details how each URL was discovered by the crawler [^1](https://webcrawl.gap3.co/audit).
**8 Summary Cards:** PRIMARY CRAWL (999), PRIMARY SITEMAP (0), PRIMARY JS (0), and others.
**8 Columns:** URL, Primary Source, All Sources, JS Check, JS URLs, Source Count, Sitemaps, Parents.

### 27. Performance (Technical)

Combines crawl metrics with PageSpeed Insights data for a performance overview [^1](https://webcrawl.gap3.co/audit).
**14 Columns:** URL, Status, Mobile PSI, Desktop PSI, Mobile LCP, Mobile INP, Mobile CLS, Response Time, Page Size, Words, Images, Int Links, Ext Links, Depth.

### 28. PageSpeed Audit (Technical)

Provides a cached Google PageSpeed Insights report for the homepage [^1](https://webcrawl.gap3.co/audit).
**2 Tabs:** Audit Report, Resource Signals.
**Homepage metrics:** Response: 231ms, HTML Size: 50.1 KB, DOM Nodes: 541, Blocking Resources: 8.

### 29. Site Tree Map (Visualizations)

Offers a hierarchical view of the website's structure [^1](https://webcrawl.gap3.co/audit).

![The Site Tree Map visualization, showing a hierarchical directory structure and a depth distribution chart.](files/screenshot_2024-06-03_at_17.08.19.png)

**2 View Toggles:** Tree View, Sunburst.
**Controls:** Expand All, Collapse All.
**Chart:** A bar chart shows the URL Depth Distribution.

### 30. Crawl Graph (Visualizations)

A D3-powered force-directed network graph of the site's internal linking structure [^1](https://webcrawl.gap3.co/audit).

![The Crawl Graph visualization, displaying a network of nodes representing pages and links.](files/screenshot_2024-06-03_at_17.08.31.png)

**4 Tabs:** Crawl Graph, Most Linked (15), Most Linking (15), Link Flow Summary.
**Legend:** Node size corresponds to incoming links; color corresponds to HTTP status.

### 31. Issue Heatmap (Visualizations)

A color-coded grid that shows the density and type of issues across all pages [^1](https://webcrawl.gap3.co/audit).

![The Issue Heatmap, a color-coded table showing the distribution of different issue types across URLs.](files/screenshot_2024-06-03_at_17.08.43.png)

**Severity Legend:** None (white), Low (amber), Medium (orange), High (red).
**Sort Options:** Most Issues, URL, Status.
**7 Columns:** URL, TOTAL, TITLE, META, H1, CONTENT, CANONICAL.

### 32. Source Code Search (Tools)

A powerful tool to search the raw HTML source of all crawled pages using regular expressions [^1](https://webcrawl.gap3.co/audit).

![The Source Code Search tool, featuring a regex input field and 10 preset search buttons.](files/screenshot_2024-06-03_at_17.06.34.png)

**10 Quick Search Preset Buttons:** Google Analytics, Google Tag Manager, Facebook Pixel, Email Addresses, Phone Numbers, TODO / FIXME, Console.log, Lorem Ipsum, Inline Styles, HTTP Links.

### 33. Custom Extraction (Tools)

Allows users to extract specific data from pages using CSS Selectors or Regex [^1](https://webcrawl.gap3.co/audit).

![The Custom Extraction tool, showing preset buttons and a rule builder for custom data extraction.](files/screenshot_2024-06-03_at_17.06.44.png)

**8 Quick Add Preset buttons:** OG Title, OG Image, Schema Type, WordPress Version, Generator Meta, All Script Srcs, All Stylesheet Hrefs, Author Meta.
**Rule Builder Table:** Allows defining custom extraction rules.

### 34. JS Discovery Test (Tools)

A single-page utility to test JavaScript rendering and discovery settings before running a full crawl [^1](https://webcrawl.gap3.co/audit).

![The JS Discovery Test tool, designed to test JavaScript rendering on a single URL.](files/screenshot_2024-06-03_at_17.06.52.png)

**Components:** URL input, "Run JS Test" button, and various status cards and settings.

### 35. Sitemap Generator (Tools)

Generates a `sitemap.xml` file based on the crawl data [^1](https://webcrawl.gap3.co/audit).

![The Sitemap Generator tool, with options for inclusion, exclusion, and export.](files/screenshot_2024-06-03_at_17.07.00.png)

**5 Sitemap Options:** Include `lastmod`, `priority`, `changefreq`, and options to only include indexable or 2xx pages.
**Actions:** Download `sitemap.xml`, Copy to Clipboard, Show XML Preview.

### 36. Crawl Comparison

This tool allows comparing the current crawl against a previously saved JSON export to identify changes [^1](https://webcrawl.gap3.co/audit).

![The Crawl Comparison tool, which allows loading a baseline crawl from a JSON file.](files/screenshot_2024-06-03_at_17.07.08.png)

**Feature:** A single "Load Baseline Crawl (JSON)" button.

### 37. Save / Load

This section provides functionality for exporting and importing entire crawl sessions [^1](https://webcrawl.gap3.co/audit).

![The Save & Load section, with options to export the current crawl or import a previous one.](files/screenshot_2024-06-03_at_17.07.12.png)

**Export Options:** "Export as JSON File" or "Save to Browser Storage".
**Import Option:** "Load JSON File".

---

## Export Options

The tool offers multiple ways to export crawl data [^1](https://webcrawl.gap3.co/audit).

| Export Format     | Details                                                                    |
|-------------------|----------------------------------------------------------------------------|
| XLSX (Excel)      | A 24-tab workbook, exported via the "Export XLSX" button in the header.    |
| JSON              | A full crawl data export available from the Save/Load section, for re-import. |
| XML Sitemap       | Generated by the Sitemap Generator, downloadable as a `.xml` file.         |
| Browser Storage   | Saves the crawl to localStorage for persistence across browser sessions.   |
| Clipboard         | The sitemap XML content can be copied directly to the clipboard.           |

---

## Crawl Results Summary (books.toscrape.com)

The 1,000-page crawl provided the following key insights [^1](https://webcrawl.gap3.co/audit):

### Performance
*   **Crawl Speed:** 1.0 URL/sec
*   **Total Time:** 960 seconds (16 minutes)
*   **Avg Response Time:** 230ms
*   **Avg Word Count:** 365 words/page

### Content Health
*   **Status Codes:** 100% 2xx (all pages healthy)
*   **Broken Links:** 0
*   **Redirects:** 0
*   **Error Pages:** 0
*   **Orphan Pages:** 0

### Issues Found
*   **Total Issues (all types):** 2,810
*   **Missing Meta Descriptions:** 104 pages
*   **Duplicate Page Titles:** 66 pages
*   **Missing Canonical Tags:** 577 pages
*   **Thin Content:** 31 pages
*   **Duplicate Content (Exact):** 1 group (2 pages)
*   **Near-Duplicate Pages:** 568 pairs (avg 87% similar)
*   **Empty Anchor Text:** 2,664 instances

### Link Profile
*   **Total Internal Links:** 14,912
*   **Total External Links:** 0
*   **Total Images:** 7,723 occurrences
*   **Images Missing Alt Text:** 0

### Technologies Detected
*   Bootstrap (CSS Framework)
*   jQuery (JavaScript Library)
*   Strict-Transport-Security

---

## Technology Stack (WebCrawlPro)

Analysis of the application's source code reveals a modern web technology stack [^4](https://webcrawl.gap3.co/_next/static/chunks/app/audit/page-7a219debdd40115d.js)[^5](https://webcrawl.gap3.co/_next/static/chunks/322-3458424d0bb3620b.js)[^6](https://webcrawl.gap3.co/_next/static/chunks/452-aa7a434e429fb999.js).

### Frontend Framework
*   **Next.js** (App Router)
*   **React**

### CSS & Design
*   **Tailwind CSS**
*   **DM Sans** (Google Font)
*   **Custom design system:** Emerald green (`#10b981`), Amber (`#f59e0b`), Rose (`#ef4444`).

### Data Visualization
*   **Recharts** (Primary charting library)
*   **D3.js** (for the force-directed Crawl Graph)

### UI Components
*   **Lucide React** (Icon library)
*   **Decimal.js** (For precise arithmetic)

### Analytics & APIs
*   **Google Analytics 4 (GA4):** Tracking ID `G-M6699ETFJ1` [^1](https://webcrawl.gap3.co/audit).
*   **Google PageSpeed Insights API**

### Backend & Architecture
*   **Next.js API routes** (as a backend proxy for CORS)
*   **Browser localStorage** (client-side data storage)
*   **SimHash algorithm** (for near-duplicate detection)

---

## How this report was produced

This report was generated by a multi-agent system. An initial agent performed a series of automated extractions on the target URL and its related pages to gather information about the tool's features, technology stack, and policies. A second agent then took control of an interactive browser session to navigate to the tool, configure a crawl, and execute it on a sample website (`books.toscrape.com`). During the 16-minute crawl, the agent systematically navigated to every one of the 37+ sections in the application's UI, documenting the layout, data columns, charts, and unique features of each. Finally, this writer agent synthesized the complete history of extractions and interactions into this comprehensive, structured report, embedding contextual screenshots and citing all sources.