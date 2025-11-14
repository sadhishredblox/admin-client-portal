# Admin/Client Portal

A Laravel and Vue.js application for managing clients with their interests.

## Features

- Admin registration and authentication
- Client CRUD operations (Create, Read, Update, Delete)
- Multiple interests per client
- Admin can only see their own clients
- Unique email validation
- Clients cannot login to the system

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 5.7 or higher

## Installation Steps

### 1. Clone the repository
```bash
git clone https://github.com/sadhishredblox/admin-client-portal.git
cd admin-client-portal
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Install Node dependencies
```bash
npm install --legacy-peer-deps
```

### 4. Create environment file
```bash
cp .env.example .env
```

### 5. Configure database
Edit `.env` file and set your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_client_portal
DB_USERNAME=root
DB_PASSWORD=root
```

### 6. Create database
```bash
mysql -u root -p
CREATE DATABASE admin_client_portal;
EXIT;
```

### 7. Generate application key
```bash
php artisan key:generate
```

### 8. Run migrations and seeders
```bash
php artisan migrate
php artisan db:seed
```

This will create:
- All required database tables
- 2 roles: Admin (id: 1) and Client (id: 2)
- 4 interests: Reading, Cooking, Watching TV, Basketball

### 9. Compile assets
```bash
npm run dev
```

For production:
```bash
npm run prod
```

### 10. Start the development server
```bash
php artisan serve
```

Visit: http://localhost:8000

## Usage

### Admin Registration
1. Go to http://localhost:8000/register
2. Fill in your details (First Name, Last Name, Email, Password)
3. Click Register

### Admin Login
1. Go to http://localhost:8000/login
2. Enter your email and password
3. Click Login

### Managing Clients
After logging in, you'll be redirected to the Client Management page where you can:
- View all your clients in a datatable
- Create new clients with multiple interests
- Edit existing clients
- Delete clients

## Database Structure

### Tables
- `users` - Stores both admins and clients
- `roles` - Stores role types (Admin, Client)
- `interests` - Stores available interests
- `client_interest` - Pivot table for user-interest relationship

## Technologies Used

- Laravel 12.0
- Vue.js 3.2.37
- MySQL
- Bootstrap 5
- Axios for API calls

## Notes

- Clients cannot login to the system (enforced by middleware)
- Each admin can only see clients they created
- Email addresses must be unique across all users
- Passwords are encrypted using Laravel's Hash facade