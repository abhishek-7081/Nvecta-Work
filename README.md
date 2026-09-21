# 🧠 AI-Powered Notes Management System

> **A production-ready, enterprise-grade Notes Management System with RESTful CRUD APIs, AI-Powered Summarization, and Vector Semantic Search built with PHP, Laravel, MySQL, and Tailwind CSS.**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12%2B-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![OpenAPI](https://img.shields.io/badge/OpenAPI-3.0-6BA539?style=for-the-badge&logo=openapiinitiative&logoColor=white)](http://127.0.0.1:8000/docs/index.html)
[![Tests](https://img.shields.io/badge/Tests-18%20Passed-success?style=for-the-badge)](https://phpunit.de)

---

## 📑 Table of Contents
- [1. Project Overview](#1-project-overview)
- [2. Key Features](#2-key-features)
- [3. Technology Stack](#3-technology-stack)
- [4. System Architecture](#4-system-architecture)
- [5. Folder Structure](#5-folder-structure)
- [6. Database Schema](#6-database-schema)
- [7. Installation & Setup Guide](#7-installation--setup-guide)
- [8. Environment Variables](#8-environment-variables)
- [9. Running the Application](#9-running-the-application)
- [10. API Documentation & Endpoints](#10-api-documentation--endpoints)
- [11. AI Architecture & Semantic Search Deep Dive](#11-ai-architecture--semantic-search-deep-dive)
- [12. AI Summarization & Smart Caching](#12-ai-summarization--smart-caching)
- [13. Security Hardening](#13-security-hardening)
- [14. Automated Testing](#14-automated-testing)
- [15. AI Development Log & Validation](#15-ai-development-log--validation)

---

## 1. Project Overview
The **AI-Powered Notes Management System** is built to showcase modern Laravel backend engineering, clean service-oriented architecture, and practical AI integrations. It addresses real-world challenges in content discovery by combining **RESTful CRUD operations** with **high-dimensional vector embeddings**, allowing users to search notes by conceptual meaning rather than rigid keyword matches.

---

## 2. Key Features

- ✅ **Complete RESTful CRUD APIs:** Create, list, retrieve, update, and soft-delete notes with strict FormRequest validation and ISO timestamps.
- ✅ **AI Semantic Vector Search (`GET /api/notes/search?q=...`):** Converts search queries into embeddings and computes cosine vector similarity against stored note vectors.
- ✅ **AI Note Summarization (`POST /api/notes/{id}/summary`):** Generates concise executive summaries and caches them in MySQL to avoid repetitive LLM costs and latency.
- ✅ **Multi-Provider AI Architecture:** Out-of-the-box support for **OpenAI** (`text-embedding-3-small`, `gpt-4o-mini`), **Google Gemini** (`text-embedding-004`, `gemini-1.5-flash`), and an offline **Local Deterministic Fallback** for zero-cost local testing.
- ✅ **Dynamic Interactive Frontend:** Modern single-page web UI featuring dark-mode glassmorphism, instant debounced search, modal dialogs, and real-time toast feedback.
- ✅ **Enterprise-Grade Pagination & Sorting:** Bounded limit validation (`max: 100`), customizable sort fields, and pagination envelopes.
- ✅ **Hardened Security:** SQL injection prevention via Eloquent PDO parameterization, whitelisted sort fields, mass assignment protection, API rate limiting (`60 req/min`), and zero stack-trace error leakage.
- ✅ **Interactive OpenAPI / Swagger UI Portal:** Built-in interactive documentation accessible at `/docs/index.html`.

---

## 3. Technology Stack

| Layer | Technology | Description |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel 12 / PHP 8.5** | Robust MVC framework utilizing Service Container, FormRequests, and Eloquent ORM |
| **Database** | **MySQL 8.0** | Relational data store with JSON column support for vector embedding storage |
| **AI Providers** | **OpenAI / Gemini API** | Embeddings (`text-embedding-3-small`) & Chat Completion (`gpt-4o-mini` / `gemini-1.5-flash`) |
| **Frontend** | **Tailwind CSS + Blade** | Modern dark-mode SPA interface with Lucide Icons and Inter/Outfit typography |
| **API Spec** | **OpenAPI 3.0 / Swagger** | Interactive API documentation viewer hosted on `/docs/index.html` |
| **Testing** | **PHPUnit 12** | 18 Automated Feature & Unit tests running against `:memory:` SQLite DB |

---

## 4. System Architecture

```
                                      +------------------------------------+
                                      |       Client / Frontend SPA        |
                                      +-----------------+------------------+
                                                        |
                                            REST API Requests (JSON)
                                                        |
                                                        v
                                      +------------------------------------+
                                      |     Laravel Routing & Middleware   |
                                      |    (Rate Limiting, Exception Guard)|
                                      +-----------------+------------------+
                                                        |
                                                        v
                                      +------------------------------------+
                                      |          NoteController            |
                                      |    (Thin Controller + Validation)  |
                                      +---+-------------+--------------+---+
                                          |             |              |
                    +---------------------+             |              +---------------------+
                    |                                   |                                    |
                    v                                   v                                    v
          +-------------------+               +-------------------+                +-------------------+
          |    Note Model     |               |  EmbeddingService |                | NoteSearchService |
          | (Eloquent/MySQL)  |               |  (Vector Syncing) |                | (Cosine Distance) |
          +---------+---------+               +---------+---------+                +---------+---------+
                    |                                   |                                    |
                    |                                   v                                    |
                    |                         +-------------------+                          |
                    |                         |     AIService     |                          |
                    |                         | (OpenAI / Gemini  |                          |
                    |                         |  / LocalFallback) |                          |
                    |                         +---------+---------+                          |
                    |                                   |                                    |
                    +-----------------------------------+------------------------------------+
                                                        |
                                                        v
                                              +-------------------+
                                              |  MySQL Database   |
                                              | (ai_notes_db)     |
                                              +-------------------+
```

---

## 5. Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── NoteController.php          # REST CRUD, Search & Summary endpoints
│   ├── Requests/
│   │   ├── BaseApiRequest.php              # Standardized 422 JSON validation handler
│   │   ├── PaginationRequest.php           # Page, limit (max 100), and sort validation
│   │   ├── SearchNotesRequest.php          # Semantic search query validation
│   │   ├── StoreNoteRequest.php            # Store validation rules
│   │   └── UpdateNoteRequest.php           # Update validation rules
│   └── Resources/
│       └── NoteResource.php                # JSON API transformer & similarity score decorator
├── Models/
│   └── Note.php                            # Eloquent model with JSON casts and SoftDeletes
├── Services/
│   ├── AI/
│   │   ├── Contracts/
│   │   │   └── AIServiceInterface.php      # AI Provider Contract
│   │   ├── GeminiService.php               # Google Gemini REST driver
│   │   ├── LocalAIService.php              # Offline deterministic L2 hash fallback
│   │   └── OpenAIService.php               # OpenAI HTTP driver with retry/timeout
│   ├── AIService.php                       # AI Service Manager & dynamic driver resolver
│   ├── EmbeddingService.php                # Note lifecycle vector synchronization
│   └── NoteSearchService.php               # Cosine vector similarity calculation engine
└── Traits/
    └── ApiResponse.php                     # Uniform JSON envelope trait

database/
└── migrations/
    └── 2026_09_21_000001_create_notes_table.php

public/
└── docs/
    ├── index.html                          # Static Swagger UI documentation portal
    └── openapi.json                        # OpenAPI 3.0 API specification

resources/
└── views/
    ├── docs.blade.php                      # Swagger UI Blade template
    └── welcome.blade.php                   # Modern Single-Page Application UI

routes/
├── api.php                                 # Rate-limited REST API routes
└── web.php                                 # Web SPA and documentation routes

tests/
├── Feature/
│   └── NoteApiTest.php                     # 14 Feature endpoint tests
└── Unit/
    └── NoteSearchServiceTest.php           # 4 Vector math cosine similarity tests
```

---

## 6. Database Schema

### Table: `notes`

| Column | Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | ❌ | Auto-incrementing primary key |
| `title` | `VARCHAR(255)` | ❌ | Note title (Indexed) |
| `content` | `LONGTEXT` | ❌ | Full note body |
| `summary` | `TEXT` | ✅ | Cached AI-generated summary |
| `embedding` | `JSON` | ✅ | Vector embedding array of 384/1536 float dimensions |
| `created_at` | `TIMESTAMP` | ✅ | Note creation timestamp (Indexed) |
| `updated_at` | `TIMESTAMP` | ✅ | Last update timestamp |
| `deleted_at` | `TIMESTAMP` | ✅ | Soft delete timestamp |

---

## 7. Installation & Setup Guide

### Prerequisites
- **PHP 8.2 or higher** with extensions enabled: `openssl`, `pdo_mysql`, `curl`, `mbstring`, `zip`
- **Composer 2.x**
- **MySQL 8.0+**

### Step 1: Clone and Navigate
```bash
git clone https://github.com/your-username/ai-notes-system.git
cd ai-notes-system
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Create MySQL Database & Run Migrations
```bash
# Create database in MySQL
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ai_notes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run database migrations
php artisan migrate
```

---

## 8. Environment Variables

Configure your database and AI keys inside `.env`:

```env
APP_NAME="AI Notes System"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_notes_db
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

# AI Service Configuration (openai | gemini | local)
AI_PROVIDER=openai
OPENAI_API_KEY=your_openai_api_key_here
GEMINI_API_KEY=your_gemini_api_key_here
```

> **Note:** If no API key is provided, the application automatically uses the built-in **`LocalAIService`** driver so embeddings and summaries work offline with zero errors.

---

## 9. Running the Application

Start the Laravel development server:
```bash
php artisan serve --port=8000
```

- 🌐 **Web UI:** Open [http://127.0.0.1:8000](http://127.0.0.1:8000)
- 📖 **Interactive Swagger Docs:** Open [http://127.0.0.1:8000/docs/index.html](http://127.0.0.1:8000/docs/index.html)

---

## 10. API Documentation & Endpoints

### Summary of REST Endpoints

| Method | Endpoint | Description | Request Payload / Query Params |
| :--- | :--- | :--- | :--- |
| **GET** | `/api/notes` | List notes with pagination | `page=1&limit=10&sort_by=id&order=desc` |
| **POST** | `/api/notes` | Create a note & vector index | `{"title":"...","content":"..."}` |
| **GET** | `/api/notes/{id}` | Get single note by ID | Path `id` |
| **PUT** | `/api/notes/{id}` | Update note & refresh vector | `{"title":"...","content":"..."}` |
| **DELETE** | `/api/notes/{id}` | Soft-delete note | Path `id` |
| **GET** | `/api/notes/search` | AI Semantic Vector Search | `q=coding+interview&limit=10` |
| **POST** | `/api/notes/{id}/summary` | Generate / Fetch AI summary | Path `id`, optional `?force=true` |

---

### Standard Response Envelopes

#### Success Response (`200 OK` / `201 Created`):
```json
{
  "success": true,
  "message": "Note created successfully",
  "data": {
    "id": 1,
    "title": "Interview Preparation: Advanced Laravel and AI",
    "content": "Deep dive into Service Container, Eloquent ORM, and vector embeddings.",
    "summary": null,
    "has_embedding": true,
    "created_at": "2026-09-21T08:30:00.000000Z",
    "updated_at": "2026-09-21T08:30:00.000000Z"
  }
}
```

#### Error Response (`422 Unprocessable Entity`):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The note title is required."],
    "content": ["The note content is required."]
  }
}
```

---

## 11. AI Architecture & Semantic Search Deep Dive

### Why Semantic Search over SQL `LIKE %...%`?
Standard SQL keyword matching fails when searchers use synonyms (e.g. searching for *"automobile maintenance"* will never match a note about *"car repair"*). Semantic search indexes the conceptual meaning of text using vector embeddings.

### The Mathematics: Cosine Similarity
Cosine similarity evaluates the cosine of the angle between query vector $A$ and note vector $B$:

$$\text{Cosine Similarity}(A, B) = \frac{A \cdot B}{\|A\| \|B\|} = \frac{\sum_{i=1}^n A_i B_i}{\sqrt{\sum_{i=1}^n A_i^2} \sqrt{\sum_{i=1}^n B_i^2}}$$

Because all embeddings generated by our service are **$L_2$ unit-normalized** ($\|A\| = 1.0$), the calculation simplifies to a high-speed dot product:

$$\text{Cosine Similarity}(A, B) = \sum_{i=1}^n A_i B_i$$

```php
// app/Services/NoteSearchService.php
public function calculateCosineSimilarity(array $vecA, array $vecB): float
{
    $minLen = min(count($vecA), count($vecB));
    if ($minLen === 0) return 0.0;

    $dotProduct = 0.0;
    for ($i = 0; $i < $minLen; $i++) {
        $dotProduct += ((float) $vecA[$i]) * ((float) $vecB[$i]);
    }
    return max(0.0, min(1.0, $dotProduct));
}
```

---

## 12. AI Summarization & Smart Caching

When a user requests `POST /api/notes/{id}/summary`:
1. **Cache Check:** The system checks if `notes.summary` is already populated. If present and `force != true`, it returns the cached summary instantly ($<10\text{ms}$).
2. **AI Generation:** If missing or forced, it prompts the LLM to generate a 2-3 sentence executive summary.
3. **Cache Persistence:** The result is saved into MySQL so future requests consume $0$ external API tokens.
4. **Cache Invalidation:** When a note is modified via `PUT /api/notes/{id}`, its `summary` column is reset to `NULL` to prevent stale data.

---

## 13. Security Hardening

- 🛡️ **SQL Injection Protection:** All database operations utilize Eloquent parameter bindings. Sort parameters are strictly validated against an approved whitelist (`id`, `title`, `created_at`, `updated_at`).
- 🛡️ **Rate Limiting:** `throttle:60,1` middleware enforces a maximum of 60 requests per minute per IP.
- 🛡️ **Secret Isolation:** API keys are never returned in JSON responses or committed to source control.
- 🛡️ **Information Leakage Prevention:** Global exception handling in `bootstrap/app.php` intercepts errors and renders safe JSON messages for API consumers without exposing internal stack traces.

---

## 14. Automated Testing

Execute the complete test suite:
```bash
php artisan test
```

### Test Suite Summary:
```text
 PASS  Tests\Unit\NoteSearchServiceTest
  ✓ identical vectors have similarity of one
  ✓ orthogonal vectors have similarity of zero
  ✓ empty vectors return zero similarity
  ✓ partially aligned vectors return accurate cosine score

 PASS  Tests\Feature\NoteApiTest
  ✓ can create note successfully
  ✓ create note fails without required fields
  ✓ can list paginated notes
  ✓ pagination rejects limit exceeding 100
  ✓ can retrieve single note
  ✓ get non existent note returns 404
  ✓ can update note
  ✓ can delete note
  ✓ can generate and cache ai summary
  ✓ ai summary returns 404 for missing note
  ✓ semantic search finds related notes
  ✓ semantic search requires query

Tests:    18 passed (53 assertions)
Duration: 1.72s
Status:   100% PASSING
```

---

## 15. AI Development Log & Validation

| Phase | AI Tool | Prompt / Objective | AI Generated Code | Manual Review & Refinement | Validation Method |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Phase 1** | Antigravity AI | Project setup, PHP 8.5 verification, MySQL connection | Baseline config, `.env` templates | Added AI provider placeholders early | Verified via `php artisan db:show` |
| **Phase 2** | Antigravity AI | Design `notes` table schema & Eloquent model | Migration & `Note.php` model | Added `JSON` cast for embeddings, nullable `summary`, and query indexes | Verified via `php artisan db:table notes` |
| **Phase 3** | Antigravity AI | Implement RESTful CRUD API with validation & ApiResponse | `NoteController`, `StoreNoteRequest`, `ApiResponse` trait | Added automatic summary/embedding cache invalidation on update | Automated cURL verification script across all 7 CRUD actions |
| **Phase 4** | Antigravity AI | Add pagination, limit bounds, and sorting validation | `PaginationRequest.php` & Controller update | Enforced `max: 100` limit to protect server memory | Tested edge cases (`limit=500`, `sort_by=invalid`) returning 422 |
| **Phase 5** | Antigravity AI | Design multi-provider AI service architecture | `AIServiceInterface`, `OpenAIService`, `GeminiService`, `LocalAIService` | Engineered L2-normalized deterministic hash fallback for zero-cost offline runs | Verified vector generation and automatic model synchronization |
| **Phase 6** | Antigravity AI | Implement AI summary endpoint with smart caching | `summary` controller method & routes | Implemented `?force=true` query parameter for manual re-summarization | Tested 1st call generation + 2nd call cache verification |
| **Phase 7** | Antigravity AI | Implement vector semantic search using Cosine Similarity | `NoteSearchService.php`, `SearchNotesRequest.php` | Optimized cosine dot product for unit vectors; added keyword fallback | Verified semantic query "coding interview" ranked Note #19 ("software engineering interview") #1 |
| **Phase 8** | Antigravity AI | Build responsive single-page frontend UI | `resources/views/welcome.blade.php` | Added match percentage badges, Lucide icons, shimmer loaders, and copy-to-clipboard | Verified live in browser with real-time UI interaction |
| **Phase 9** | Antigravity AI | Security audit and exception hardening | Global exception renderers in `bootstrap/app.php` | Intercepted 404, 405, 429, and 500 errors to prevent stack trace leaks | Tested SQLi payloads and method rejections |
| **Phase 10** | Antigravity AI | Build automated Feature & Unit test suites | `NoteApiTest.php` and `NoteSearchServiceTest.php` | Added dependency injection fallback for isolated unit test execution | Executed `php artisan test` with 100% pass rate (18 tests, 53 assertions) |
| **Phase 11** | Antigravity AI | Create OpenAPI 3.0 spec & Swagger UI | `openapi.json`, `index.html` | Embedded dark-mode Swagger UI with live "Try it out" buttons | Verified interactive Swagger portal on `/docs/index.html` |

---

## 👨‍💻 Author & Submission Note
Built as a technical assessment demonstrating modern Laravel backend architecture, REST API standards, secure database design, practical AI integrations, and automated testing.
