/*

Config database using these field in env:

    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=botaichinh
    DB_USERNAME=botaichinh
    DB_PASSWORD=123@123a
    DB_ADMIN=postgres

PostgreSQL user & database setup and Laravel migration guide

1. Connect to PostgreSQL as a superuser

    sudo psql -u postgres

2. Create a role/user with LOGIN and a secure password.

    CREATE USER botaichinh WITH PASSWORD '123@123a';qq\qq

3. Create the application database.

    CREATE DATABASE botaichinh;

4. Grant the role CONNECT to the database and appropriate privileges:
    - Database-level privileges (CONNECT, and optionally ALL).
    - Schema-level privileges (USAGE, CREATE or ALL on public schema).
    - Privileges on existing tables and sequences in the schema.
    - ALTER DEFAULT PRIVILEGES so that future tables and sequences are automatically granted to the role.

    GRANT CONNECT ON DATABASE botaichinh TO botaichinh;
    \c botaichinh
    GRANT ALL PRIVILEGES ON DATABASE botaichinh TO botaichinh;
    GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO botaichinh;

    ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT ALL PRIVILEGES ON TABLES TO botaichinh;

    GRANT ALL PRIVILEGES ON SCHEMA public TO botaichinh;

5. When inside psql you can switch to the new DB with \c <dbname> (or reconnect with psql -d).
6. Update your Laravel .env file with DB_CONNECTION=pgsql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD.
7. From the Laravel project root, run migrations and seeders:
    - php artisan migrate:refresh --seed
    - NOTE: migrate:refresh drops all tables and re-runs migrations; do NOT run this in production unless you intend to wipe the data.

PostgreSQL backup and restore

1. Setup rclone
    sudo apt install rclone -y

2. Config rclone
    rclone config
    n
    drive

3. Execute backup
    php artisan db:backup

4. Restore backup
    php artisan db:restore file/to/absolute/path/restore.sql --force