# Maintenance System - Fleet Management

A comprehensive web-based fleet maintenance management system built with PHP and MySQL. This system helps organizations manage their vehicle fleet, track maintenance records, assign mechanics, and monitor spare parts inventory.

## Features

### Role-Based Access Control
The system supports three user roles with specific functionalities:

#### 1. Admin Dashboard
- View system statistics (vehicles, users, parts, maintenance records)
- Manage vehicles (add, view, delete)
- Manage users (add, view, delete with role assignment)
- Manage spare parts inventory
- Create and track maintenance records
- Assign mechanics to maintenance tasks
- Filter maintenance records by status (pending, in progress, done)

#### 2. Driver Dashboard
- View assigned vehicle details
- Track maintenance history with invoices
- View parts used in each maintenance
- Monitor maintenance costs
- Check maintenance status (pending, in progress, completed)

#### 3. Mechanic Dashboard
- View assigned maintenance tasks
- Update task status (pending → in progress → done)
- Track personal task statistics
- View vehicle and maintenance details

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5.3, HTML5, CSS3, JavaScript
- **Architecture**: MVC-inspired structure with role-based routing

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### Setup Instructions

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd MaintenanceSystem
   ```

2. **Database Setup**
   - Create a new database named `system`
   - Import the SQL file:
     ```bash
     mysql -u root -p system < "system (1).sql"
     ```
   - Or use phpMyAdmin to import `system (1).sql`

3. **Configure Database Connection**
   - Open `connection.php`
   - Update credentials if needed:
     ```php
     $host = "localhost";
     $db = "system";
     $user = "root";
     $pass = "";
     ```

4. **Configure Base URL**
   - Open `partials/navbar.php`
   - Update `$BASE_URL` to match your installation path:
     ```php
     $BASE_URL = "/MainenanceSystem";
     ```

5. **Set Permissions** (Linux/Mac)
   ```bash
   chmod -R 755 .
   ```

6. **Access the Application**
   - Navigate to: `http://localhost/MainenanceSystem`

## Default Login Credentials

| Role     | Email              | Password |
|----------|-------------------|----------|
| Admin    | admin@test.com    | 123456   |
| Driver   | driver1@test.com  | 123456   |
| Mechanic | mech1@test.com    | 123456   |

**Note**: Change default passwords after first login for security.

## Database Schema

### Main Tables
- **user**: Stores user accounts with roles (admin, driver, mechanic)
- **vehicle**: Vehicle information and plate numbers
- **maintenance_record**: Maintenance history with status tracking
- **spare_part**: Inventory of spare parts with pricing
- **service_center**: Maintenance service center locations
- **works_on**: Links mechanics to maintenance tasks
- **maintenance_parts**: Tracks parts used in each maintenance

## Project Structure

```
MainenanceSystem/
├── AdminDashboard/
│   ├── dashboard.php          # Admin main dashboard
│   ├── vehicles.php           # Vehicle management
│   ├── users.php              # User management
│   ├── spare_parts.php        # Parts inventory
│   ├── maintinance.php        # Maintenance records
│   └── maintenance_add.php    # Create maintenance record
├── driver/
│   ├── my_vehicle.php         # Vehicle details & history
│   └── maintenance_status.php # Status tracking
├── mechanic/
│   ├── tasks.php              # Assigned tasks
│   └── update_status.php      # Status update handler
├── partials/
│   ├── header.php             # HTML header
│   ├── navbar.php             # Navigation bar
│   └── footer.php             # HTML footer
├── auth_admin.php             # Admin authentication
├── auth_driver.php            # Driver authentication
├── auth_mechanic.php          # Mechanic authentication
├── connection.php             # Database connection
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── index.php                  # Home page
└── system (1).sql             # Database schema
```

## Key Features Detail

### Maintenance Record Management
- Create comprehensive maintenance records
- Add multiple spare parts with quantities
- Calculate automatic cost totals
- Track vehicle mileage at maintenance
- Assign specific mechanics
- Link to service centers

### Vehicle Assignment
- One vehicle per driver
- Prevents duplicate assignments
- Validates vehicle existence
- Tracks assignment history

### Invoice System
- Detailed parts breakdown
- Automatic cost calculation
- Historical invoice viewing
- Parts quantity and pricing

### Security Features
- Session-based authentication
- Role-based access control
- Prepared SQL statements (SQL injection prevention)
- Password hashing support
- Email uniqueness validation

## Common Issues & Solutions

### Issue: Page not found (404)
**Solution**: Verify `$BASE_URL` in `partials/navbar.php` matches your installation path.

### Issue: Database connection failed
**Solution**: Check credentials in `connection.php` and ensure MySQL service is running.

### Issue: Access denied after login
**Solution**: Clear browser cookies/cache and verify user role in database.

### Issue: Cannot add vehicle - duplicate error
**Solution**: Plate numbers must be unique. Check existing vehicles first.
