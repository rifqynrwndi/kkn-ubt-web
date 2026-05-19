<p align="center">
  <img src="public/images/logo-ubt.png" alt="UBT Logo" height="80">
</p>

<h1 align="center">KKN UBT Web</h1>
<p align="center">
  Sistem Informasi Kuliah Kerja Nyata — Universitas Borneo Tarakan
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=flat&logo=bootstrap" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat&logo=livewire" alt="Livewire">
</p>

---

## About

**KKN UBT Web** is a web-based application that manages the entire lifecycle of Kuliah Kerja Nyata (KKN) at Universitas Borneo Tarakan. From student registration and document verification to competitive real-time group selection.

### Key Features

- **Student Registration & Document Upload** — Register for KKN waves, upload 5 required documents
- **Document Verification** — Admin review & approve/reject student documents with bulk actions
- **WAR System (Warisan Alokasi Real-time)** — Competitive real-time group selection with concurrent locking
- **Admin Dashboard** — Full CRUD for students, groups, villages, faculties, supervisors
- **Role-Based Access** — Superadmin, Mahasiswa, Pembimbing (DPL)
- **Dark Mode** — Auto-detection with `data-bs-theme` attribute
- **CI/CD** — GitHub Actions auto-deploy to production

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.3 |
| Database | MySQL 8.0 |
| Frontend | Bootstrap 5 (Stisla UI), Livewire 3.6 |
| Real-time | AJAX Polling, Database Locks |
| Auth | Laravel Auth, Spatie Permissions |
| Deployment | GitHub Actions, Ubuntu VPS, Nginx, Let's Encrypt |

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 20+
- MySQL 8.0+

### Installation

```bash
git clone https://github.com/rifqynrwndi/kkn-ubt-web.git
cd kkn-ubt-web

cp .env.example .env
# Edit .env with your database credentials

composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed

npm ci
npm run dev
```

Access at `http://localhost:8000` after running `php artisan serve`.

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Superadmin | `admin@kknubt.ac.id` | Set via `SUPERADMIN_PASSWORD` in `.env` |

---

## WAR System

The WAR system handles thousands of concurrent join requests with strict quota enforcement:

| Rule | Limit |
|------|-------|
| Max per group | 12 |
| Max male | 4 |
| Max female | 9 |
| Same study program | 1 |
| Per-faculty quota | 2-3 |

**Concurrency:** Cache-based atomic locks + `lockForUpdate()` row-level pessimistic locking prevent race conditions.

---

## Database Seeding

```bash
php artisan db:seed --force
```

Seeders include:
- **FakultasProdiSeeder** — 7 faculties, 22 study programs
- **DesaSeeder** — 64+ villages across 3 regencies in North Kalimantan
- **KelompokKknSeeder** — 1 KKN group per village
- **SettingSeeder** — Default application settings

### Import Old Data

Import participants from the legacy system:

```bash
# 1. Import old SQL dump
mysql -u root -p kkn_ubt_old < /path/to/old_dump.sql

# 2. Configure old_mysql DB connection in .env
# OLD_DB_DATABASE=kkn_ubt_old

# 3. Run import
php artisan import:old-kkn --old-gelombang=16 --new-gelombang=1
```

---

## Deployment

### GitHub Actions CI/CD

Two workflows are configured:

- **CI** (`.github/workflows/ci.yml`) — runs tests on push/PR
- **Deploy** (`.github/workflows/deploy.yml`) — deploys on push to `main`

### Server Setup

See [`DEPLOY.md`](DEPLOY.md) for complete step-by-step Ubuntu 24.04 VPS deployment guide.

Required GitHub Secrets:

| Secret | Description |
|--------|-------------|
| `SSH_HOST` | Server IP |
| `SSH_USERNAME` | SSH user |
| `SSH_PORT` | SSH port |
| `SSH_PRIVATE_KEY` | SSH private key |
| `DEPLOY_PATH` | App path (e.g., `/var/www/kkn-ubt`) |

---

## Directory Structure

```
├── app/
│   ├── Console/Commands/   # Artisan commands (import, stress test)
│   ├── Http/Controllers/   # 28 controllers
│   ├── Models/             # 20+ models
│   └── Services/War/       # WAR engine (5 services)
├── database/
│   ├── migrations/         # 25+ migrations
│   └── seeders/            # 10 seeders
├── resources/views/        # Blade templates
├── routes/web.php          # All web routes
└── .github/workflows/      # CI/CD pipelines
```

---

## License

Proprietary — Universitas Borneo Tarakan.

---

<p align="center">
  Developed for <strong>LPPM Universitas Borneo Tarakan</strong>
</p>
