# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

Nagdash is a standalone PHP dashboard for aggregating Nagios monitoring status from multiple Nagios instances. It uses Twig 3.0 for templating, jQuery/Bootstrap on the frontend, and supports two Nagios backends: `nagios-api` (REST JSON) and `livestatus`.

## Commands

```bash
# Install PHP dependencies
composer install

# Install Node dependencies (for CSS linting only)
npm install

# Lint CSS
npm run lint

# Lint/format PHP (Laravel Pint)
vendor/bin/pint

# Test with mock data: set $mock_state_file in config.php
# $mock_state_file = './test_data/state';
```

There are no automated tests. The `test_data/state` file provides a mock Nagios state for manual testing.

## Architecture

**Entry points** (all in `public/`):
- `index.php` — renders the main page layout (uses Twig, reads config, sets up JS)
- `nagdash.php` — AJAX endpoint polled repeatedly by JS; returns rendered dashboard HTML
- `do_action.php` — handles POST actions (ack, downtime, enable/disable notifications)
- `do_settings.php` — handles settings form, stores preferences in cookies, redirects back

**Request flow**: Browser loads `index.php` → JS (`public/js/nagdash.js`) polls `nagdash.php` on an interval → dashboard HTML is injected into `#nagioscontainer`. Actions call `do_action.php` via POST and show results in a modal.

**Source code** (`src/`, PSR-4 namespace `Nagdash\`):
- `bootstrap.php` — requires autoloader, loads all classes and `config.php`, defines `nagdash_twig()`
- `Controllers/DashboardController.php` — aggregates state from all configured Nagios instances, filters, and sorts
- `Controllers/ActionController.php` — routes actions (ack, downtime, etc.) to the correct Nagios instance
- `Controllers/SettingsController.php` — writes cookie-based user preferences
- `NagiosConnection.php` — interface that both API implementations satisfy
- `NagiosApi.php` — implementation for `nagios-api` REST backend
- `NagiosLivestatus.php` — implementation for `livestatus-api` backend
- `utils.php` — `NagdashHelpers` static class: curl wrapper, state fetching, data parsing, factory methods
- `timeago.php` — converts Unix timestamps to human-readable relative strings

**Templates** (`templates/`, Twig):
- `layout.twig` — full HTML page; injects tag color CSS from config; loads CDN jQuery + Bootstrap 2
- `dashboard.twig` — the AJAX response; renders host/service status tables
- `partials/controls.twig` — action buttons (ack, downtime, silence)
- `partials/settings.twig` — settings modal content

## Configuration

Copy `config.php.example` to `config.php`. Key settings:

```php
$api_type = "nagios-api";        // or "livestatus"
$nagios_hosts = [                 // one entry per Nagios instance
  ["hostname" => "...", "port" => 6315, "tag" => "DC1", "tagcolour" => "#336699", ...]
];
$refresh_every_ms = 20000;        // AJAX poll interval
$mock_state_file = './test_data/state';  // enable for local testing
```

`config.php` is gitignored. The `public/` directory should be the web server DocumentRoot.

## Key Patterns

- **Multi-instance aggregation**: `DashboardController::getData()` calls `NagdashHelpers::get_nagios_host_data()` which iterates `$nagios_hosts` and fetches/merges state from each
- **API factory**: `NagdashHelpers::get_nagios_api_object()` returns the correct `NagiosApi` or `NagiosLivestatus` instance based on `$api_type`
- **Settings persistence**: User preferences (host filter regex, sort order, enabled instances) live in cookies set by `SettingsController`, read in `nagdash.php` and `index.php`
- **Tag colors**: Each Nagios instance has a `tag` and `tagcolour`; these are injected as inline CSS in `layout.twig` and used throughout the dashboard to identify which instance a host belongs to
