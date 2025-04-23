Okay, let's create the `infrastructure_devops_details.md` file to provide concrete starting points for Docker, CI/CD, Monitoring, Logging, and Backups.

--- START OF FILE infrastructure_devops_details.md ---

**Huberman App: Infrastructure & DevOps Specifics**

**1. Introduction**

This document provides more specific details and initial configurations for the infrastructure and DevOps components outlined in the project plan and backend TODO list. It includes a concrete example for `docker-compose.yml`, a basic CI/CD pipeline structure using GitHub Actions, and initial recommendations for monitoring, logging, and backup configurations. These are starting points and should be refined based on specific hosting choices (Forge/Vapor) and evolving project needs.

**2. Local Development Environment (`docker-compose.yml`)**

This configuration provides the necessary services for local backend development and testing. Place this file in the project root.

```yaml
# docker-compose.yml
version: '3.8'

services:
  # PHP Service (Application Server)
  app:
    build:
      context: .
      dockerfile: Dockerfile # Assumes a Dockerfile exists for PHP setup
      args:
        UID: "${UID:-1000}" # Pass host user ID for permissions
        GID: "${GID:-1000}" # Pass host group ID for permissions
    container_name: huberman_app_php
    restart: unless-stopped
    tty: true
    environment:
      SERVICE_NAME: app
      SERVICE_TAGS: dev
      # Pass environment variables from .env file
      PHP_IDE_CONFIG: "serverName=Docker" # Example for Xdebug integration
      XDEBUG_MODE: "${XDEBUG_MODE:-off}" # Control Xdebug mode via .env
      XDEBUG_CONFIG: "client_host=host.docker.internal" # Xdebug config for Docker Desktop
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html # Mount application code
      # Persist bash history (optional)
      # - ./docker-data/bashhistory:/root/.bash_history_docker
    networks:
      - huberman_app_network
    depends_on:
      - db
      - redis

  # Web Server (Nginx)
  webserver:
    image: nginx:alpine
    container_name: huberman_app_nginx
    restart: unless-stopped
    tty: true
    ports:
      - "${APP_PORT:-8000}:80" # Expose app port from .env or default 8000
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/ # Mount custom Nginx config
    networks:
      - huberman_app_network
    depends_on:
      - app

  # Database Server (PostgreSQL)
  db:
    image: postgres:16-alpine
    container_name: huberman_app_db
    restart: unless-stopped
    tty: true
    ports:
      - "${DB_PORT:-5432}:5432" # Expose DB port from .env or default 5432
    environment:
      POSTGRES_DB: "${DB_DATABASE:-huberman_db}"
      POSTGRES_USER: "${DB_USERNAME:-sail}" # Use Sail defaults or .env values
      POSTGRES_PASSWORD: "${DB_PASSWORD:-password}"
      POSTGRES_HOST_AUTH_METHOD: "trust" # For easy local connections, change if needed
    volumes:
      - huberman_db_data:/var/lib/postgresql/data # Persistent DB data
      # Optional: Mount init scripts
      # - ./docker/db/init:/docker-entrypoint-initdb.d
    networks:
      - huberman_app_network
    healthcheck: # Basic healthcheck
      test: ["CMD", "pg_isready", "-U", "${DB_USERNAME:-sail}", "-d", "${DB_DATABASE:-huberman_db}"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Cache Server (Redis)
  redis:
    image: redis:7-alpine
    container_name: huberman_app_redis
    restart: unless-stopped
    ports:
      - "${REDIS_PORT:-6379}:6379" # Expose Redis port from .env or default 6379
    volumes:
      - huberman_redis_data:/data # Persistent Redis data
    networks:
      - huberman_app_network
    healthcheck: # Basic healthcheck
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

# Define Networks
networks:
  huberman_app_network:
    driver: bridge

# Define Volumes
volumes:
  huberman_db_data:
    driver: local
  huberman_redis_data:
    driver: local

```

**Notes on `docker-compose.yml`:**

*   **`Dockerfile`:** This setup assumes a `Dockerfile` in the root for the `app` service to handle PHP setup, installing extensions (like `pdo_pgsql`, `pgsql`, `redis`, `bcmath`, `pcntl`, `gd`), composer installation, and potentially setting user permissions correctly. You'll need to create this file.
*   **`.env` Integration:** It relies heavily on your project's `.env` file for configuration (ports, DB credentials, etc.). Ensure your `.env` file is present and correctly configured. Use `.env.example` as a template.
*   **UID/GID:** Passing host user/group IDs helps avoid permission issues with mounted volumes, especially file creation by PHP.
*   **Nginx Config:** Requires a custom Nginx configuration file (e.g., `docker/nginx/conf.d/app.conf`) to proxy requests to the `app` service (PHP-FPM on port 9000).
*   **Xdebug:** Includes basic Xdebug configuration for easier debugging. Control via `XDEBUG_MODE` in `.env`.
*   **Healthchecks:** Basic checks added to ensure services are responsive.

**3. CI/CD Pipeline Definition (GitHub Actions Example)**

This provides a basic pipeline structure for running tests and static analysis on pushes/PRs to `main` and `develop`. Place this in `.github/workflows/ci.yml`.

```yaml
# .github/workflows/ci.yml
name: Laravel CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  build_and_test:
    runs-on: ubuntu-latest

    services: # Start dependent services for testing
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_DB: huberman_test_db
          POSTGRES_USER: sail
          POSTGRES_PASSWORD: password
          POSTGRES_HOST_AUTH_METHOD: trust # Easiest for CI connection
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
      redis:
        image: redis:7-alpine
        ports:
          - 6379:6379
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
    - name: Checkout code
      uses: actions/checkout@v4

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2' # Match project version
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, pgsql, pdo_pgsql, redis, bcmath, gd # Match required extensions
        tools: composer:v2
        coverage: none # Set to pcov or xdebug if generating coverage reports

    - name: Setup Node.js (Optional, if needed for frontend assets/tools)
      uses: actions/setup-node@v4
      with:
        node-version: '20.x' # Match project version
        cache: 'npm'

    - name: Get Composer Cache Directory
      id: composer-cache
      run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

    - name: Cache Composer Dependencies
      uses: actions/cache@v4
      with:
        path: ${{ steps.composer-cache.outputs.dir }}
        key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
        restore-keys: |
          ${{ runner.os }}-composer-

    - name: Install Composer Dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader

    - name: Prepare Laravel Environment
      run: |
        cp .env.example .env
        sed -i 's/DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/g' .env # Ensure localhost for service access
        sed -i 's/DB_PORT=5432/DB_PORT=5432/g' .env
        sed -i 's/DB_DATABASE=laravel/DB_DATABASE=huberman_test_db/g' .env
        sed -i 's/DB_USERNAME=root/DB_USERNAME=sail/g' .env
        sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/g' .env
        sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=127.0.0.1/g' .env
        sed -i 's/REDIS_PORT=6379/REDIS_PORT=6379/g' .env
        php artisan key:generate

    - name: Wait for Services (Postgres)
      run: |
        echo "Waiting for PostgreSQL to start..."
        until pg_isready -h 127.0.0.1 -p 5432 -U sail; do
          sleep 1
        done
        echo "PostgreSQL started!"

    - name: Run Database Migrations
      run: php artisan migrate --force # Use --force to run without prompt in CI

    - name: Run Linters (Pint)
      run: ./vendor/bin/pint --test

    - name: Run Static Analysis (PHPStan)
      run: ./vendor/bin/phpstan analyse --memory-limit=2G

    - name: Run Tests (PHPUnit)
      run: php artisan test

    # Optional: NPM Steps if needed for backend assets
    # - name: Install NPM Dependencies
    #   run: npm ci
    # - name: Build Assets
    #   run: npm run build
```

**Notes on `ci.yml`:**

*   **Services:** Uses GitHub Actions `services` to run PostgreSQL and Redis alongside the main job container, making them accessible on `127.0.0.1`.
*   **Environment:** Copies `.env.example` and modifies it specifically for the CI environment database/redis services.
*   **Dependencies:** Caches Composer dependencies for faster runs.
*   **Tests:** Runs Pint, PHPStan, and PHPUnit. Add other checks as needed.
*   **Deployment:** Deployment steps (Forge, Vapor, etc.) are *not* included here but would typically be added as separate jobs triggered manually or on merges to `main`.

**4. Monitoring, Logging & Backup Specifics (Initial Recommendations)**

These are starting points, adjust based on provider capabilities (Forge/Vapor often provide defaults) and application sensitivity.

*   **Monitoring:**
    *   **Key Metrics:**
        *   System: CPU (<80%), Memory (<85%), Disk Space (<90% full).
        *   Application: 5xx Error Rate (<1%), Average API Response Time (<500ms), Requests Per Minute.
        *   Queue: `jobs` table count (< 100 pending for > 5 mins), `failed_jobs` count (> 0).
    *   **Tools:** Leverage Forge/Vapor built-in monitoring. Consider Sentry (or Flare for Laravel) for error tracking & basic performance monitoring. For more depth, look at Datadog or New Relic later.

*   **Logging:**
    *   **Level:** Production: `WARNING` or `ERROR`. Staging/Dev: `DEBUG`.
    *   **Channel (Laravel `config/logging.php`):**
        *   Use the `stack` channel.
        *   Include `daily` file logging (`path/to/storage/logs/laravel.log`).
        *   Integrate a log aggregation service (if using Forge/Vapor check their recommendations, otherwise Papertrail, Logtail, Datadog Logs). Configure a `syslog` or dedicated driver pointing to the service.
    *   **Format:** Configure the `stack` channel or aggregation channel to use a `Monolog\Formatter\JsonFormatter` for structured logging.
    *   **Key Events to Log (Contextual):** User registration/login failures, password resets, subscription webhook received (start/end/status), subscription state changes, job processing start/end/failure, critical errors caught in exception handler, manual logging for complex workflow steps. **AVOID LOGGING SENSITIVE DATA.**

*   **Alerting (Initial Critical Alerts):**
    *   Configure via monitoring tool (Forge/Vapor/Datadog/Sentry):
        *   5xx Error Rate > 2% over 5 minutes.
        *   Server/Instance Unreachable.
        *   CPU or Memory > 90% for 5 minutes.
        *   Disk Space > 95% full.
        *   Failed Jobs Count increases significantly or > X total.
        *   Queue length grows consistently without processing.
        *   Sentry/Flare reports a spike in new or existing errors.

*   **Backups (Database - PostgreSQL):**
    *   **Mechanism:** **Strongly recommend** using the managed database provider's automated snapshot feature (e.g., AWS RDS, Google Cloud SQL, DigitalOcean Managed DB).
    *   **Frequency:** Daily automated snapshots.
    *   **Retention:** Start with **7 days**. Evaluate if longer retention (e.g., 14-30 days) is needed based on recovery requirements.
    *   **Verification:** **Schedule and perform quarterly test restores** of a recent backup to a temporary database instance to confirm integrity and practice the recovery procedure. Document the process.
    *   **Location:** Ensure backups are stored geographically separate (usually handled by cloud providers).

**5. Conclusion**

These concrete configurations provide a solid starting point for the Huberman App's infrastructure and DevOps practices. They establish a consistent local development environment, automate basic testing and validation via CI, and outline initial strategies for observing and protecting the application in deployed environments. Remember to version control `docker-compose.yml`, `Dockerfile`, `nginx.conf`, and `.github/workflows/ci.yml`, and adapt these configurations as the project evolves and specific hosting choices are finalized.
