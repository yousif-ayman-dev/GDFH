# GDFH Project

GDFH is a web application developed using Laravel as part of an academic software development project.

This repository contains the current development stage of the system.

## Current Development Stage

The current version includes:

- User authentication
- User registration and login
- User profile management
- Project creation
- Project listing and viewing
- Project editing
- Project deletion
- Project ownership authorization
- Project member management
- Adding members to projects
- Updating project member roles and statuses
- Removing project members
- Validation and authorization rules
- Automated feature tests

At the time this stage was completed, the automated test suite passed:

```text
57 tests passed
183 assertions
```

## Technologies

The project is built using:

- Laravel 12
- PHP 8.2+
- SQLite
- Laravel Breeze
- Blade
- Tailwind CSS
- Vite
- Node.js / NPM

Development environment used:

```text
PHP 8.2.12
Laravel 12.64.0
Node.js 24.18.0
```

Exact development versions are not necessarily required as long as the project dependencies are supported.

## Project Setup

### 1. Extract the Project

Extract the ZIP file to any directory.

Example:

```text
C:\Projects\GDFH
```

Open a terminal inside the project directory.

### 2. Install PHP Dependencies

If the `vendor` directory is already included in the provided package, this step may not be necessary.

Otherwise run:

```bash
composer install
```

Composer must be installed on the computer.

### 3. Install JavaScript Dependencies

If the `node_modules` directory is already included in the provided package, this step may not be necessary.

Otherwise run:

```bash
npm install
```

### 4. Create the Environment File

Copy:

```text
.env.example
```

and create:

```text
.env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 5. Generate Application Key

Run:

```bash
php artisan key:generate
```

### 6. Create SQLite Database

Create an empty file named:

```text
database.sqlite
```

inside:

```text
database/
```

On Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File
```

The default `.env.example` is configured to use SQLite:

```env
DB_CONNECTION=sqlite
```

### 7. Run Database Migrations

Run:

```bash
php artisan migrate
```

This will create the required database tables.

### 8. Build Front-End Assets

Run:

```bash
npm run build
```

Alternatively, during development:

```bash
npm run dev
```

Keep the development process running if `npm run dev` is used.

### 9. Start Laravel

Run:

```bash
php artisan serve
```

Laravel should display a local address similar to:

```text
http://127.0.0.1:8000
```

Open that address in a web browser.

## Running Automated Tests

To verify the implemented functionality:

```bash
php artisan test
```

At the completion of this development stage, the expected result was:

```text
Tests: 57 passed (183 assertions)
```

## Important Notes

This package represents a development-stage version of the project and is not the final release.

If `vendor` and `node_modules` are included in the provided ZIP file, the project dependencies are already present. However, running `composer install` and `npm install` may still be appropriate if the project is moved to a different development environment.

The `.env` file is intentionally not included because environment configuration may differ between computers.

SQLite is used for the current development environment, so no MySQL server configuration is required for the default setup.

## Development Status

Completed in this stage:

- Authentication
- Project CRUD
- Project authorization
- Project member backend management
- Validation
- Feature testing

Further interface development and additional system modules are still in progress.
