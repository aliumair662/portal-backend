# Van Wijk Uitvaartkisten

## Goal

Creating a portal that gives Depots and Funeral homes the possibility to manage their own stock in a centralized system, reduce the direct communication being made to Van Wijk by automating parts of the ordering process and reduce the manual work.

## Laravel backend

This repository holds the Laravel application that acts as:
* a layer between the Odoo API
* a layer between database storage for Portal data
* an API for the frontend (vue) to interact with Portal data & Odoo API

## Setup first time
* ``lando start``
* ``lando composer install``
* ``lando artisan key:generate``
* ``lando artisan storage:link``
* ``cp .env.example .env``
* Fill in password for odoo in .env file
* ``lando artisan odoo:product:images``
* ``lando artisan migrate:fresh --seed``

Once this is done the laravel framework should be ready te be used by the frontend.

## Odoo API

Based on https://github.com/Edujugon/laradoo but added our own model wrappers

_**Defined models**_
* Location
* OrganizationType
* Partner
* Pricelist
* PricelistItem
* Product
* ProductVariant
* Stock
* Translation

_Not used_
* Tarifs
* TarifsAdv

## Services
* PricelistService
* ProductService
* ProductVariantService
* ShippingService
* StockService

## API routes
check `routes/api.php` 

## Commands
```odoo:product:images```
This stores all product images on the server, so we don't need to query the Odoo API every time. 
