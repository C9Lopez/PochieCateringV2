# Kusina ni Maria - Filipino Catering System
## XAMPP Setup Guide

### Requirements
- XAMPP (with Apache and MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

---

## Installation Steps

### Step 1: Copy Files to XAMPP
1. Copy the entire `public` folder contents to `C:\xampp\htdocs\catering\` (or your preferred folder name)
2. The folder structure should look like:
   ```
   C:\xampp\htdocs\catering\
   ├── admin/
   ├── config/
   ├── database/
   ├── includes/
   ├── staff/
   ├── uploads/
   ├── index.php
   ├── login.php
   ├── register.php
   └── ... (other files)
   ```

### Step 2: Create the Database
1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Click "Import" tab
4. Select the file: `database/filipino_catering.sql`
5. Click "Go" to import

**OR** manually:
1. Create a new database named `filipino_catering`
2. Run the SQL script in `database/filipino_catering.sql`

### Step 3: Configure Database Connection
Edit `config/database.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password (blank by default in XAMPP)
define('DB_NAME', 'filipino_catering');
```

### Step 4: Set File Permissions
Create these folders if they don't exist and ensure they're writable:
- `uploads/`
- `uploads/chat/`
- `uploads/payments/`

---

## Access the Website

### Customer Side
- **Homepage**: http://localhost/catering/index.php
- **Menu**: http://localhost/catering/menu.php
- **Packages**: http://localhost/catering/packages.php
- **Login**: http://localhost/catering/login.php
- **Register**: http://localhost/catering/register.php

### Admin Dashboard
- **URL**: http://localhost/catering/admin/dashboard.php
- **Default Super Admin Login**:
  - Email: `admin@filipinocatering.com`
  - Password: `admin123`

### Staff Dashboard
- **URL**: http://localhost/catering/staff/dashboard.php
- Create staff accounts from Admin > Users

---

## Features

### Customer Features
- Browse menu items by category
- View catering packages with pricing
- Create booking with custom menu selection
- Real-time chat with admin/staff (with image upload)
- Track booking status
- Submit payment with proof upload
- Profile management

### Admin Features
- Dashboard with statistics
- Manage all bookings
- Update booking status (New → Pending → Negotiating → Approved → Paid → Completed)
- Chat with customers
- Manage menu items and categories
- Manage catering packages
- Verify payments
- Assign staff to bookings

### Super Admin Features (all admin features plus):
- User management (create admin/staff accounts)
- Site settings
- Reports & analytics
- Activity logs

### Staff Features
- View assigned bookings
- Chat with customers
- View booking details

---

## Booking Status Flow

1. **New** - Customer submitted booking
2. **Pending** - Admin reviewing the booking
3. **Negotiating** - Discussing details via chat
4. **Approved** - Booking confirmed, awaiting payment
5. **Paid** - Payment verified
6. **Preparing** - Event preparation in progress
7. **Completed** - Event finished

---

## Payment Process

1. Admin approves booking
2. Customer sees "Submit Payment" button
3. Customer uploads payment proof (GCash, Bank Transfer, etc.)
4. Admin verifies payment in Payments section
5. Booking status auto-updates to "Paid"

---

## Deployment to Live Hosting

1. Upload all files via FTP to your web hosting
2. Create MySQL database on hosting
3. Import the SQL file
4. Update `config/database.php` with hosting database credentials
5. Ensure `uploads` folder has write permissions (chmod 755 or 777)

---

## Default Data Included

- 8 Menu Categories (Appetizers, Soups, Meat, Seafood, Vegetables, etc.)
- 30+ Filipino dishes with prices
- 5 Catering packages
- 1 Super Admin account

---

## Troubleshooting

**Database Connection Error**
- Check if MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Ensure database `filipino_catering` exists

**Images Not Uploading**
- Check folder permissions for `uploads/`
- Increase `upload_max_filesize` in php.ini if needed

**Session Issues**
- Clear browser cookies
- Restart Apache in XAMPP

---

## Support

For your thesis project, feel free to modify:
- Site name in Settings (Admin > Settings)
- Menu items and prices
- Package details
- Payment methods in `submit-payment.php`

Good luck with your thesis! 🎓
