# Tasker Enterprise

Tasker is a full-featured enterprise project management platform built with Laravel. It supports projects, teams, tasks, time tracking, Kanban boards, Gantt charts, calendar, AI assistant, marketplace, messaging, and reporting.

## Technologies

- Laravel 12
- PHP 8.2+
- MySQL
- Laravel Breeze (Authentication)
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Node.js / NPM
- PHPUnit 11
- Playwright (E2E)

Development environment:

```text
PHP 8.2.12
Laravel 12.64.0
Node.js 24.18.0
npm 11.16.0
```

## Project Setup

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Install JavaScript Dependencies

```bash
npm install
```

### 3. Create the Environment File

```powershell
Copy-Item .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Configure Database

The project uses **MySQL**. Update your `.env` with your local MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gdfh
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Build Front-End Assets

For development (keep running):

```bash
npm run dev
```

For production build:

```bash
npm run build
```

### 8. Start Laravel

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

## Running Automated Tests

### PHPUnit (Feature Tests)

```bash
php artisan test
```

Current test suite result:

```text
278 tests, 873 assertions — OK
```

### Playwright (E2E Tests)

```bash
npx playwright test
```

## Key Features

- User authentication with onboarding (account type: client / freelancer)
- Projects: full CRUD, archive, members, teams, comments, attachments, proposals, reviews, activity timeline
- Teams: full CRUD, members, roles, ownership transfer, invitations
- Kanban board (4 columns: Todo / In Progress / Review / Done)
- Gantt chart (Day / Week / Month zoom)
- Calendar (Month / Week / Agenda views)
- Time tracking (Start / Pause / Resume / Stop / Manual entry)
- AI Assistant (Google Gemini 2.5 Flash with rule-based fallback)
- Reports & Analytics with multi-filter support
- Direct messaging between users
- Notifications center
- Marketplace (freelancer profiles, services, proposals, contracts)
- Full RTL / Arabic-first interface
- Light Mode / Dark Mode / System Mode

## Development Notes

- The design system is defined in `resources/css/app.css` using CSS custom properties.
- Dark mode is managed via an Alpine.js store (`Alpine.store('theme')`) with `localStorage` persistence.
- All business logic is separated into Service classes under `app/Services/`.
- Authorization is handled through Laravel Policies under `app/Policies/`.
