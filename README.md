# UniVault – CSCA 512 Project

A PHP/MySQL academic resource sharing portal for a university community.

## Requirements
- XAMPP (Apache + MySQL), PHP 8.0+
- MySQL 5.7+ or MariaDB
- Browser with PDF support
- Internet connection for jQuery CDN and Google Fonts (the core app works without Google Fonts)

## Installation
1. Extract the `univault` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin/`.
4. Import `database.sql`. It creates `univault_db` and sample academic structure.
5. Check `config/config.php`: default XAMPP MySQL is `root` with blank password.
6. Visit `http://localhost/univault/setup_demo.php` once to create demo accounts.
7. Delete `setup_demo.php` immediately after setup.
8. Open `http://localhost/univault/`.

## Demo accounts
- Admin: admin@univault.local / password
- Faculty: faculty@univault.local / password
- Student: student@univault.local / password

Change demo passwords before any public deployment.

## Main workflows
- Register as student or faculty.
- Sign in and use **Contribute** to upload PDF/DOC/DOCX/PPT/PPTX (max 20 MB).
- Every upload starts as `pending`.
- Admin opens Admin from navigation and approves/rejects submissions.
- Approved resources appear in Explore.
- Open a PDF resource for inline preview; use Download to save it.
- Signed-in users can bookmark resources.
- Admin can open Reports for upload/download statistics.

## Included academic seed data
- Pondicherry University-style department directory (sample list, editable).
- Sample courses and semester structure.
- MCA sample subject CSCA 512 with five modules and syllabus topics.

This is an academic prototype. Verify department/course names and official syllabus details with current university sources before real use.

## CSCA 512 mapping
- Module I: HTTP client/server web application behaviour.
- Module II: HTML forms, tables and CSS layout.
- Module III: JavaScript validation and DOM events.
- Module IV: JSON API endpoints, jQuery and AJAX.
- Module V: PHP authentication, uploads, SQL queries and MySQL connectivity.

## Security and limitations
- Passwords use PHP `password_hash` / `password_verify`.
- Database operations use PDO prepared statements.
- POST forms use CSRF tokens.
- Uploads are restricted by extension, MIME type and size; filenames are randomized.
- For production, configure HTTPS, secure cookies, stricter upload storage outside the public web root, rate limiting, audit/retention policies, and regular backups.
- The current subject/course list is starter data. Admin catalog editing UI is not included in this initial academic build; catalog tables can be managed in phpMyAdmin or extended.
