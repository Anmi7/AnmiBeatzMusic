# Admin Upload & Search/Filter Setup

## Backend (Laravel)

### 1. Set admin token
In `backend/.env` add:
```env
APP_ADMIN_TOKEN=your-secret-token-here
```
Use a long random string (e.g. from `php artisan tinker` → `Str::random(48)`). The frontend will ask for this token to access the admin form.

### 2. Run migration (nullable audio fields)
```bash
cd backend
php artisan migrate
```
If you use SQLite and see an error about `change()`, install: `composer require doctrine/dbal` then run `migrate` again.

### 3. Storage link (for cover uploads)
Uploaded cover images are stored in `storage/app/public/covers`. Create the public link:
```bash
cd backend
php artisan storage:link
```
This creates `public/storage` → `storage/app/public` so URLs like `/storage/covers/xxx.jpg` work.

### 4. CORS
If the frontend runs on a different port (e.g. 5173), ensure `config/cors.php` allows it. Laravel’s default `paths` often include `api/*` and the front origin.

---

## Frontend (Vue)

### 1. Install dependencies
```bash
cd frontend
npm install
```
This installs `vue-router` (added for `/admin`).

### 2. API URL (optional)
If the API is not at `http://localhost:8000`, set in `frontend/.env`:
```env
VITE_API_URL=http://localhost:8000/api
```

### 3. Use admin
1. Open the site and click **Admin** in the nav.
2. Enter the same value as `APP_ADMIN_TOKEN`.
3. Use the form to add a song: title, artist, genre, cover image, release date. Audio can be added later (edit in DB or future admin edit screen).

---

## Search & filter

- **Search**: Text input filters by song title and artist (client-side).
- **Genre**: Dropdown filters by genre.
- **Release date**: “From” and “To” date inputs filter by `release_date`.

Backend `GET /api/tracks` also supports query params for server-side filtering: `title`, `artist`, `genre`, `release_date_from`, `release_date_to`. The current frontend does all filtering in the client after loading all tracks; you can later switch to using these params for large catalogs.
