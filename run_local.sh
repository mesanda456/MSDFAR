#!/usr/bin/env bash

# Resolve script directory
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
if [ -d "$DIR/dfar_ms" ]; then
    cd "$DIR/dfar_ms"
else
    cd "$DIR"
fi

echo "========================================================"
echo "          Starting DFAR Application Locally (macOS)"
echo "========================================================"

# 1. Check PHP
if ! command -v php >/dev/null 2>&1; then
    echo "[ERROR] PHP is not installed or not in PATH."
    exit 1
fi
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "[OK] Found PHP $PHP_VERSION"

# 2. Check MySQL
if nc -z 127.0.0.1 3306 2>/dev/null || nc -z localhost 3306 2>/dev/null; then
    echo "[OK] MySQL is running on port 3306."
else
    echo "[WARNING] MySQL does not appear to be reachable on port 3306."
    echo "Please ensure MySQL service is running."
fi

# 3. Stop any existing listeners on 8080 and 8081
PID_8080=$(lsof -ti :8080 2>/dev/null)
if [ -n "$PID_8080" ]; then
    echo "Stopping existing process on port 8080 (PID $PID_8080)..."
    kill -9 $PID_8080 2>/dev/null || true
fi

PID_8081=$(lsof -ti :8081 2>/dev/null)
if [ -n "$PID_8081" ]; then
    echo "Stopping existing process on port 8081 (PID $PID_8081)..."
    kill -9 $PID_8081 2>/dev/null || true
fi

PID_8082=$(lsof -ti :8082 2>/dev/null)
if [ -n "$PID_8082" ]; then
    echo "Stopping existing process on port 8082 (PID $PID_8082)..."
    kill -9 $PID_8082 2>/dev/null || true
fi

# 4. Create log directory
mkdir -p runtime/logs

# 5. Start Backend Server on Port 8080
echo "Starting Backend on http://localhost:8080 ..."
nohup php -d opcache.enable=0 -S 127.0.0.1:8080 -t backend/web backend/web/router.php > runtime/logs/backend.log 2>&1 &
BACKEND_PID=$!
echo "[OK] Backend started (PID $BACKEND_PID)"

# 6. Start Frontend Server on Port 8081
echo "Starting Frontend on http://localhost:8081 ..."
nohup php -d opcache.enable=0 -S 127.0.0.1:8081 -t frontend/web frontend/web/router.php > runtime/logs/frontend.log 2>&1 &
FRONTEND_PID=$!
echo "[OK] Frontend started (PID $FRONTEND_PID)"

# 7. Start Documentation Portal on Port 8082
DOCS_DIR="$DIR/docs"
if [ ! -d "$DOCS_DIR" ] && [ -d "$DIR/../docs" ]; then
    DOCS_DIR="$DIR/../docs"
fi
if [ -d "$DOCS_DIR" ]; then
    echo "Starting Documentation Portal on http://localhost:8082 ..."
    nohup php -d opcache.enable=0 -S 127.0.0.1:8082 -t "$DOCS_DIR" > runtime/logs/docs.log 2>&1 &
    DOCS_PID=$!
    echo "[OK] Documentation Portal started (PID $DOCS_PID)"
fi

sleep 1

# Check if servers are running
if kill -0 $BACKEND_PID 2>/dev/null && kill -0 $FRONTEND_PID 2>/dev/null; then
    echo ""
    echo "========================================================"
    echo "  DFAR Application is now running!"
    echo "  - Backend:       http://localhost:8080"
    echo "  - Frontend:      http://localhost:8081"
    echo "  - Documentation: http://localhost:8082"
    echo "========================================================"
    echo ""
    echo "To stop the servers at any time, run: ./stop_local.sh"
else
    echo "[ERROR] One or more servers failed to start. Check runtime/logs/ for details."
fi
