# LavaLust Framework

> A lightweight, fast PHP framework built for developers who want clean MVC architecture without unnecessary complexity or performance overhead.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF)](https://www.php.net/)
[![GitHub Stars](https://img.shields.io/github/stars/ronmarasigan/lavalust?style=flat)](https://github.com/ronmarasigan/lavalust/stargazers)

---

## Overview

**LavaLust** is an open-source PHP framework that follows the **MVC (Model–View–Controller)** architectural pattern. It is designed for developers who need a structured, maintainable, and scalable foundation — without the bloat of heavier modern frameworks.

Whether you are building a simple web application, a REST API, or a teaching project, LavaLust provides the right tools with minimal friction.

---

## Features

| Feature | Description |
|---|---|
| **MVC Architecture** | Clean separation of Models, Views, and Controllers for organized, maintainable code |
| **Built-in Routing** | Flexible URL routing that maps requests to controllers with minimal configuration |
| **Libraries & Helpers** | Reusable components for sessions, forms, validation, and database access |
| **Modular Design** | Scalable structure that supports clean organization as your application grows |
| **REST API Support** | First-class support for building RESTful APIs using LavaLust conventions |
| **ORM-like Models** | Simplified, readable database interaction without a heavy abstraction layer |

---

## Requirements

- PHP 7.4 or higher
- A web server with URL rewriting support (Apache `.htaccess` or Nginx config)
- Composer (optional, for dependency management)

---

## Installation

**Clone the repository:**

```bash
git clone https://github.com/ronmarasigan/lavalust.git
cd lavalust
```

**Or download a release directly:**

```bash
wget https://github.com/ronmarasigan/lavalust/archive/refs/heads/main.zip
unzip main.zip
```

Configure your web server to point to the project root and ensure `mod_rewrite` (Apache) or equivalent is enabled.

---

## Quick Start

### 1. Define a Route

**File:** `app/config/routes.php`

```php
$router->get('/', 'Welcome::index');
$router->get('/about', 'Welcome::about');
$router->post('/users/store', 'Users::store');
```

### 2. Create a Controller

**File:** `app/controllers/Welcome.php`

```php
<?php

class Welcome extends Controller
{
    public function index()
    {
        $data['title'] = 'Home';
        $this->call->view('welcome', $data);
    }

    public function about()
    {
        $this->call->view('about');
    }
}
```

### 3. Create a View

**File:** `app/views/welcome.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>
<body>
    <h1>Welcome to LavaLust Framework</h1>
    <p>Lightweight. Fast. MVC.</p>
</body>
</html>
```

### 4. Create a Model

**File:** `app/models/User_model.php`

```php
<?php

class User_model extends Model
{
    protected $table = 'users';

    public function getAll()
    {
        return $this->db->table($this->table)->get()->getResult();
    }

    public function findById(int $id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->get()
    }
}
```

---

## Project Structure

```
lavalust/
├── app/
│   ├── config/          # Application configuration (database, routes, etc.)
│   ├── controllers/     # Controller classes
│   ├── models/          # Model classes
│   ├── views/           # View templates
│   └── libraries/       # Custom libraries and helpers
├── scheme/              # Core framework files (do not modify)
├── public/              # Publicly accessible entry point
│   └── index.php
└── runtime/            # Cache, logs, and uploads (must be writable)
```

---

## Configuration

### Database

**File:** `app/config/database.php`

```php
$database['main'] = array(
    'driver'	=> getenv('DB_DRIVER') ?: '',
    'hostname'	=> getenv('DB_HOST') ?: '',
    'port'		=> getenv('DB_PORT') ?: '',
    'username'	=> getenv('DB_USER') ?: '',
    'password'	=> getenv('DB_PASSWORD') ?: '',
    'database'	=> getenv('DB_NAME') ?: '',
    'charset'	=> getenv('DB_CHARSET') ?: '',
    'dbprefix'	=> getenv('DB_PREFIX') ?: '',
    // Optional for SQLite
    'path'      => ''
);
```

### Base URL

**File:** `app/config/config.php`

```php
$config['base_url'] = 'http://localhost:3000/';
```

---

## Building a REST API

LavaLust supports REST API development out of the box. Controllers can return JSON responses for API endpoints.

```php
<?php

class Api extends Controller
{
    $this->call->library('api');

    public function users()
    {
        $this->api->require_method('GET');
        $auth = $this->api->require_jwt(); 

        $this->call->model('User_model');
        $users = $this->User_model->getAll();

        $this->api->respond(['data' => $users]);
    }
}
```

Route definition:

```php
$router->get('/api/users', 'Api::users');
```

---

## Philosophy

LavaLust is built on a single principle: **minimal core, maximum control.**

Modern frameworks often add layers of abstraction that benefit large enterprise teams but get in the way of developers who want to understand exactly what their code is doing. LavaLust provides structure and utilities without hiding the underlying logic — making it an excellent choice for:

- **Rapid prototyping** — Get an application running in minutes
- **Learning MVC** — Understand how each architectural layer works
- **Lightweight production apps** — Deploy without dragging in unused dependencies
- **Teaching PHP development** — Clear conventions, readable source code

---

## Documentation

Full documentation is available at **[https://lavalust.netlify.app](https://lavalust.netlify.app)**

Topics covered include:

- Installation and server configuration
- Routing: static, dynamic, and grouped routes
- Controllers and request handling
- Models and query builder
- Views, layouts, and partials
- Built-in libraries (sessions, form validation, file upload)
- Helper functions
- REST API development
- Security best practices

---

## Contributing

Contributions are welcome. To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "Add your feature description"`
4. Push to your branch: `git push origin feature/your-feature-name`
5. Open a pull request against `main`

Please ensure your code follows the existing style conventions and includes relevant documentation or comments where appropriate.

---

## Roadmap

- [ ] CLI tool for generating controllers, models, and migrations
- [ ] Middleware support
- [ ] Improved query builder with relationship support
- [ ] Enhanced error handling and debugging tools

---

## License

LavaLust Framework is open-source software licensed under the **[MIT License](https://opensource.org/licenses/MIT)**.

---

## Links

- **GitHub Repository:** [https://github.com/ronmarasigan/lavalust](https://github.com/ronmarasigan/lavalust)
- **Documentation:** [https://lavalust.netlify.app](https://lavalust.netlify.app)
- **Report an Issue:** [https://github.com/ronmarasigan/lavalust/issues](https://github.com/ronmarasigan/lavalust/issues)

---

# Laboratory Exercise No. 6 – Product Management API

CRUD with authentication using the **LavaLust Api library** (JWT access token + rotating refresh token), MySQL (Aiven), and a React frontend (`frontend/`).

## 1. Setup

```bash
cp .env.example .env        # then fill in the DB_* values
php lava jwt:generate       # writes JWT_SECRET and REFRESH_TOKEN_KEY into .env
php lava migration run      # creates migrations, users, refresh_tokens, products
php lava serve --port=3000
```

For **Aiven**, use the host/port/user/password/database from the Aiven console and set `DB_SSL=true`
(optionally `DB_SSL_CA=/path/to/ca.pem`).

## 2. Migration commands (Lab: Database Migration)

| Command | What it does |
|---|---|
| `php lava migration run` | Run pending migrations |
| `php lava migration status` | Show applied / pending migrations |
| `php lava migration create-migration create_x_table` | Create a new migration file |
| `php lava migration rollback` | Roll back the latest migration |
| `php lava migration rollback-all` | Roll back everything (**dev only**) |
| `php lava migration refresh` | Roll back all, then re-run (**dev only**) |

Browser routes with the same effect: `/migrate`, `/status`, `/rollback`, `/rollback-all`, `/refresh`, `/create-migration/{name}`.
They are public, so set `MIGRATION_ENABLED=false` on the deployed server after the tables exist.

Flow: `CLI → MigrationController → route → Migration library → database`

## 3. Endpoints

All `/api/products` endpoints need `Authorization: Bearer <access_token>`.

| Method | URL | Body | Description |
|---|---|---|---|
| POST | `/api/auth/register` | `username, email, password` | Create an account |
| POST | `/api/auth/login` | `username` (or email), `password` | Returns `access_token`, `refresh_token`, `user` |
| POST | `/api/auth/refresh` | `refresh_token` | Rotates the token pair |
| POST | `/api/auth/logout` | `refresh_token` | Revokes the refresh token |
| GET | `/api/auth/me` | – | Current user |
| GET | `/api/products` | – | List products |
| GET | `/api/products/{id}` | – | One product |
| POST | `/api/products` | `product_name, description, price, quantity` | Add |
| PUT / PATCH | `/api/products/{id}` | PUT: all fields, PATCH: any fields | Update |
| DELETE | `/api/products/{id}` | – | Delete |

Errors are JSON: `{"error": "...", "status": 401}`; validation errors return `422` with an `errors` object.

## 4. Deploying on Render (Docker)

1. Push this repo (without `frontend/` if you submit it separately) to GitHub. `.env` is git-ignored.
2. Create a **Web Service → Docker** from the repo. Add `PORT=80` if Render does not detect the port.
3. Add the variables from `.env.example` (`DB_*`, `DB_SSL=true`, `JWT_SECRET`, `REFRESH_TOKEN_KEY`, `ALLOW_ORIGIN=<your frontend URL>`).
4. Run `php lava migration run` once against the Aiven database (from your computer, with the Aiven values in `.env`), then set `MIGRATION_ENABLED=false` on Render.

## 5. Frontend (React + Vite)

```bash
cd frontend
cp .env.example .env     # VITE_API_URL=http://localhost:3000  (or your Render URL)
npm install
npm run dev
```

Login → Product list → Add / Edit / Delete → Logout. The app only talks to the API; it never connects to the database.
php lava migrat