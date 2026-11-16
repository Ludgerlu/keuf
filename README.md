# KEUF

KEUF is a web application designed for managing construction and site operations. It centralizes the follow-up of projects, employees, attendance, stock, and operational incidents to improve coordination and reporting across the company.

## Project description

This application is intended for project managers and operational teams who need a clear, structured, and traceable way to manage worksites and internal processes. It covers the main activities of a construction or field-management environment:

- employee management and assignment to projects;
- project and phase tracking;
- daily attendance and presence monitoring;
- stock management for materials and supplies;
- issue and remark management for each project;
- dashboards and indicators by user profile.

The platform is built around a role-based organization so each user can access the data and actions relevant to their responsibilities.

## Main features

- Management of employees and their project assignments
- Tracking of projects, phases, and progress percentages
- Presence management with daily attendance follow-up
- Project stock and material movement monitoring
- Reporting of incidents, priorities, and operational remarks
- Role-based dashboards for administrators, technical directors, controllers, and stock managers
- Reporting and export functionalities for operational data

## Technical stack

- PHP 8.1+
- Laravel 10
- Blade templates
- MySQL database
- Vite frontend build tool
- MVC architecture with Laravel conventions

## Project structure

- `app/` : application logic, models, controllers, and providers
- `routes/` : web routes for the application
- `resources/views/` : interface templates and dashboards
- `database/migrations/` : database structure
- `public/` : publicly served assets
- `tests/` : automated tests

## Installation

1. Clone the repository.
2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install frontend dependencies:

   ```bash
   npm install
   ```

4. Create the environment file:

   ```bash
   cp .env.example .env
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Configure the database credentials in the `.env` file.
7. Run the database migrations:

   ```bash
   php artisan migrate
   ```

8. Start the application:

   ```bash
   php artisan serve
   ```

9. For frontend assets, run:

   ```bash
   npm run dev
   ```

## Usage

Once connected, users can access their dashboard according to their role and manage daily operations such as project follow-up, workforce attendance, material stock, and incident tracking.

## License

This project is licensed under the MIT license.
