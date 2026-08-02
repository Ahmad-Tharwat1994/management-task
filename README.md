# Task Management API

A RESTful Task Management API built with Laravel 11.

## Features

* User Authentication using Laravel Sanctum
* Project Management
* Task Management
* Dashboard Statistics
* Repository Pattern
* Service Layer
* Resource Classes
* Form Request Validation
* Feature & Unit Tests
* Queue Jobs
* Soft Deletes
* Pagination

---

# Requirements

* PHP 8.2+
* Composer
* MySQL
* Laravel 11
* Git

---

# Installation

Clone the repository

```bash
git clone git@github.com:Ahmad-Tharwat1994/management-task.git
```

Go to the project directory

```bash
cd task-management-api
```

Install dependencies

```bash
composer install
```

Copy environment file

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

---

# Environment Setup

Update your `.env` file.

```env
APP_NAME="Task Management API"

APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

CACHE_STORE=file

SESSION_DRIVER=file
```

---

# Database Setup

Run migrations

```bash
php artisan migrate
```

Seed the database

```bash
php artisan db:seed
```

Or run both together

```bash
php artisan migrate --seed
```

---

# Queue

Create queue table

```bash
php artisan queue:table
```

Run migrations

```bash
php artisan migrate
```

Start queue worker

```bash
php artisan queue:work
```

---

# Run the Application

```bash
php artisan serve
```

---

# Run Tests

```bash
php artisan test
```

Run a specific test

```bash
php artisan test --filter=TaskTest
```

---

# API Features

## Authentication

* Register
* Login
* Logout

## Projects

* Create Project
* List Projects
* View Project
* Update Project
* Delete Project

## Tasks

* Create Task
* Update Task
* Delete Task
* View Task
* List Tasks
* Filter by Status
* Filter by Priority
* Search by Title

## Dashboard

Returns:

* Total Projects
* Active Projects
* Total Tasks
* Completed Tasks
* Pending Tasks
* Overdue Tasks

---

# Architecture

The project follows a layered architecture.

```
Controller
    ↓
Service
    ↓
Repository Interface
    ↓
Repository (Eloquent)
    ↓
Database
```

---

# Testing

The project includes:

* Unit Tests
* Feature Tests

---

# Queue Job

A scheduled queue job checks overdue tasks and sends a database notification to the project owner.

---

# Postman Collection


You can explore and test all API endpoints using the shared Postman collection:

https://hv656030-3873002.postman.co/workspace/Hhh-Vvv's-Workspace~f14e2077-35ea-4afa-8a60-5bb876ce2948/collection/undefined?action=share&creator=57060684

The collection includes requests for:

Authentication
Projects
Tasks
Dashboard

Note: Import the collection into Postman and update the base_url and authentication token according to your local environment.

---



# API Documentation

This project uses Scramble to generate interactive API documentation.

After running the application, you can access the documentation at:

http://task-management-api.test/docs/api

Or, if you're using Laravel's built-in development server:

http://127.0.0.1:8000/docs/api

The documentation is generated automatically from the application's routes, controllers, request validation, and resources, ensuring it always stays up to date with the codebase.

# License

This project is created for technical assessment purposes.