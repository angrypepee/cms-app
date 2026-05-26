#!/usr/bin/env bash
# Double-click this file in Finder to start the app.
cd "$(dirname "$0")"

if ! command -v docker >/dev/null 2>&1; then
    osascript -e 'display dialog "Docker Desktop is not installed. Download it from https://www.docker.com/products/docker-desktop/" buttons {"OK"} default button 1'
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "Starting Docker Desktop..."
    open -a Docker
    echo "Waiting for Docker to be ready..."
    while ! docker info >/dev/null 2>&1; do sleep 2; done
fi

echo "Building & starting CMS App..."
docker compose up -d --build

echo "Waiting for app..."
sleep 5
open "http://localhost:8080"
echo "App is running at http://localhost:8080"
echo "Press any key to close this window."
read -n 1
