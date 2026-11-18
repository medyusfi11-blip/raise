# Ibrahim Azzouz Cars — Car Rental Website

Full-stack car rental web application for Ibrahim Azzouz Cars, Tetouan, Morocco.

## Tech Stack

- **Frontend:** React 18 (CDN), Tailwind CSS, Font Awesome, Cairo font
- **Backend:** PHP 8.x, PDO (MySQL)
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Admin Dashboard:** PHP + Tailwind CSS, AJAX (fetch API)

---

## Local Setup (XAMPP / WAMP / MAMP)

### 1. Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled

### 2. Install
```
1. Copy the `ibrahim-azzouz-cars` folder into your web server root:
   - XAMPP: C:/xampp/htdocs/
   - WAMP:  C:/wamp64/www/
   - MAMP:  /Applications/MAMP/htdocs/

2. Start Apache and MySQL from your control panel.
```

### 3. Create Database
```
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Select sql/database.sql
4. Click "Go" to import
```

Or via command line:
```bash
mysql -u root -p < sql/database.sql
```

### 4. Configure (optional)
Edit `config/database.php` if your MySQL credentials differ:
```php
$this->username = getenv('DB_USER') ?: 'root';   // default
$this->password = getenv('DB_PASS') ?: '';         // default
```

Or set environment variables:
```
DB_HOST=localhost
DB_NAME=ibrahim_azzouz_cars
DB_USER=root
DB_PASS=
DB_PORT=3306
```

### 5. Access
```
Website:    http://localhost/ibrahim-azzouz-cars/public/
Admin:      http://localhost/ibrahim-azzouz-cars/public/admin/
```

### 6. Admin Login
```
Username: admin
Password: admin123
```

---

## Project Structure

```
ibrahim-azzouz-cars/
├── .htaccess                    # Root rewrite → public/
├── config/
│   ├── app.php                  # App constants, paths, env detection
│   └── database.php             # PDO singleton (Database class)
├── includes/
│   ├── auth.php                 # Session auth, CSRF, login/logout
│   └── helpers.php              # sanitize, upload, flash, formatPrice...
├── public/
│   ├── .htaccess                # URL rewriting, security rules
│   ├── index.php                # Main website (React SPA + Booking system)
│   ├── admin/
│   │   ├── login.php            # Admin login page
│   │   ├── index.php            # Dashboard (stats, recent bookings)
│   │   ├── cars.php             # Car management (CRUD + image upload)
│   │   ├── bookings.php         # Booking management (accept/reject/complete)
│   │   └── settings.php         # Site settings, password, profile
│   ├── api/
│   │   ├── auth.php             # POST login, logout, GET check
│   │   ├── cars.php             # CRUD cars + features
│   │   ├── bookings.php         # CRUD bookings + stats
│   │   ├── content.php          # Settings, dashboard stats
│   │   ├── settings.php         # Settings, password, profile
│   │   └── upload.php           # Image upload endpoint
│   └── uploads/
│       └── cars/                # Uploaded car images
├── sql/
│   └── database.sql             # Full schema + seed data
└── README.md
```

---

## Database Tables

| Table | Description |
|-------|-------------|
| `admins` | Admin users (username, hashed password, role) |
| `cars` | Vehicle inventory (name, model, type, price, status, features JSON) |
| `car_features` | Individual car features (linked to cars via FK) |
| `bookings` | Customer booking requests (customer info, dates, price, status) |
| `settings` | Site configuration key-value pairs |

---

## Features

### Frontend
- Responsive car listing with real-time data from database
- Booking modal with auto price calculation
- Booking confirmation with reference number
- Contact section with WhatsApp integration
- Mobile-friendly design

### Admin Dashboard
- Login/logout with session auth
- Dashboard with live stats (total cars, bookings, revenue)
- **Car Management:** Add, edit, delete cars; upload images; manage features; change status/price
- **Booking Management:** View all bookings; accept, reject, or complete; search and filter
- **Settings:** Site info, admin password, profile update
- Toast notifications for all actions
- Pagination for large lists

---

## Deployment to Production

### Recommended Hosting
- **Shared:** Hostinger, SiteGround, Bluehost (PHP + MySQL included)
- **VPS:** DigitalOcean, Linode, Hetzner (full control)
- **Cloud:** AWS Lightsail, Google Cloud, Railway

### Steps
1. Upload all files via FTP/SFTP or Git
2. Create MySQL database on hosting (via cPanel or SSH)
3. Import `sql/database.sql`
4. Edit `config/app.php` — change `APP_URL` to your domain
5. Set `APP_ENV` to `production` (auto-detected, or force in config)
6. Ensure Apache `mod_rewrite` is enabled
7. Set folder permissions: `public/uploads/cars/` writable (755)
8. Force HTTPS in `.htaccess` (uncomment the HTTPS redirect line)
9. Test admin login at `https://yourdomain.com/admin/`

### Environment Variables (production)
```
DB_HOST=localhost
DB_NAME=ibrahim_azzouz_cars
DB_USER=your_db_user
DB_PASS=your_db_password
DB_PORT=3306
```
