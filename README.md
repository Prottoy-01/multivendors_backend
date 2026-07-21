# 🛒 MultiVendor E-Commerce Backend

A full-featured **Multi-Vendor E-Commerce ** built with **Laravel**, **PHP**, and **Laragon**. The platform enables multiple vendors to sell products through a single marketplace while customers can browse products, apply coupons, and place orders. Administrators have full control over vendors, products, categories, orders, and promotional campaigns.

> Developed as a university project to demonstrate practical software development skills by building a complete multi-vendor e-commerce platform using the Laravel framework.

---

## ✨ Features

### 👤 Customer

- User Registration & Login
- Browse Products
- Search Products
- View Product Details
- Shopping Cart
- Apply Coupon Codes
- Checkout Process
- Order History
- Profile Management

---

### 🏪 Vendor

- Vendor Registration & Login
- Vendor Dashboard
- Product Management (CRUD)
- Inventory Management
- Upload Product Images
- Apply Product Discounts
- Manage Customer Orders
- Sales Overview

---

### 🛠️ Admin

- Secure Admin Dashboard
- Manage Vendors
- Manage Customers
- Manage Products
- Category Management
- Brand Management
- Coupon Management (Create, Update & Delete)
- Order Monitoring
- User Management
- Marketplace Administration

---

# 🛠 Tech Stack

| Technology | Description |
|------------|-------------|
| Laravel | PHP Framework |
| MySQL | Database |
| Blade | Template Engine |
| HTML5 | Markup |
| CSS3 | Styling |
| Bootstrap | Responsive UI |
| JavaScript | Client-side Functionality |
| Composer | Dependency Management |
| Laragon | Local Development Environment |

---

# 🏗 Project Structure

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
vendor/
```

---

# 🗄 Database Modules

- Users
- Vendors
- Customers
- Products
- Categories
- Brands
- Shopping Cart
- Orders
- Coupons
- Discounts
- Authentication

---

# 🔐 Authentication

- User Authentication
- Vendor Authentication
- Admin Authentication
- Role-Based Authorization
- Middleware Protection
- Password Encryption

---

# 🚀 Installation

## Clone Repository

```bash
git clone https://github.com/Prottoy-01/multivendors_backend.git
```

## Navigate to Project

```bash
cd multivendors_backend
```

## Install Dependencies

```bash
composer install
```

## Create Environment File

```bash
cp .env.example .env
```

## Generate Application Key

```bash
php artisan key:generate
```

## Configure Database

Update your `.env` file.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

## Run Migrations

```bash
php artisan migrate
```

If seeders are available:

```bash
php artisan db:seed
```

## Start Server

```bash
php artisan serve
```

Open:

```
http://127.0.0.1:8000
```

---

# 📸 Project Screenshots

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 000722.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 000756.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 000826.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 000852.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 001416.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 001513.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 001607.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 001643.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 001729.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 002310.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 002431.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 002655.png" width="90%">
</p>

<p align="center">
<img src="screenshots/Screenshot 2026-07-22 002723.png" width="90%">
</p>

---

# 📚 Learning Outcomes

Through this project, I gained practical experience in:

- Laravel MVC Architecture
- Laravel Development
- Eloquent ORM
- Authentication & Authorization
- Role-Based Access Control (RBAC)
- CRUD Operations
- Middleware
- Session Management
- Coupon Management
- Discount Management
- Inventory Management
- File Upload Handling
- RESTful Routing
- Backend Development Best Practices

---

# 🔮 Future Improvements

- REST API for Mobile Apps
- Email Notifications
- Multi-language Support
- Live Order Tracking

---

# 👨‍💻 Author

**Tanvirul Haque Prottoy**



---

# 📄 License

This project is provided solely for educational and learning purposes. Any unauthorized commercial use, reproduction, distribution, or modification of this project without the prior permission of the author is strictly prohibited.