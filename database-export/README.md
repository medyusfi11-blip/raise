# Database export (portable)

This folder contains ready-to-import MySQL/MariaDB exports of the **Ibrahim Azzouz Cars** database so you can run the project elsewhere.

## Contents

- `ibrahim_azzouz_cars_live_dump.sql` — **recommended** live dump extracted from the running local database (`127.0.0.1:3307`, XAMPP/MySQL) on 2026-11-18. Includes schema + current data (admins: 1, cars: 7, car_features: 14, bookings: 0, settings: 5). Drops/recreates the `ibrahim_azzouz_cars` database on import.
- `ibrahim_azzouz_cars.sql` — schema + seed data from the project source (`sql/database.sql`).
- `001_preserve_booking_history.sql` — migration for existing databases (adds booking snapshot columns and keeps booking history when a car is deleted). The base/live schema already reflects this; running the migration on a fresh import is optional/idempotent on supported MySQL/MariaDB versions.

## Database details (from project config)

- Database name: `ibrahim_azzouz_cars`
- Charset/collation: `utf8mb4` / `utf8mb4_unicode_ci`
- Default app DB config (`config/database.php`): host `localhost`, port `3307`, user `root`, password empty (override with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` env vars).

## Tables

| Table | Purpose |
|---|---|
| `admins` | Admin users (default: `admin` / `admin123`) |
| `cars` | Vehicle inventory and status/pricing |
| `car_features` | Per-car feature rows (FK → `cars`) |
| `bookings` | Customer bookings with car snapshots (FK → `cars`, `ON DELETE SET NULL`) |
| `settings` | Site key/value settings |

## How to run elsewhere

### 1. Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled

### 2. Import the database

**phpMyAdmin:** Import → choose `ibrahim_azzouz_cars_live_dump.sql` (live data) or `ibrahim_azzouz_cars.sql` (seed only) → Go.

**MySQL CLI:**
```bash
mysql -h <host> -P <port> -u <user> -p < ibrahim_azzouz_cars_live_dump.sql
```

The live dump drops/recreates the `ibrahim_azzouz_cars` database and imports schema and current data. The seed script creates the database if missing and populates schema + sample data.

### 3. Configure the app
Copy the project to the new server and set DB credentials in `config/database.php` or via environment variables:

```env
DB_HOST=localhost
DB_NAME=ibrahim_azzouz_cars
DB_USER=your_user
DB_PASS=your_password
DB_PORT=3306
```

Adjust `APP_URL` / environment in `config/app.php` for the new host as needed.

### 4. Access
- Website: `http(s)://<host>/public/` (or per your web root/vhost)
- Admin: `http(s)://<host>/public/admin/` (default login `admin` / `admin123` — change the password after first login)

## Notes
- Uploaded car images live in `public/uploads/cars/`; copy that folder along with the project to keep existing images.
- `ibrahim_azzouz_cars_live_dump.sql` was extracted from the local XAMPP MySQL instance (`c:\xampp\mysql\bin\mysqld.exe`, port 3307). If you need a fresh dump later, run `mysqldump` against the running database with valid credentials.
