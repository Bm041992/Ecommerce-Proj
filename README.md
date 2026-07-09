# Shopify Product Importer

A Laravel application that imports Shopify products from a CSV file using the Shopify REST API. Product imports are processed asynchronously using Laravel Queues and monitored through a dashboard.

## Features

* CSV file upload
* CSV header validation
* Asynchronous product import using Laravel Queue
* Create and update products in Shopify
* Import dashboard with upload status
* Product status tracking (Pending, Processing, Successful, Failed)
* Error message display for failed uploads
* Server-side DataTables using Yajra

## Requirements

* PHP 8.2+
* Composer
* MySQL

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/Bm041992/Ecommerce-Proj.git
cd Ecommerce-Proj
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure the environment

```bash
cp .env.example .env
```

Update the `.env` file with your database and Shopify credentials.

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Run the migrations

```bash
php artisan migrate
```

### 6. Start the queue worker

```bash
php artisan queue:work
```

### 7. Run the application

```bash
php artisan serve
```

## Usage

1. Upload a valid Shopify product CSV file.
2. The application validates the CSV headers before processing.
3. Products are uploaded to Shopify asynchronously using a queue.
4. Monitor the import progress from the dashboard.

## Dashboard

The dashboard displays:

* Product Title
* SKU
* Shopify Product ID
* Upload Status (Pending, Processing, Successful, Failed)
* Error Message for failed uploads
