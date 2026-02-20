# IMS Pro - Advanced Inventory Management System

A professional, feature-rich Inventory Management System (IMS) built with PHP and MySQL. IMS Pro provides a sleek, modern dashboard to track products, manage suppliers, monitor stock levels, and handle transactions with ease.

## 🚀 Features

- **Dynamic Dashboard**: Real-time stats overview with visual stock charts using Chart.js.
- **Product Management**: Track inventory with categories, cost/selling prices, and low stock thresholds.
- **Supplier Tracking**: Maintain a database of suppliers and their contact information.
- **Stock Operations**:
    - **Purchases (Stock In)**: Record incoming stock from suppliers.
    - **Sales (Stock Out)**: Record outgoing sales and track revenue.
- **Low Stock Alerts**: Automatic identification of products falling below threshold.
- **Data Export**: Export inventory and transaction records to CSV.
- **Premium UI**: Modern dark-themed interface with glassmorphism effects and smooth transitions.

## 🛠️ Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL
- **Frontend**: Bootstrap 5, Vanilla CSS, Bootstrap Icons
- **Visuals**: Chart.js for data visualization

## 📥 Installation

### 1. Database Setup
1. Create a new database named `inventory_system` in your MySQL server (e.g., via phpMyAdmin).
2. Import the [schema.sql] file located in the `database/` folder.

### 2. Configuration
1. Open [includes/db.php]
2. Update the following variables with your local database credentials:
   ```php
   $host = 'localhost';
   $db   = 'inventory_system';
   $user = 'root';
   $pass = ''; // Empty for local server
   ```

### 3. Running Locally
1. Place the project folder in your local server's root directory (e.g., `htdocs` for XAMPP).
2. Access the project via `http://localhost/Advance-inventory-management-System/`.

## 🔐 Default Credentials

- **Email**: `admin@system.com`
- **Password**: `admin123`

## 📁 Project Structure

- `assets/`: CSS, JS, and image assets.
- `database/`: SQL schema files.
- `includes/`: Core logic, database connection, and authentication.
- `layout/`: Reusable UI components like sidebar and header.
- `utils/`: Helper scripts like CSV export.
- `*.php`: Main application pages (Dashboard, Products, Sales, etc.).

---
*Built with ❤️ for efficient inventory management.*