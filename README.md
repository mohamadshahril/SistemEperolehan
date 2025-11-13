# Laravel + Vue Starter Kit

## Introduction

Our Vue starter kit provides a robust, modern starting point for building Laravel applications with a Vue frontend using [Inertia](https://inertiajs.com).

Inertia allows you to build modern, single-page Vue applications using classic server-side routing and controllers. This lets you enjoy the frontend power of Vue combined with the incredible backend productivity of Laravel and lightning-fast Vite compilation.

This Vue starter kit utilizes Vue 3 and the Composition API, TypeScript, Tailwind, and the [shadcn-vue](https://www.shadcn-vue.com) component library.

## Official Documentation

Documentation for all Laravel starter kits can be found on the [Laravel website](https://laravel.com/docs/starter-kits).

## Contributing

Thank you for considering contributing to our starter kit! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## License

The Laravel + Vue starter kit is open-sourced software licensed under the MIT license.

---

### Guide: Create a Location module (Laravel + Vue 3 + Inertia)

Follow these high-level steps to add a standard CRUD "Location" module consistent with this project:

1) Backend: Model and Migration
- Run: php artisan make:model Location -m
- In the migration, add fields such as code (unique), name, address lines, city, state, postcode, country, timestamps, softDeletes; add an index on name.
- Run: php artisan migrate

2) Validation Requests
- Run: php artisan make:request LocationStoreRequest and LocationUpdateRequest
- Define rules for required code/name, code unique, and optional address fields.

3) Controller (Inertia)
- Run: php artisan make:controller LocationController
- Implement actions: index (with optional search + pagination), create, store, edit, update, destroy.
- Return Inertia pages: locations/Index, locations/Create, locations/Edit.

4) Routes
- In routes/web.php, inside Route::middleware('auth') group, add: Route::resource('locations', LocationController::class);

5) Frontend Pages (Vue 3)
- Create components under resources/js/pages/locations: Index.vue, Create.vue, Edit.vue.
- Index.vue: list paginated results with optional search and link to create/edit.
- Create/Edit.vue: use @inertiajs/vue3 useForm to post/put to locations.store/locations.update.

6) Client-side Routing (if applicable)
- If using module route files (see resources/js/routes), create resources/js/routes/locations/index.ts exporting routes:
  - /locations -> Index.vue
  - /locations/create -> Create.vue
  - /locations/:id/edit -> Edit.vue
- Ensure these routes are merged into the app router like purchase-requests.

7) Navigation
- Add a sidebar/header link to route('locations.index') similar to existing menu items.

8) Policies/Permissions (optional)
- php artisan make:policy LocationPolicy --model=Location
- Register in AuthServiceProvider and enable authorizeResource in LocationController if needed.

9) UX and Feedback
- Use redirect()->with('success', '...') in controller; ensure layout renders flash messages and validation errors.

10) Tests (recommended)
- php artisan make:test LocationControllerTest
- Write feature tests for index/store/update/destroy and permissions.

11) Finalize
- Run migrations/seeders, npm run dev (or build), then smoke test index/create/edit/delete flows.
