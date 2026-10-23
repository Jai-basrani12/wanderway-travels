# WanderWay Travels

A modern, responsive, and lightweight travel agency web application built with **Core PHP**, **MySQL (via mysqli)**, and **Bootstrap 5**. Designed to run smoothly both on local development environments (XAMPP) and shared hosting (such as InfinityFree or cPanel) with zero code modifications.

---

## 🌟 Key Features

### 🌴 Public Portal
- **Homepage (`index.php`)**: Catchy hero banner, 3 latest active vacation packages, "Why Choose Us" highlights, and direct call-to-actions.
- **Packages Listing & Search (`packages.php`)**: Browse all active holiday packages with a real-time GET search box filtering by title and destination. Includes empty state handling.
- **Package Details (`package.php?id=`)**: Full package itinerary overview, high-resolution imagery, pricing, duration, and an "Enquire Now" pre-fill button. Includes a custom 404 handler for invalid/inactive IDs.
- **Custom Enquiry Form (`enquiry.php`)**: Validated booking enquiry form with 10-digit Indian phone verification, optional email/travel dates/persons, honeypot anti-spam protection, CSRF security, and Post/Redirect/Get flow.
- **Contact Us (`contact.php`)**: Centralized business contact information, interactive Google Maps placeholder, and direct WhatsApp messaging button.
- **Custom 404 Error Page (`404.php`)**: Branded not-found page with quick recovery navigation.
- **Floating WhatsApp Widget**: Mobile-responsive quick chat button on every public page.

### 🛡️ Admin Management Portal (`/admin/`)
- **Brute-Force Protected Authentication (`admin/login.php`)**: Rate limiting that restricts failed login attempts (maximum 5 attempts per 15 minutes) with session regeneration upon login.
- **Admin Dashboard (`admin/dashboard.php`)**: Live operational metrics showing total packages and new customer enquiries with direct filter shortcuts.
- **Tour Package CRUD (`admin/packages.php`)**:
  - Add, edit, and toggle active/inactive status.
  - Image upload handler (`includes/upload.php`) supporting JPG/JPEG/PNG with size (max 2MB) and MIME-type validation.
  - Safe delete protection: blocks deletion if customer enquiries are linked to the package.
- **Customer Enquiry Management (`admin/enquiries.php`)**:
  - Filter by status (`all`, `new`, `contacted`, `closed`).
  - Clickable `tel:` phone links for rapid customer follow-ups.
  - Full message view via Bootstrap modal.
  - Inline status update with CSRF tokens.
  - Pagination configured at 20 enquiries per page.

---

## 📁 Folder Structure

```text
wanderway/
├── admin/                    # Admin portal pages
│   ├── add_package.php       # Create new tour package
│   ├── create_admin.php      # One-time initial admin setup script
│   ├── dashboard.php         # Admin metrics dashboard
│   ├── edit_package.php      # Update existing package
│   ├── enquiries.php         # Customer enquiry management & pagination
│   ├── login.php             # Protected login with rate limiting
│   ├── logout.php            # Secure session destruction
│   └── packages.php          # Package list, status toggle & safe delete
├── assets/
│   ├── css/
│   │   └── style.css         # Travel theme styles with teal accent (#0d9488)
│   └── img/
│       ├── favicon.svg       # Branded compass favicon
│       └── placeholder.svg   # Scalable default package fallback image
├── config/
│   ├── config.php            # Environment detection, mysqli, session, CSRF, contacts
│   ├── live.example.php      # Live production database credentials template
│   └── live.php              # Live credentials (ignored in git)
├── includes/
│   ├── admin_check.php       # Middleware protecting admin pages
│   ├── footer.php            # Common footer and WhatsApp floating button
│   ├── header.php            # Bootstrap 5 navigation & SEO meta tags
│   └── upload.php            # Secure image upload handler
├── uploads/                  # Uploaded package images
│   ├── .gitkeep              # Git directory preservation
│   └── .htaccess             # Hardened security: blocks script/PHP execution
├── 404.php                   # Branded custom 404 error page
├── contact.php               # Contact information & Google Maps embed
├── database.sql              # Clean MySQL table definitions
├── enquiry.php               # Public booking & custom enquiry form
├── index.php                 # Homepage landing & featured tours
├── package.php               # Single tour package view
├── packages.php              # Full tour catalog & search
├── robots.txt                # Search engine crawler directives
├── .gitignore                # Excludes live credentials & uploads
└── .htaccess                 # Disables directory browsing & sets 404 handler
```

---

## 💻 Local Setup (XAMPP on Windows)

1. **Place Project in `htdocs`**:
   Copy or clone the `wanderway` folder to:
   ```text
   C:\xampp\htdocs\wanderway\
   ```

2. **Start Services**:
   Open **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

3. **Import Database**:
   - Open your browser and navigate to: `http://localhost/phpmyadmin/`
   - Click on **New** and create a database named: `wanderway_db` (Collation: `utf8mb4_unicode_ci`).
   - Select `wanderway_db`, go to the **Import** tab, choose `database.sql` from the project root, and click **Import**.

4. 4. **Create Administrator Account**:
   - Generate a password hash in a terminal (XAMPP's PHP):
     `C:\xampp\php\php.exe -r "echo password_hash('YOUR_STRONG_PASSWORD', PASSWORD_DEFAULT);"`
   - In phpMyAdmin, run:
     `INSERT INTO admins (username, password) VALUES ('admin', 'PASTE_HASH_HERE');`
5. **Launch Application**:
   - **Public Website**: [http://localhost/wanderway/](http://localhost/wanderway/)
   - **Admin Login**: [http://localhost/wanderway/admin/login.php](http://localhost/wanderway/admin/login.php)

---

## 🚀 Shared Hosting Deployment (e.g. InfinityFree / cPanel)

1. **Upload Files**:
   Upload all files and folders to your hosting root directory (usually `htdocs/` or `public_html/`).

2. **Setup MySQL Database**:
   - In your hosting control panel, create a new MySQL database and user.
   - Open **phpMyAdmin** on your host, select the new database, and import `database.sql`.

3. **Configure Live Database**:
   - Copy `config/live.example.php` to `config/live.php`.
   - Update `config/live.php` with your live hosting database details:
     ```php
     <?php
     return [
         'host' => 'sqlxxx.yourhosting.com',
         'user' => 'epiz_xxxxxxx',
         'pass' => 'your_actual_password',
         'name' => 'epiz_xxxxxxx_wanderway_db'
     ];
     ```
   - *Note: `config/config.php` automatically detects when running on a live server, turns off error display, sets `BASE_URL` to `'/'`, and loads credentials from `live.php` without modifying any core files.*

4. **Verify Permissions**:
   Ensure `uploads/` folder has write permissions (`755` or `775`).

---

## 🔒 Security Best Practices Implemented

- **Prepared Statements**: 100% of queries that accept input use parameterized `mysqli_stmt` calls.
- **XSS Prevention**: All dynamic values rendered in HTML are escaped using `htmlspecialchars()`.
- **CSRF Protection**: Cryptographically secure session tokens validated on all sensitive POST forms.
- **Anti-Spam Honeypot**: Hidden fields to discard automated form spam.
- **Upload Hardening**: Restricts uploads to JPG/JPEG/PNG, validates file contents via `getimagesize()`, randomizes filenames, and disables PHP execution inside `uploads/` via `.htaccess`.
- **Brute-Force Rate Limiting**: Enforces a 15-minute lock after 5 consecutive failed login attempts.
