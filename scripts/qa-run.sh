#!/usr/bin/env bash
# Run Playwright E2E (Laravel tests are run by composer qa first).
# Ensure database is seeded and server running: php artisan migrate:fresh --seed && php artisan serve
# Optional: BASE_URL=http://127.0.0.1:8000
set -e
echo "Running Playwright E2E (BASE_URL=${BASE_URL:-http://127.0.0.1:8000})..."
npx playwright test
echo "E2E passed."
