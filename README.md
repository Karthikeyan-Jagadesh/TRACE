# TRACE — Lost & Found Intelligence Network

> **"Every lost thing leaves a trace."**  
> *A smarter way to reconnect lost belongings with the people who matter.*

---

## 1. Executive Summary & Product Vision

**TRACE** is a full-featured, production-quality PHP web application designed to replace fragmented lost-and-found noticeboards, chat groups, and generic classified listings with a centralized, privacy-conscious intelligence network.

Built from first principles without heavy third-party CMS bloat or paid external APIs, TRACE combines:
- **The Clarity of a Modern Search Engine:** Instant keyword, category, status, and date-range filtering with server-side pagination.
- **The Precision of a Case-Management System:** Multi-signal explainable matching, multi-step claim lifecycles, and cryptographic ownership verification.
- **The Visual Quality of a Premium Consumer Application:** An original "Spatial Editorial Intelligence" design system featuring Obsidian surfaces (`#111310`), Warm Ivory daylight canvas (`#F4F2EA`), restrained Electric Lime accents (`#D7FF64`), and precision typography (*Plus Jakarta Sans* and *Space Mono*).

---

## 2. Key Features

### 🔍 Deterministic Explainable Smart Matching Engine
- **Zero Paid APIs / Explainable AI:** A transparent, deterministic multi-signal scoring model implemented in PHP.
- **Signal Breakdown:**
  | Signal | Weight | Logic & Evaluation |
  | :--- | :---: | :--- |
  | **Category** | 20% | Strict category match (1.0) vs. incompatible category penalty (0.0). |
  | **Item Name** | 20% | Normalized tokenization, stop-word stripping, Jaccard & Overlap similarity. |
  | **Description** | 15% | Token overlap across normalized descriptions. |
  | **Brand** | 10% | Case-insensitive token match on registered brand names. |
  | **Color** | 10% | Strict color match or token occurrence. |
  | **Date Proximity** | 10% | Configurable exponential/linear time decay ($e^{-\Delta t / 30}$ days). |
  | **Geographic Proximity** | 15% | Spherical distance via the Haversine formula ($R = 6371\text{ km}$). |
- **Confidence Bands:** Strong Match ($\ge 80\%$), Possible Match ($60\text{--}79\%$), Weak Match ($40\text{--}59\%$), Hidden ($< 40\%$).
- **Explainability:** Generates human-readable rationales (e.g., *"Matched on Category, Brand (Sony), Title similarity, and reported within 0.1 km on the same day"*).

### 🔒 Secret Ownership Verification & Privacy Architecture
- **Zero Public Exposure:** Sensitive identifying attributes (e.g., engravings, serial codes, distinctive scratches, lock screen photos) are stored in a dedicated `report_private_details` table and never rendered in public HTML, API responses, or client JavaScript.
- **Verification Workflow:** Finders set a verification challenge. Claimants must provide verifiable evidence during their claim submission before any return coordination or physical handover instructions are revealed.

### 📋 Full Claim Lifecycle & State Machine
- **Strict State Transitions:** `submitted` $\rightarrow$ `under_review` $\rightarrow$ `approved` / `rejected` $\rightarrow$ `completed`.
- **Atomic Operations:** Transactional database transitions update both claim status and parent report availability simultaneously.
- **Audit Logging:** Every status change, note, and review action is recorded with actor ID, timestamp, and transition metadata.

### 🗺️ Geographic & Spatial Intelligence
- **Leaflet.js + OpenStreetMap:** Free, open-source mapping with custom lost (Amber `#F2B84B`) and found (Lime `#D7FF64`) coordinates.
- **Privacy Fuzzing:** Coordinate precision is capped for sensitive personal items to protect residential privacy.
- **Dual View:** Users can toggle seamlessly between spatial map view and high-density grid view.

### ⏱️ Automated Report Lifecycle & Expiration
- **Configurable Policies:** Lost reports default to 45-day retention; found reports default to 90 days.
- **CLI Runner:** Includes `bin/expire-reports.php` for scheduled cron execution or automated task triggers.

### 🛡️ Enterprise Security Suite
- **SQL Injection Prevention:** 100% prepared PDO statements across all repositories and search queries.
- **XSS Sanitization:** `Helper::escape()` applied on all dynamic view output.
- **CSRF Defense:** Cryptographic session tokens validated on all state-altering `POST` requests.
- **Role-Based Access Control:** Strict separation between unauthenticated guests, verified users, and platform administrators.
- **Brute-Force Rate Limiting:** Cooldown protection on authentication and claim verification endpoints.

---

## 3. Technology Stack

- **Backend:** PHP 8.2+ (Modular MVC architecture, zero heavy framework lock-in)
- **Database:** MySQL 8.0+ / MariaDB 10.4+ (InnoDB, UTF8mb4, Normalized 3NF Schema)
- **Frontend:** HTML5, Vanilla JavaScript, Tailwind CSS (Design Token System)
- **Mapping:** Leaflet.js (v1.9.4) + OpenStreetMap
- **Typography:** Plus Jakarta Sans (Interface), Space Mono (Technical Data & Identifiers)
- **Icons & Assets:** Native SVG micro-icons and responsive styling

---

## 4. Directory Structure

```text
trace/
├── app/
│   ├── Controllers/          # Request orchestrators
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── ClaimController.php
│   │   ├── DashboardController.php
│   │   ├── HomeController.php
│   │   ├── MatchController.php
│   │   ├── NotificationController.php
│   │   ├── ReportController.php
│   │   └── SearchController.php
│   ├── Core/                 # Zero-dependency framework kernel
│   │   ├── Autoloader.php
│   │   ├── Database.php      # PDO Singleton & Transaction Manager
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── Session.php       # Strict session & CSRF engine
│   │   └── View.php
│   ├── Helpers/
│   │   └── Helper.php        # String, date, security & formatting helpers
│   ├── Middleware/
│   │   ├── AdminMiddleware.php
│   │   ├── AuthMiddleware.php
│   │   └── CsrfMiddleware.php
│   ├── Repositories/         # PDO data access layer
│   │   ├── AuditLogRepository.php
│   │   ├── ClaimRepository.php
│   │   ├── MatchRepository.php
│   │   ├── NotificationRepository.php
│   │   ├── ReportRepository.php
│   │   └── UserRepository.php
│   ├── Services/             # Pure domain logic
│   │   ├── AuthService.php
│   │   ├── ClaimService.php
│   │   ├── ExpirationService.php
│   │   ├── MatchingService.php
│   │   └── ReportService.php
│   └── Validators/
│       └── Validator.php
├── bin/
│   └── expire-reports.php    # CLI automated report expiration runner
├── config/
│   ├── app.php               # Environment & system configurations
│   └── database.php          # Database credentials & PDO settings
├── database/
│   ├── schema.sql            # Complete DDL: 12 normalized tables with indexes
│   └── seed.php              # Realistic seed data generator
├── public/
│   ├── .htaccess             # URL rewriting for Apache
│   ├── index.php             # Front controller
│   ├── assets/
│   │   ├── css/trace.css     # Design tokens, variables & custom aesthetics
│   │   └── js/trace.js       # Client interactions, Leaflet maps & theme engine
│   └── uploads/              # Validated user file uploads
├── resources/
│   └── views/                # Modular PHP templates
│       ├── admin/            # Moderation, Claims, Users & Audit logs
│       ├── auth/             # Login & Registration
│       ├── claims/           # Claim submission, detail & coordination
│       ├── dashboard/        # Overview, My Reports & Settings
│       ├── home/             # Landing page & 404
│       ├── layouts/          # Header, Footer, Main layout
│       ├── matches/          # Candidate comparisons & score breakdown
│       ├── notifications/    # Notification center
│       ├── reports/          # Multi-step lost/found forms & report view
│       └── search/           # Filterable search gallery & spatial map
├── routes/
│   └── web.php               # Route definitions
├── storage/
│   └── logs/                 # Security, matching & application error logs
├── tests/
│   ├── AuthTest.php          # Authentication, password hashing & ACL tests
│   ├── ClaimWorkflowTest.php # Claim lifecycle & state transitions
│   └── MatchingServiceTest.php # Deterministic scoring & Haversine tests
├── .env                      # Local environment overrides
├── .env.example
├── composer.json
└── README.md
```

---

## 5. Database Architecture

The schema contains 12 normalized tables configured with foreign key constraints, indexes, and transactional support:

1. **`organizations`**: Campus and enterprise workspaces.
2. **`users`**: User records, hashed passwords (`password_hash`), roles (`user`, `admin`), account status (`active`, `suspended`).
3. **`reports`**: Lost and found items with identifiers (e.g. `TRC-L-2026-00124`), status (`active`, `under_review`, `claimed`, `recovered`, `expired`, `closed`), coordinates, and expiration timestamps.
4. **`report_images`**: Stored relative paths, thumbnails, and MIME-verified image metadata.
5. **`report_private_details`**: Isolated secret verification questions, answers, and evidence guidelines.
6. **`match_suggestions`**: Candidate pairs (`lost_report_id`, `found_report_id`), normalized scores (0–100), JSON explanation tags, and user acknowledgment status (`suggested`, `viewed`, `dismissed`, `claimed`).
7. **`claims`**: Ownership claims, verification statuses, reviewer IDs, notes, and handover confirmation flags.
8. **`claim_evidence`**: Secure claimant-submitted proof and documents.
9. **`claim_status_history`**: Audit trail of claim state transitions.
10. **`notifications`**: Targeted user alerts with event types (`match_found`, `claim_submitted`, `claim_approved`, `claim_rejected`, `recovery_confirmed`).
11. **`report_flags`**: Moderation reports for suspicious, scam, or abusive submissions.
12. **`audit_logs`**: System-wide administrative tracking for moderation, account suspensions, and overrides.

---

## 6. Installation & Quick Start

### Prerequisites
- **PHP 8.2 or later** (with `pdo_mysql`, `mbstring`, `fileinfo`, `openssl` extensions enabled)
- **MySQL 8.0+ / MariaDB 10.4+**
- Modern Web Browser (Chrome, Firefox, Safari, Edge)

### Step 1: Clone or Open Project
Navigate to the project root directory:
```bash
cd trace
```

### Step 2: Environment Configuration
Copy `.env.example` to `.env` and verify database settings:
```bash
cp .env.example .env
```
Default `.env` configuration:
```ini
APP_NAME=TRACE
APP_ENV=development
APP_URL=http://localhost:8000
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=trace_db
DB_USER=root
DB_PASS=
```

### Step 3: Initialize Database Schema
Import `database/schema.sql` using MySQL CLI or phpMyAdmin:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS trace_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p trace_db < database/schema.sql
```

### Step 4: Seed Realistic Sample Data
Populate the database with sample organizations, users, correlated lost/found items, claims, and algorithmic matches:
```bash
php database/seed.php
```

### Step 5: Start Local Development Server
Launch the PHP built-in server pointing to the `public/` web root:
```bash
php -S localhost:8000 -t public
```
Or double-click `run-server.bat` on Windows.

Visit **`http://localhost:8000`** in your browser.

---

## 7. Demonstration Accounts & Credentials

The seed script establishes realistic accounts to test all user, finder, claimant, and administrative roles:

| Role | Email | Password | Pre-seeded Scenario |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@trace.internal` | `Password123!` | Access to `/admin` moderation, audit logs, and account controls. |
| **User (Lost Owner)** | `alex.morgan@campus.edu` | `Password123!` | Owner of lost Sony WH-1000XM4 Headphones (`TRC-L-2026-00001`). Has pending match suggestions. |
| **User (Finder)** | `elena.rostova@campus.edu` | `Password123!` | Finder of Sony WH-1000XM4 Headphones (`TRC-F-2026-00002`). Managed secret verification question. |
| **User (Student)** | `marcus.vance@campus.edu` | `Password123!` | Lost Hydro Flask Water Bottle (`TRC-L-2026-00003`). |
| **User (Student)** | `priya.sharma@campus.edu` | `Password123!` | Found Hydro Flask (`TRC-F-2026-00004`). |

---

## 8. Automated Test Suite

TRACE includes automated unit and workflow test suites verifying domain logic without external testing dependencies:

Run all tests via terminal:
```bash
php tests/MatchingServiceTest.php
php tests/AuthTest.php
php tests/ClaimWorkflowTest.php
```

### Test Coverage Highlights:
- **`MatchingServiceTest.php` (14 Tests):**
  - Tokenization, stop-word removal, and case normalization.
  - Jaccard similarity bounds ($0.0 \le J \le 1.0$).
  - Haversine spherical distance calculations.
  - Incompatible category rejection (e.g. Keys vs. Water Bottles).
  - Date proximity decay curve verification.
  - Exclusion of self-matching reports.
- **`AuthTest.php` (14 Tests):**
  - Registration validation and duplicate email prevention.
  - Password hashing with Bcrypt (`password_hash`).
  - Session regeneration and role assignment.
  - Authentication failure for incorrect passwords and suspended accounts.
- **`ClaimWorkflowTest.php` (13 Tests):**
  - Claim submission with private evidence notes.
  - State transitions (`submitted` $\rightarrow$ `under_review` $\rightarrow$ `approved` $\rightarrow$ `completed`).
  - Automated parent report status updates (`active` $\rightarrow$ `under_review` $\rightarrow$ `claimed` $\rightarrow$ `recovered`).
  - Claim audit trail persistence.

---

## 9. Scheduled Report Expiration (Cron CLI)

To automatically expire reports that have passed their retention policy:

```bash
php bin/expire-reports.php
```

### Linux Crontab Configuration:
```cron
# Run daily at 02:00 AM
0 2 * * * /usr/bin/php /var/www/trace/bin/expire-reports.php >> /var/www/trace/storage/logs/cron.log 2>&1
```

### Windows Task Scheduler:
Create a scheduled task executing `C:\xampp\php\php.exe` with arguments `bin\expire-reports.php` within the `trace/` working directory.

---

## 10. Acceptance Criteria Verification Checklist

| Scenario | Requirement | Verification Method | Status |
| :---: | :--- | :--- | :---: |
| **1** | **Lost-Item Reporting** | Register/Login, submit via `/reports/create/lost`. Verify unique code `TRC-L-...` in DB and search gallery. | ✅ Verified |
| **2** | **Found-Item Reporting** | Submit via `/reports/create/found`. Verify private verification questions are masked from public view. | ✅ Verified |
| **3** | **Potential Matching** | Submit correlated reports. Verify automatic match score computation and rationale in `/matches`. | ✅ Verified |
| **4** | **Ownership Claim** | Submit claim on found report. Verify evidence submission and status changes to `submitted` / `under_review`. | ✅ Verified |
| **5** | **Safe Recovery** | Finder reviews and approves claim; confirms handover. Report updates atomically to `recovered`. | ✅ Verified |
| **6** | **Privacy Architecture** | Public visitors cannot view `report_private_details` in HTML, scripts, or network responses. | ✅ Verified |
| **7** | **Report Expiration** | Run `bin/expire-reports.php`. Reports past expiration timestamp transition cleanly without data loss. | ✅ Verified |
| **8** | **Administration Suite** | Log in as `admin@trace.internal`. Access `/admin`, moderate reports, review claims, and inspect audit logs. | ✅ Verified |
| **9** | **Responsive Design** | Tested across Desktop, Tablet, and Mobile viewport sizes without horizontal overflow. | ✅ Verified |
| **10** | **Clean Installation** | Works out of the box with `schema.sql`, `seed.php`, and built-in PHP server. | ✅ Verified |

---

## 11. Design Tokens & Color Palette

- **Dark Obsidian (`#111310`):** Primary dark surface background.
- **Deep Graphite (`#1B1E19`):** Card, panel, and navigation surface.
- **Warm Ivory (`#F4F2EA`):** High-contrast editorial daylight background.
- **Electric Lime (`#D7FF64`):** Purposeful accent for primary actions, match indicators, and verified badges.
- **Muted Sage (`#929B88`):** Subtle secondary labels, borders, and metadata typography.
- **Warning Amber (`#F2B84B`):** Lost-item classification markers and pending alerts.
- **Success Green (`#39865B`):** Approved claims and recovered milestones.

---

## 12. License & Acknowledgments

Built for community and campus spaces. Free to use, adapt, and deploy.
All geographic map tiles powered by **OpenStreetMap** contributors.
