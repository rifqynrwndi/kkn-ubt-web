#!/bin/bash
#
# WAR Load Test — ~200 concurrent users
# Usage: ./scripts/load-test-war.sh [base_url] [concurrent] [duration_seconds]
#
# Requires: curl, bc (basic calculator)
# Install on Ubuntu: sudo apt install -y curl bc
#
# Example:
#   ./scripts/load-test-war.sh https://kknubt.id 200 30
#   ./scripts/load-test-war.sh http://127.0.0.1:8000 50 10

set -euo pipefail

BASE_URL="${1:-https://kknubt.id}"
CONCURRENT="${2:-200}"
DURATION="${3:-30}"
RESULTS_DIR="/tmp/war-load-test-$(date +%s)"

mkdir -p "$RESULTS_DIR"

echo "=== KKN UBT WAR Load Test ==="
echo "Target:     $BASE_URL"
echo "Concurrent: $CONCURRENT"
echo "Duration:   ${DURATION}s"
echo "Results:    $RESULTS_DIR"
echo ""

# --- Test 1: Homepage (read-only) ---
echo "--- Test 1: Homepage GET ---"
echo "Sending $CONCURRENT requests to $BASE_URL/"

start_time=$(date +%s%N)
for i in $(seq 1 $CONCURRENT); do
    curl -s -o /dev/null -w "%{http_code} %{time_total}\n" \
        --connect-timeout 5 --max-time 10 \
        "$BASE_URL/" >> "$RESULTS_DIR/homepage.txt" &
done
wait
end_time=$(date +%s%N)
elapsed_ms=$(( (end_time - start_time) / 1000000 ))

ok=$(awk '$1 == 200' "$RESULTS_DIR/homepage.txt" | wc -l)
err=$(awk '$1 != 200' "$RESULTS_DIR/homepage.txt" | wc -l)
avg_time=$(awk '{sum += $2; n++} END {printf "%.3f", sum/n}' "$RESULTS_DIR/homepage.txt")

echo "  Results: $ok OK, $err errors, avg ${avg_time}s, total ${elapsed_ms}ms"
echo ""

# --- Test 2: Login page (read-only) ---
echo "--- Test 2: Login page GET ---"
echo "Sending $CONCURRENT requests to $BASE_URL/login"

start_time=$(date +%s%N)
for i in $(seq 1 $CONCURRENT); do
    curl -s -o /dev/null -w "%{http_code} %{time_total}\n" \
        --connect-timeout 5 --max-time 10 \
        "$BASE_URL/login" >> "$RESULTS_DIR/login.txt" &
done
wait
end_time=$(date +%s%N)
elapsed_ms=$(( (end_time - start_time) / 1000000 ))

ok=$(awk '$1 == 200' "$RESULTS_DIR/login.txt" | wc -l)
err=$(awk '$1 != 200' "$RESULTS_DIR/login.txt" | wc -l)
avg_time=$(awk '{sum += $2; n++} END {printf "%.3f", sum/n}' "$RESULTS_DIR/login.txt")

echo "  Results: $ok OK, $err errors, avg ${avg_time}s, total ${elapsed_ms}ms"
echo ""

# --- Test 3: WAR routes (should redirect unauthenticated) ---
echo "--- Test 3: WAR routes (unauthenticated) ---"
echo "Sending $CONCURRENT requests to $BASE_URL/war"

start_time=$(date +%s%N)
for i in $(seq 1 $CONCURRENT); do
    curl -s -o /dev/null -w "%{http_code} %{time_total}\n" \
        --connect-timeout 5 --max-time 10 \
        "$BASE_URL/war" >> "$RESULTS_DIR/war.txt" &
done
wait
end_time=$(date +%s%N)
elapsed_ms=$(( (end_time - start_time) / 1000000 ))

ok=$(awk '$1 == 302' "$RESULTS_DIR/war.txt" | wc -l)
err=$(awk '$1 != 302' "$RESULTS_DIR/war.txt" | wc -l)
avg_time=$(awk '{sum += $2; n++} END {printf "%.3f", sum/n}' "$RESULTS_DIR/war.txt")

echo "  Results: $ok redirected (expected), $err errors, avg ${avg_time}s, total ${elapsed_ms}ms"
echo ""

# --- Test 4: Sustained load (mixed endpoints) ---
echo "--- Test 4: Sustained mixed load for ${DURATION}s ---"
echo "Sending concurrent requests to multiple endpoints..."

end=$((SECONDS + DURATION))
request_count=0

while [ $SECONDS -lt $end ]; do
    # Round-robin through endpoints
    case $((request_count % 4)) in
        0) endpoint="/" ;;
        1) endpoint="/login" ;;
        2) endpoint="/war" ;;
        3) endpoint="/css/custom.css" ;;
    esac

    curl -s -o /dev/null -w "%{http_code} %{time_total}\n" \
        --connect-timeout 5 --max-time 10 \
        "$BASE_URL$endpoint" >> "$RESULTS_DIR/sustained.txt" &

    request_count=$((request_count + 1))

    # Limit concurrency
    if (( request_count % CONCURRENT == 0 )); then
        wait
    fi
done
wait

ok=$(awk '$1 == 200 || $1 == 302' "$RESULTS_DIR/sustained.txt" | wc -l)
err=$(awk '$1 != 200 && $1 != 302' "$RESULTS_DIR/sustained.txt" | wc -l)
total=$(wc -l < "$RESULTS_DIR/sustained.txt")
avg_time=$(awk '{sum += $2; n++} END {printf "%.3f", sum/n}' "$RESULTS_DIR/sustained.txt")
rps=$(echo "scale=1; $total / $DURATION" | bc)

echo "  Total requests: $total"
echo "  OK: $ok, Errors: $err"
echo "  Avg response time: ${avg_time}s"
echo "  Requests/sec: $rps"
echo ""

# --- Summary ---
echo "=== Summary ==="
echo "All test results saved to: $RESULTS_DIR"
echo ""
echo "Raw data files:"
ls -la "$RESULTS_DIR/"
echo ""

if [ -f "$RESULTS_DIR/sustained.txt" ]; then
    echo "Response time distribution (sustained test):"
    awk '{print $2}' "$RESULTS_DIR/sustained.txt" | sort -n | awk '
    BEGIN { p50=0; p95=0; p99=0 }
    NR==int(NR*0.50) { p50=$1 }
    NR==int(NR*0.95) { p95=$1 }
    NR==int(NR*0.99) { p99=$1 }
    END { printf "  P50: %ss\n  P95: %ss\n  P99: %ss\n", p50, p95, p99 }
    '
fi
