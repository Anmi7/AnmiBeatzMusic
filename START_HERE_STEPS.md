# Anmi Beatz – Start Here (Do These In Order)

Do each step once. If something fails, stop and fix it before the next step.

---

## Step 1: Set your database in `.env`

1. Open **`backend/.env`** in your editor.
2. Find the lines that start with `DB_`.
3. Make them look **exactly** like this (change the password to your real Postgres password):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=anmi_beatz
DB_USERNAME=postgres
DB_PASSWORD=your_actual_password_here
```

4. Save the file.

---

## Step 2: Create the database in pgAdmin

1. Open **pgAdmin**.
2. In the left sidebar, click your **PostgreSQL server** (e.g. "PostgreSQL 16") to connect.
3. Right‑click **Databases** → **Create** → **Database**.
4. In **Database** type: **anmi_beatz**
5. Click **Save**.

Done. You don’t need to run any SQL file if you do this.

---

## Step 3: Run migrations (create tables)

1. Open a terminal (PowerShell or Command Prompt).
2. Go to the backend folder and run migrations:

```bash
cd "d:\My Codes\AnmiBeatzMusic\backend"
php artisan migrate
```

3. When it asks "Do you want to run the migrations?" type **yes** and press Enter.
4. You should see "Migration table created" and "Migrated" messages. If you see an error about database connection, check Step 1 (and that Postgres is running).

---

## Step 4: Create storage link (for cover images)

In the **same** terminal (still in `backend`):

```bash
php artisan storage:link
```

You should see: "The [...] link has been created."

---

## Step 5: Set your admin token

1. Open **`backend/.env`** again.
2. Find the line: **APP_ADMIN_TOKEN=**
3. Set it to a secret only you know, for example:

```env
APP_ADMIN_TOKEN=MySecretAnmiToken2024
```

4. Save the file. You’ll type this same value when you open the admin page.

---

## Step 6: Start the backend

In the **same** terminal (in `backend`):

```bash
php artisan serve
```

Leave this running. You should see: "Server running on [http://localhost:8000]".

---

## Step 7: Start the frontend (new terminal)

1. Open a **second** terminal.
2. Run:

```bash
cd "d:\My Codes\AnmiBeatzMusic\frontend"
npm install
npm run dev
```

3. Leave this running. You should see something like: "Local: http://localhost:5173/".

---

## Step 8: Open the site

1. In your browser go to: **http://localhost:5173**
2. You should see the Anmi Beatz site (maybe with no tracks yet).

---

## Step 9: Open the admin (only you)

1. In the browser go to: **http://localhost:5173/admin**
2. Enter the **same** value you put in `APP_ADMIN_TOKEN` (e.g. `MySecretAnmiToken2024`).
3. Click **Continue**.
4. Add a song (title, artist, genre, cover image, release date) and submit.

---

## Quick recap

| Step | What you do |
|------|------------------|
| 1 | Set `DB_*` and `APP_ADMIN_TOKEN` in `backend/.env` |
| 2 | In pgAdmin: Create database **anmi_beatz** |
| 3 | In terminal: `cd backend` → `php artisan migrate` |
| 4 | In terminal: `php artisan storage:link` |
| 5 | (Already in .env) |
| 6 | Terminal 1: `php artisan serve` (keep running) |
| 7 | Terminal 2: `cd frontend` → `npm install` → `npm run dev` (keep running) |
| 8 | Browser: **http://localhost:5173** |
| 9 | Browser: **http://localhost:5173/admin** → enter token → add songs |

If something doesn’t work, say which step number and what you see (error message or screen).
