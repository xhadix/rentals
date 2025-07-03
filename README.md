# Multi Region Product Rental API

A optimized Laravel API for a Product Rental Service with regional pricing variations.

## 🚀 Features

- **Product Management**: Complete product catalog with attributes and descriptions
- **Regional Pricing**: Dynamic pricing based on region and rental period 
- **Optimized Queries**: Eloquent eager loading
- **Redis Caching**: Backend caching for improved performance (5-minute TTL)
- **HTTP Caching**: Client-side caching with proper cache headers and ETag support
- **Gzip Compression**: Response compression
- **API Filtering**: Filter products by region and rental period
- **Pagination**: handling large datasets with proper cache key management
- **Database Indexes**: Optimized database performance with indexing
- **Unit Testing**: test coverage


## Requirements

- PHP 8.1+
- Laravel 12
- Docker & Docker Compose
- Composer


**Note**: PostgreSQL and Redis run in Docker containers, so no local installation needed.


## Installation

### 1. Clone the Repository

```bash
git clone git@github.com:xhadix/rentals.git
cd rentals
```

### 2. Start Docker Services (Database & Redis)

**Important**: Start Docker services first as the database and Redis run in containers.

```bash
docker compose up -d
```

This will start:
- PostgreSQL database (port 5432)
- Redis cache (port 6380) - **Note**: Uses port 6380 to avoid conflict with local Redis installed


### 3. Install Dependencies

```bash
composer install
```

### 4. Environment Configuration

Create a `.env` file from the example:

```bash
cp .env.example .env
```

Key settings for Docker services:

```
APP_NAME="multi region product listing"
APP_ENV=local
APP_DEBUG=false  # Set to false to enable gzip compression
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cinch
DB_USERNAME=cinch
DB_PASSWORD=cinchtest

CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6380  # Docker Redis port
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations and Seeders

Now that Docker services are running, set up the database:

```bash
php artisan migrate
php artisan db:seed
```