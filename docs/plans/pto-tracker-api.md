# PTO Tracker API

## Overview

Add a minimal API to the PTO Tracker app (`~/Websites/pto_tracker`, Laravel 6) so the dora-metrics app can query employee time-off data for sprint capacity planning. Two endpoints: one for querying time-off by date range, one for listing employees (used for auto-linking builders).

**Companion plan**: The dora-metrics consumer side (PaidTimeOffService, builder linking, capacity calculations, test connection in admin settings) is documented in `docs/plans/team-details-and-carryover.md` in the dora-metrics repo.

---

## Current State

- The PTO Tracker is a Laravel 6 app with no active API routes (`routes/api.php` is empty or only has the default scaffolding).
- Employee and time-off data exists in the database but is only accessible through the web UI.
- No authentication mechanism exists for API consumers.

---

## Implementation

### 1. Shared-Token Auth Middleware

Create `app/Http/Middleware/ApiTokenAuth.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = config('app.api_token');

        if (! $token || $request->bearerToken() !== $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php` under `$routeMiddleware`:

```php
'auth.api-token' => \App\Http\Middleware\ApiTokenAuth::class,
```

**Environment**: Add `API_TOKEN` to `.env`. Both the PTO Tracker and dora-metrics apps store the same token. Add `'api_token' => env('API_TOKEN')` to `config/app.php`.

---

### 2. API Routes

In `routes/api.php`:

```php
Route::middleware('auth.api-token')->group(function () {
    Route::get('/time-off', 'Api\TimeOffController@index');
    Route::get('/employees', 'Api\EmployeeController@index');
});
```

---

### 3. Time-Off Endpoint

**Controller**: `app/Http/Controllers/Api/TimeOffController.php`

**Route**: `GET /api/time-off`

**Query parameters**:

| Parameter | Type | Required | Description |
|---|---|---|---|
| `employee_names[]` | array of strings | No* | Filter by employee name (case-insensitive) |
| `employee_ids[]` | array of integers | No* | Filter by employee ID |
| `start_date` | Y-m-d | Yes | Start of date range |
| `end_date` | Y-m-d | Yes | End of date range |

*At least one of `employee_names[]` or `employee_ids[]` should be provided. If neither is given, return all employees with PTO in the range.

**Query logic**: Approved PTO records where `start_time <= end_date AND end_time >= start_date` (overlapping the requested window), grouped by employee.

**Response**:

```json
{
  "data": [
    {
      "employee_id": 5,
      "employee_name": "Jane Doe",
      "pto_days": 3.5,
      "entries": [
        {
          "start_time": "2026-04-07",
          "end_time": "2026-04-09",
          "days": 3,
          "is_half_day": false,
          "is_approved": true,
          "description": "Vacation"
        }
      ]
    }
  ]
}
```

**`pto_days`**: Sum of days across all approved entries for this employee in the requested window. Half-days count as 0.5.

---

### 4. Employees Endpoint

**Controller**: `app/Http/Controllers/Api/EmployeeController.php`

**Route**: `GET /api/employees`

**Query parameters**:

| Parameter | Type | Required | Description |
|---|---|---|---|
| `per_page` | integer | No | Results per page (default 100). Used by dora-metrics `testConnection()` with `per_page=1` for a lightweight health check. |

**Response**:

```json
{
  "data": [
    {
      "id": 5,
      "name": "Jane Doe",
      "email": "jane.doe@company.com",
      "active": true
    }
  ],
  "meta": {
    "total": 42,
    "per_page": 100,
    "current_page": 1
  }
}
```

This endpoint serves two purposes:
1. **Health check**: dora-metrics `PaidTimeOffService::testConnection()` calls `GET /api/employees?per_page=1` to verify the API is reachable and the token is valid.
2. **Auto-linking**: dora-metrics `builders:link-pto` artisan command fetches the full employee list and matches by case-insensitive name to set `pto_employee_id` on builders.

---

### 5. Validation

Add Form Request classes for both endpoints following Laravel 6 conventions:

- `TimeOffRequest`: validates `start_date` (required, date), `end_date` (required, date, after_or_equal:start_date), `employee_names` (optional, array), `employee_ids` (optional, array of integers).
- `EmployeeRequest`: validates `per_page` (optional, integer, min:1, max:500).

---

### 6. Tests

- **Middleware test**: verify requests without a Bearer token get 401. Verify requests with an invalid token get 401. Verify valid token passes through.
- **Time-off endpoint test**: create PTO records, verify filtering by date range and employee names/IDs, verify `pto_days` calculation with half-days, verify only approved records are returned.
- **Employees endpoint test**: verify employee list response shape, verify `per_page` pagination, verify the health-check pattern (`per_page=1`) returns a valid response.

---

## Key Decisions

- **Shared bearer token**: Simple `API_TOKEN` env variable checked in middleware. No Sanctum/Passport overhead for this internal integration. Both apps store the same token in their `.env` files.
- **Two endpoints, not one**: Separating `/api/time-off` and `/api/employees` keeps each endpoint focused. The employees endpoint doubles as a health check for test-connection.
- **`per_page=1` as health check**: Rather than adding a dedicated `/api/health` or `/api/ping` endpoint, the employees endpoint with `per_page=1` serves as a lightweight connectivity test that also verifies auth is working.
- **Laravel 6 conventions**: This app uses Laravel 6, so no constructor property promotion, no `match` expressions, no named arguments. Controllers use the `Controller@method` string syntax in routes.
