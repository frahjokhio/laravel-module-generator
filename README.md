# laravel-module-generator

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

A Laravel package to quickly generate modular structures with CRUD boilerplate for APIs. It scaffolds Controllers, Services, Repositories, Requests, Models, routes, and Service Providers for fast and consistent modular development.

## Installation

```bash
composer require frahjokhio/module-generator
```

## Usage

Generate a new module:

```bash
php artisan make:module ModuleName
```

The command will prompt you for:

- Migration creation  
- Seeder creation  
- Columns for the migration, e.g.:

```
title:string:unique, content:text, status:enum:draft|published|archived
```

### Generated Module Structure

```
Modules/
└── ModuleName/
    ├── Controllers/
    ├── Services/
    ├── Repositories/
    ├── Requests/
    ├── Models/
    ├── routes/
    └── ModuleNameServiceProvider.php
```

### Features

- Auto-generates:
  - Controller with basic CRUD methods
  - Service and Repository for business logic
  - FormRequest for validation
  - Model with `$fillable`
  - API routes
  - ServiceProvider registration
- Optional migration and seeder generation
- Supports Laravel 12
- Stubs are publishable for customization:

```bash
php artisan vendor:publish --tag=module-stubs
```

## License

MIT License.
