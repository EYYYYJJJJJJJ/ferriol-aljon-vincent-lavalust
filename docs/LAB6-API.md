# Laboratory Exercise 6: LavaLust API

- Student: Aljon Vincent E. Ferriol
- Student ID: MCC2024-00052
- Email: ferriol.aljone@minsu.edu.ph

This backend exposes the authenticated product API used by the separate Vue frontend. It preserves the server-rendered Labs 1–5 routes.

## Submission links

- Backend repository: https://github.com/EYYYYJJJJJJJ/ferriol-aljon-vincent-lavalust
- Frontend repository: https://github.com/EYYYYJJJJJJJ/ferriol-aljon-vincent-lab6-frontend
- Render API URL: https://ferriol-aljon-vincent.onrender.com/index.php/api
- Frontend URL: https://ferriol-aljon-vincent-lab6.onrender.com

## Endpoints

- `POST /index.php/api/auth/login`
- `POST /index.php/api/auth/refresh`
- `GET /index.php/api/auth/me`
- `POST /index.php/api/auth/logout`
- `GET|POST /index.php/api/products`
- `GET|PUT|PATCH|DELETE /index.php/api/products/{id}`

All product endpoints require an access token in `Authorization: Bearer <token>`. Passwords are hashed, refresh tokens are hashed before storage, access tokens are tied to a revocable server-side session, and all SQL values use prepared statements.

## Required production environment

Configure `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`, and `DB_SSL_CA` for Aiven MySQL. Set `DB_DRIVER=mysql` and `REQUIRE_MYSQL=true` so a deployment cannot silently use the Lab 5 SQLite fallback. Also configure:

```text
APP_ENV=production
APP_KEY=<random value of at least 32 characters>
JWT_SECRET=<random value of at least 32 characters>
REFRESH_TOKEN_KEY=<different random value of at least 32 characters>
AUTH_USERNAME=aljon
AUTH_PASSWORD=<deployment secret>
AUTH_EMAIL=ferriol.aljone@minsu.edu.ph
API_ALLOWED_ORIGINS=https://YOUR-FRONTEND.onrender.com
```

Real secrets belong in Render environment variables and are intentionally absent from Git.

## Verification

From the repository root:

```text
.local-stack/xampp/php/php.exe tests/lab6_migrations_test.php
node tests/lab6_api_test.mjs
```

The tests use isolated temporary SQLite databases and never modify Aiven or the live application. They cover forward migrations and data preservation, authentication on every CRUD operation, validation, create/read/update/delete, refresh-token rotation and replay prevention, immediate logout revocation, and CORS.
