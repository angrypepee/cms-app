#!/usr/bin/env bash
cd "$(dirname "$0")"
docker compose down
echo "App stopped."
sleep 2
