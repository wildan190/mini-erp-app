#!/bin/bash
# Docker test runner - ensures QUEUE_CONNECTION=sync before Laravel bootstraps
# The env vars must be exported BEFORE PHP starts, not passed via exec env,
# because PHP reads $_SERVER at startup from the process environment.

export QUEUE_CONNECTION=sync
export SESSION_DRIVER=array
export CACHE_STORE=array
export DB_CONNECTION=sqlite
export DB_DATABASE=":memory:"
export MAIL_MAILER=array
export PYTHON_EXECUTABLE=python3
export APP_ENV=testing

cd /app

php artisan test "$@"
