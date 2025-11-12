# Laravel Module Generator

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

A Laravel package to quickly generate modular structures with CRUD boilerplate for APIs.  
It scaffolds Controllers, Services, Repositories, Requests, Models, routes, and Service Providers for fast and consistent modular development.

---

## 🚀 Installation

```bash
composer require frahjokhio/module-generator
```

---

## ⚙️ Usage

Generate a new module:

```bash
php artisan make:module ModuleName
```

The command will prompt you for:

- Migration creation  
- Seeder creation  
- Column definitions for the migration

---

## 🧱 Column Definition Format

Each column follows this format:

```
column_name:type:modifier1:modifier2,...
```

- Separate each column with a comma `,`
- Separate properties of a single column with a colon `:`
- Modifiers like `nullable`, `unique`, or `default(...)` are optional

### Examples

| Column | Example | Description |
|--------|----------|-------------|
| String | `title:string:unique` | Simple string with unique constraint |
| Nullable | `slug:string:unique:nullable` | String column that can be null |
| Text | `content:text:nullable` | Large text field |
| Enum | `status:enum:draft\|published\|archived:default('draft')` | Enum with allowed values and default value |
| Boolean | `is_featured:boolean:default(false)` | Boolean with default value |
| Foreign Key | `user_id:foreignId:constrained` | Foreign key to `users.id` |

### Example Input

```
title:string:unique,
user_id:integer,
slug:string:unique:nullable,
content:text:nullable,
status:enum:draft|published|archived:default('draft'),
is_featured:boolean:default(false),
user_id:foreignId:constrained
```

---

## 🧩 Validation Mapping

When generating Form Requests, each column type automatically maps to appropriate validation rules.

| Type | Validation Rule |
|------|------------------|
| string | `string\|max:255` |
| text | `string\|max:255` |
| email | `email\|max:255` |
| password | `string\|min:8\|max:255` |
| integer, bigint | `integer` |
| boolean | `boolean` |
| float, decimal, double | `numeric` |
| date, datetime, timestamp | `date` |
| json | `array` |
| foreignId | `integer` + `exists:table,id` if `constrained` |
| enum | `in:value1,value2,...` |

### Other Rule Behaviors

- Adds `'required'` unless the field is `nullable` or has a `default(...)`
- Adds `Rule::unique('table', 'column')` if `unique` is specified
- Enum values are parsed automatically from the definition

---

## 📁 Generated Module Structure

```
Modules/
└── ModuleName/
    ├── Controllers/
    ├── Services/
    ├── Repositories/
    ├── Requests/
    ├── Models/
    ├── Routes/
    └── ModuleNameServiceProvider.php
```

---

## ✨ Features

- Auto-generates:
  - Controller with CRUD methods
  - Service and Repository for logic separation
  - FormRequest with validation rules
  - Model with `$fillable` properties
  - API routes and module provider
- Optional migration and seeder generation
- Laravel 12 compatible
- Requires PHP 8.2 or above

---

## 📜 License

MIT License.
