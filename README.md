# Contact Management System – Merge Contacts

## Tech Stack
- Laravel 10
- PHP 8.2
- MySQL
- Alpine.js / jQuery
- Tailwind CSS
- NPM 18

## Features
- Dynamic custom fields
- Contact CRUD
- Contact merge with conflict handling
- No data loss guarantee
- Soft deletes & merge tracking

## Database Design
- contacts
- custom_fields
- contact_custom_field_values
- merged_contacts (or merge_logs)

## Merge Logic
- Primary contact is preserved
- Secondary contact marked as merged
- Conflicts resolved via user selection
- Original values retained in merge log

## How Merge is Tracked
- merged_into_contact_id
- is_merged flag
- merge_history table (JSON snapshot)

## Setup Instructions
1. git clone
2. composer install
3. npm install
4. php artisan migrate
5. php artisan migrate db:seed --class=DatabaseSeeder
6. npm run dev
7. php artisan serve

## Demo Video
👉 (Google Drive / Loom link)
