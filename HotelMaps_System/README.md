# ComfortVue Philippines (HotelMaps System)

ComfortVue is an interactive discovery and reservation platform for motels, condo staycations, Airbnbs, and short-term rentals across Metro Manila, featuring dynamic interactive maps, route guidance, verified user bookings, and administrative management.

---

## 🛠️ Tech Stack & Requirements

- **Server / Runtime**: PHP 7.4+ or PHP 8.x
- **Web Server**: Apache / Nginx (or PHP Built-in Server / XAMPP / WampServer / Laragon)
- **Database**: MySQL / MariaDB (Database name: `hotelmaps_system`)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, Leaflet.js

---

## 🚀 How to Run the Application

This is a PHP & MySQL application that must be served via a web server (e.g., XAMPP Apache or PHP's built-in development server).

### Option 1: Running with XAMPP (Recommended)

1. **Place Project in htdocs**:
   Ensure this project folder is located inside your XAMPP web root:
   ```text
   C:\xampp\htdocs\Hotel maps oct 8
   ```

2. **Start Apache & MySQL**:
   - Open the **XAMPP Control Panel**.
   - Click **Start** for **Apache**.
   - Click **Start** for **MySQL**.

3. **Import Database**:
   - Open your browser and navigate to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database named:
     ```sql
     hotelmaps_system
     ```
   - Select the newly created database, go to the **Import** tab, choose the file:
     ```text
     database/schema.sql
     ```
   - Click **Import** (or **Go**) to populate tables and initial records.

4. **Verify Database Configuration**:
   Open [db.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/db.php) and ensure your MySQL credentials match:
   ```php
   $host = '127.0.0.1';
   $db   = 'hotelmaps_system';
   $user = 'root';
   $pass = '';
   ```

5. **Launch in Browser**:
   Open any modern browser and visit:
   - **Landing Page**: [http://localhost/Hotel%20maps%20oct%208/](http://localhost/Hotel%20maps%20oct%208/) (or `index.php`)
   - **Interactive Map Workspace**: [http://localhost/Hotel%20maps%20oct%208/home.php](http://localhost/Hotel%20maps%20oct%208/home.php)
   - **Admin Management Portal**: [http://localhost/Hotel%20maps%20oct%208/admin_login.php](http://localhost/Hotel%20maps%20oct%208/admin_login.php)

---

### Option 2: Running with PHP Built-in Server

If you prefer running via the terminal/command line:

1. **Ensure MySQL is running** and `hotelmaps_system` database is imported with `database/schema.sql`.
2. Open PowerShell or Command Prompt in the project root:
   ```powershell
   cd "c:\xampp\htdocs\Hotel maps oct 8"
   php -S 127.0.0.1:8000
   ```
3. Open your browser and visit:
   - [http://127.0.0.1:8000/](http://127.0.0.1:8000/)

---

## 📁 Key File Structure

- [index.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/index.php) / [app/Views/index.view.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/app/Views/index.view.php) - Landing page with hero section, search highlights, and quick access.
- [home.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/home.php) / [app/Views/home.view.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/app/Views/home.view.php) - Interactive map interface, real-time routing, destination cards, search/filter, and booking engine.
- [api.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/api.php) - RESTful API backend handling destinations, user bookings, authentication, reviews, and admin operations.
- [db.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/db.php) - Centralized PDO database connection configuration.
- [admin_login.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/admin_login.php) / [admin.php](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/admin.php) - Admin login and management dashboard.
- [database/schema.sql](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/database/schema.sql) - Full MySQL schema and initial seed data.

---

## 📌 Project Maintenance Rule

> **Important**: Keep [README.md](file:///c:/xampp/htdocs/Hotel%20maps%20oct%208/README.md) continuously updated whenever project structure, setup instructions, endpoints, or environment configurations change.
