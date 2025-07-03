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

### 7. Start the Laravel Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`



## Database Schema

### Core Tables

- **products**: Product information (name, description, SKU, etc.)
- **attributes**: Attribute types (color, size, brand)
- **attribute_values**: Specific values for attributes
- **product_attributes**: Pivot table linking products to their attributes
- **regions**: Available regions (Singapore, Malaysia, Thailand, Indonesia)
- **rental_periods**: Available rental durations (3, 6, 12 months)
- **product_pricings**: Regional and period-based pricing 


### Relationships

- Product → hasMany ProductAttribute, ProductPricing
- Attribute → hasMany AttributeValue, belongsToMany Product
- Region → hasMany ProductPricing
- RentalPeriod → hasMany ProductPricing


### Indexes

#### Products Table
```sql
-- Performance indexes
INDEX idx_products_sku ON products(sku)           -- Fast SKU lookups
INDEX idx_products_is_active ON products(is_active) -- Filter active products
UNIQUE idx_products_sku_unique ON products(sku)    -- Enforce SKU uniqueness
```

#### Product Pricings Table (Critical for API Performance)
```sql
-- Composite unique constraint
UNIQUE idx_product_pricings_unique ON product_pricings(product_id, region_id, rental_period_id)

-- Individual foreign key indexes
INDEX idx_product_pricings_product_id ON product_pricings(product_id)
INDEX idx_product_pricings_region_id ON product_pricings(region_id)
INDEX idx_product_pricings_rental_period_id ON product_pricings(rental_period_id)
INDEX idx_product_pricings_is_active ON product_pricings(is_active)
```

#### Product Attributes Table
```sql
-- Composite unique constraint
UNIQUE idx_product_attributes_unique ON product_attributes(product_id, attribute_id)

-- Foreign key indexes
INDEX idx_product_attributes_product_id ON product_attributes(product_id)
INDEX idx_product_attributes_attribute_id ON product_attributes(attribute_id)
```

#### Regions Table
```sql
INDEX idx_regions_code ON regions(code)           -- Fast region code lookups (SG, MY, TH, ID)
INDEX idx_regions_is_active ON regions(is_active) -- Filter active regions
UNIQUE idx_regions_code_unique ON regions(code)   -- Enforce unique region codes
```

#### Attribute Values Table
```sql
-- Composite unique constraint
UNIQUE idx_attribute_values_unique ON attribute_values(attribute_id, value)
INDEX idx_attribute_values_is_active ON attribute_values(is_active)
```



## API Endpoints

### Get All Products

```
GET /api/products
```

**Query Parameters:**
- `region` (optional): Filter by region code (SG, MY, TH, ID)
- `rental_period` (optional): Filter by rental period in months (3, 6, 12)
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 20)

**Example:**
``` 
http://localhost:8000/api/products?region=SG&rental_period=3
```

### Get Single Product

```
GET /api/products/{id}
```

**Example:**
```
http://localhost:8000/api/products/1
```

## API Filtering

### Regional Availability Filtering

```
http://localhost:8000/api/products
```

**Singapore Products:**
```
http://localhost:8000/api/products?region={region code}
```


**Example:**
```
http://localhost:8000/api/products?region=SG
```

### Rental Period Filtering

**3-Month Rentals:**
```
http://localhost:8000/api/products?rental_period=(number of months [3,6,12])
```
**Example:**
```
http://localhost:8000/api/products?rental_period=3
```

### Pagination Examples

**Basic Pagination:**
```
# Get first page with 2 items
http://localhost:8000/api/products?per_page=2&page=1

# Get second page with 2 items  
http://localhost:8000/api/products?per_page=2&page=2
```

**Pagination with Filters:**
```bash
# regions products with pagination
"http://localhost:8000/api/products?region=ID&per_page=1&page=2"
```

### Combined Region + Rental Period Filtering

**Nintendo Switch in Indonesia (6-month rental):**
```
http://localhost:8000/api/products?region=ID&rental_period=6
```

**Products in Singapore (12-month rental):**
```
http://localhost:8000/api/products?region=SG&rental_period=12
```

### HTTP Headers

All API responses include optimized caching and compression headers:

```
Cache-Control: public, max-age=300
ETag: "a1b2c3d4e5f6..."
Content-Encoding: gzip
Expires: Thu, 03 Jul 2025 12:00:00 GMT
```


### Product List Response

```json
{
    "data": [
        {
            "id": 7,
            "name": "Canon EOS R6 Mark II",
            "description": "Canon EOS R6 Mark II mirrorless camera with professional-grade features.",
            "sku": "CANON-EOSR6M2-001",
            "image_url": "https://example.com/images/canon-eos-r6m2.jpg",
            "is_active": true,
            "created_at": "2025-07-03T12:57:14.000000Z",
            "updated_at": "2025-07-03T12:57:14.000000Z"
        },
        {
            "id": 5,
            "name": "Dell XPS 13 Plus",
            "description": "Dell XPS 13 Plus ultrabook with Intel 13th gen processor and premium build quality.",
            "sku": "DELL-XPS13-001",
            "image_url": "https://example.com/images/dell-xps13.jpg",
            "is_active": true,
            "created_at": "2025-07-03T12:57:14.000000Z",
            "updated_at": "2025-07-03T12:57:14.000000Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/products?page=1",
        "last": "http://localhost:8000/api/products?page=4",
        "prev": null,
        "next": "http://localhost:8000/api/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 4,
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/products?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": "http://localhost:8000/api/products?page=2",
                "label": "2",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/products?page=3",
                "label": "3",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/products?page=4",
                "label": "4",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/products?page=2",
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "path": "http://localhost:8000/api/products",
        "per_page": 2,
        "to": 2,
        "total": 7
    }
}
```

### Single Product Response

```json
{
    "data": {
        "id": 1,
        "name": "MacBook Pro 16\" M3",
        "description": "Apple MacBook Pro 16-inch with M3 chip, perfect for professional work and creative tasks.",
        "sku": "MBP-16-M3-001",
        "image_url": "https://cdsassets.apple.com/live/SZLF0YNV/images/sp/111901_mbp16-gray.png",
        "attributes": [
            {
                "name": "color",
                "display_name": "Color",
                "value": "black",
                "display_value": "Black"
            },
            {
                "name": "size",
                "display_name": "Size",
                "value": "large",
                "display_value": "Large"
            },
            {
                "name": "brand",
                "display_name": "Brand",
                "value": "apple",
                "display_value": "Apple"
            }
        ],
        "pricing": [
            {
                "region": {
                    "id": 3,
                    "name": "Thailand",
                    "code": "TH",
                    "currency": "THB"
                },
                "rental_periods": [
                    {
                        "months": 3,
                        "display_name": "3 Months",
                        "price": "225.00",
                        "currency": "THB"
                    },
                    {
                        "months": 6,
                        "display_name": "6 Months",
                        "price": "202.50",
                        "currency": "THB"
                    },
                    {
                        "months": 12,
                        "display_name": "12 Months",
                        "price": "180.00",
                        "currency": "THB"
                    }
                ]
            },
            {
                "region": {
                    "id": 2,
                    "name": "Malaysia",
                    "code": "MY",
                    "currency": "MYR"
                },
                "rental_periods": [
                    {
                        "months": 3,
                        "display_name": "3 Months",
                        "price": "255.00",
                        "currency": "MYR"
                    },
                    {
                        "months": 6,
                        "display_name": "6 Months",
                        "price": "229.50",
                        "currency": "MYR"
                    },
                    {
                        "months": 12,
                        "display_name": "12 Months",
                        "price": "204.00",
                        "currency": "MYR"
                    }
                ]
            },
            {
                "region": {
                    "id": 1,
                    "name": "Singapore",
                    "code": "SG",
                    "currency": "SGD"
                },
                "rental_periods": [
                    {
                        "months": 3,
                        "display_name": "3 Months",
                        "price": "330.00",
                        "currency": "SGD"
                    },
                    {
                        "months": 6,
                        "display_name": "6 Months",
                        "price": "297.00",
                        "currency": "SGD"
                    },
                    {
                        "months": 12,
                        "display_name": "12 Months",
                        "price": "264.00",
                        "currency": "SGD"
                    }
                ]
            }
        ]
    }
}
```

##  Testing

### Run All Tests

```bash
php artisan test
```

## Performance Optimizations

### 1. Eager Loading
- All queries use proper `with()` relationships to prevent N+1 queries

### 2. Redis Caching (Backend)
- Redis caching for product lists and details (5-minute TTL)
- **Smart cache key strategy**: Includes page number, filters, and per_page in cache key
- Cache stored in Docker Redis container (port 6380)
- Cache invalidation methods in ProductService

**Cache Key Strategy:**
```php
$cacheKey = "products:index:" . md5(serialize([
    'region' => $regionCode,
    'rental_period' => $rentalPeriodMonths,
    'per_page' => $perPage,
    'page' => $currentPage,  // Essential for pagination
]));
```

### 3. HTTP Caching (Client-side)
- **Cache-Control**: `public, max-age=300` (5 minutes)
- **ETag Support**: MD5 hash of response content for efficient caching
- **304 Not Modified**: Returns 304 when content hasn't changed
- **Expires Header**: Explicit expiration time for better browser caching

### 4. Gzip Compression
- **Custom Middleware**: `GzipResponse` middleware for response compression
- **Selective Compression**: Only compresses responses > 100 bytes

### 5. Database Indexes
- Foreign key indexes on all relationship columns
- Composite unique indexes for business rule enforcement
- Performance indexes on frequently queried columns (is_active, codes)

### 6. Pagination Optimization
- **Efficient pagination**: Uses Laravel's built-in pagination with proper cache keys
- **Cache per page**: Each page/filter combination gets its own optimized cache entry

## Architecture

### Models
- **Product**: Main product model with relationships
- **Attribute/AttributeValue**: Flexible attribute system
- **Region/RentalPeriod**: Reference data
- **ProductPricing**: Complex pricing matrix
- **ProductAttribute**: Pivot model for product attributes

### Controllers
- **ProductController**: Clean API endpoints using service layer
- Separation of concerns with dependency injection

### Middleware
- **HttpCacheMiddleware**: Sets proper HTTP cache headers and ETag support
- **GzipResponse**: Custom gzip compression middleware

### Resources
- **ProductResource**: Collection response formatting
- **ProductDetailResource**: Single product with full details
- **PricingResource**: Pricing data formatting

### Services
- **ProductService**: Business logic and caching layer
- Redis cache management with TTL
- **Smart pagination caching**: Page-aware cache key generation

## 🔧 Configuration

### Cache Configuration
The application uses Redis for backend caching:
- **Redis Host**: Docker container (port 6380)
- **Cache TTL**: 5 minutes
- **Cache Database**: Redis database 1
- **Cache Strategy**: Smart key generation including pagination parameters
- To disable caching, set `CACHE_STORE=array` in `.env`

### Compression Configuration
Gzip compression is enabled by default:
- **Minimum Size**: 100 bytes
- **Compression Level**: Default (varies by content)
- **Headers Added**: `Content-Encoding: gzip`

### HTTP Cache Configuration
Client-side caching configured via middleware:
- **Max Age**: 300 seconds (5 minutes)
- **Cache Type**: Public (can be cached by proxies)
- **ETag**: MD5 hash of response content

## 📞 API Examples

### Basic Queries
```bash
# Get all products
curl "http://localhost:8000/api/products"

# Get product details
curl "http://localhost:8000/api/products/1"

# Get products with pagination
curl "http://localhost:8000/api/products?page=2&per_page=5"
```

### Regional Filtering
```bash
# Singapore products only
curl "http://localhost:8000/api/products?region=SG"

# Indonesia products only (limited selection)
curl "http://localhost:8000/api/products?region=ID"

# Thailand products
curl "http://localhost:8000/api/products?region=TH"
```

### Rental Period Filtering
```bash
# 3-month rentals
curl "http://localhost:8000/api/products?rental_period=3"

# 12-month rentals (best discount)
curl "http://localhost:8000/api/products?rental_period=12"
```

### Combined Filtering
```bash
# Premium products in Singapore with 6-month rental
curl "http://localhost:8000/api/products?region=SG&rental_period=6"

# Budget options in Indonesia
curl "http://localhost:8000/api/products?region=ID&rental_period=12"
```

## Performance Metrics

### Response Compression
- **Uncompressed**: ~1,588 bytes
- **Gzip Compressed**: ~702 bytes

### Caching Strategy
- **Backend Cache**: Redis (5-minute TTL)
- **HTTP Cache**: Browser/proxy cache (5 minutes)
- **ETag Support**: Efficient conditional requests
- **Pagination Cache**: per-page caching with filter awareness

### Query Optimization
- **N+1 Prevention**: Eager loading with `with()`
- **Database Indexes**: strategic indexes across tables
- **Foreign Key Performance**: All foreign keys indexed for fast JOINs

### Database Performance
- **Index Coverage**: foreign keys and frequently queried columns
- **Composite Indexes**: Optimized for complex filter queries
- **Unique Constraints**: Business rule enforcement with performance benefits

