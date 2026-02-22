# Laravel Environment Template
# Copy this to backend/.env and update with your values

APP_NAME="Anmi Beatz"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ========== DATABASE CONFIGURATION ==========
# Choose ONE database setup below and uncomment it

# ----- Option 1: SQLite (Easiest for Development) -----
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/frontend-folder/database/database.sqlite

# ----- Option 2: MySQL -----
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=anmi_beatz
# DB_USERNAME=root
# DB_PASSWORD=your_mysql_password

# ----- Option 3: PostgreSQL -----
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=anmi_beatz
# DB_USERNAME=postgres
# DB_PASSWORD=your_postgres_password

# ========== CACHE, SESSION & QUEUE ==========
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ========== MAIL CONFIGURATION ==========
MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# ========== AWS CONFIGURATION (Optional) ==========
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINTS=false

# ========== VITE (Frontend Build Tool) ==========
VITE_API_URL=http://localhost:8000/api
