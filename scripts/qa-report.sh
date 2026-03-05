#!/usr/bin/env bash
#
# QA Report runner: runs Laravel Feature + Unit tests, then Playwright E2E,
# and prints a summary with passed/failed scenarios and failure details.
#
# Prerequisites:
#   - For E2E: php artisan migrate:fresh --seed && php artisan serve (in another terminal)
#   - BASE_URL defaults to http://127.0.0.1:8000
#
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
export BASE_URL
REPORT_DIR="${REPORT_DIR:-/tmp/glm-qa}"
mkdir -p "$REPORT_DIR"
PHPUNIT_LOG="$REPORT_DIR/phpunit.log"
PLAYWRIGHT_LOG="$REPORT_DIR/playwright.log"

echo "=============================================="
echo "  GLM QA – Full test run + report"
echo "=============================================="
echo ""

# --- Laravel tests ---
echo "[1/2] Running Laravel tests (Feature + Unit)..."
./vendor/bin/phpunit 2>&1 | tee "$PHPUNIT_LOG"
[ ${PIPESTATUS[0]} -eq 0 ] && PHPUNIT_OK=1 || PHPUNIT_OK=0
echo ""

# --- Playwright E2E ---
echo "[2/2] Running Playwright E2E (BASE_URL=$BASE_URL)..."
npx playwright test 2>&1 | tee "$PLAYWRIGHT_LOG"
[ ${PIPESTATUS[0]} -eq 0 ] && PLAYWRIGHT_OK=1 || PLAYWRIGHT_OK=0
echo ""

# --- QA Report summary ---
echo "=============================================="
echo "  QA REPORT SUMMARY"
echo "=============================================="
if [ "$PHPUNIT_OK" -eq 1 ]; then
  PHPUNIT_SUMMARY=$(grep -E "^(OK|Tests:)" "$PHPUNIT_LOG" | head -2 || true)
  echo "  Laravel tests:    PASSED  $PHPUNIT_SUMMARY"
else
  echo "  Laravel tests:    FAILED"
fi
if [ "$PLAYWRIGHT_OK" -eq 1 ]; then
  echo "  Playwright E2E:   PASSED"
else
  echo "  Playwright E2E:   FAILED"
fi
echo ""

FAILED=0
[ "$PHPUNIT_OK" -eq 0 ] && FAILED=1
[ "$PLAYWRIGHT_OK" -eq 0 ] && FAILED=1

if [ "$FAILED" -eq 1 ]; then
  echo "----------------------------------------------"
  echo "  FAILED SCENARIOS / BUGS"
  echo "----------------------------------------------"
  if [ "$PHPUNIT_OK" -eq 0 ]; then
    echo ""
    echo "Laravel (PHPUnit) failures:"
    grep -A 2 "FAILURES!\|ERRORS!\|1) Tests\\" "$PHPUNIT_LOG" 2>/dev/null | head -40 || true
    grep "Failed asserting\|Error:" "$PHPUNIT_LOG" 2>/dev/null | head -10 || true
  fi
  if [ "$PLAYWRIGHT_OK" -eq 0 ]; then
    echo ""
    echo "Playwright E2E failures:"
    grep -E "^\s+[0-9]+\)|Error:|Expected:|Received:" "$PLAYWRIGHT_LOG" 2>/dev/null | head -30 || true
  fi
  echo ""
  echo "Full logs: $PHPUNIT_LOG, $PLAYWRIGHT_LOG"
  echo ""
  exit 1
fi

echo "  All checks passed."
exit 0
