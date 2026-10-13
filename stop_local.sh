#!/usr/bin/env bash

echo "Stopping PHP built-in web servers on ports 8080 and 8081..."

PID_8080=$(lsof -ti :8080 2>/dev/null)
if [ -n "$PID_8080" ]; then
    echo "Stopping process on port 8080 (PID $PID_8080)..."
    kill -9 $PID_8080 2>/dev/null || true
fi

PID_8081=$(lsof -ti :8081 2>/dev/null)
if [ -n "$PID_8081" ]; then
    echo "Stopping process on port 8081 (PID $PID_8081)..."
    kill -9 $PID_8081 2>/dev/null || true
fi

PID_8082=$(lsof -ti :8082 2>/dev/null)
if [ -n "$PID_8082" ]; then
    echo "Stopping process on port 8082 (PID $PID_8082)..."
    kill -9 $PID_8082 2>/dev/null || true
fi

echo "Done."
