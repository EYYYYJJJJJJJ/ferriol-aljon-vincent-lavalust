/**
 * Isolated end-to-end Lab 6 HTTP checks. Requires Node 18+ and PHP with pdo_sqlite.
 * Run: node tests/lab6_api_test.mjs
 * Optional: PHP_BINARY=/path/to/php. No live services or databases are touched.
 */
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { existsSync } from 'node:fs';
import { mkdtemp, rm } from 'node:fs/promises';
import net from 'node:net';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { setTimeout as delay } from 'node:timers/promises';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const bundledPhp = path.join(root, '.local-stack', 'xampp', 'php', 'php.exe');
const php = process.env.PHP_BINARY || (existsSync(bundledPhp) ? bundledPhp : 'php');
const temporary = await mkdtemp(path.join(os.tmpdir(), 'lavalust-lab6-api-'));
const username = `lab6_test_${randomBytes(6).toString('hex')}`;
const password = `Test!${randomBytes(20).toString('base64url')}`;
const origin = 'http://localhost:5173';
const environment = {
  ...process.env,
  DB_DRIVER: 'sqlite',
  DB_SQLITE_PATH: path.join(temporary, 'api.sqlite'),
  AUTH_USERNAME: username,
  AUTH_PASSWORD: password,
  AUTH_EMAIL: `${username}@example.test`,
  APP_ENV: 'production',
  APP_KEY: randomBytes(32).toString('hex'),
  API_JWT_SECRET: randomBytes(32).toString('hex'),
  API_REFRESH_TOKEN_KEY: randomBytes(32).toString('hex'),
  API_ALLOWED_ORIGINS: origin,
  REQUIRE_MYSQL: 'false',
};
let server;
let output = '';
let count = 0;

function check(condition, message) {
  assert.ok(condition, message);
  count++;
}

async function unusedPort() {
  const socket = net.createServer();
  await new Promise((resolve, reject) => {
    socket.once('error', reject);
    socket.listen(0, '127.0.0.1', resolve);
  });
  const port = socket.address().port;
  await new Promise(resolve => socket.close(resolve));
  return port;
}

try {
  const init = spawnSync(php, ['scripts/init_lab6.php'], {
    cwd: root, env: environment, encoding: 'utf8', windowsHide: true,
  });
  assert.equal(init.status, 0, `Isolated database initialization failed: ${init.error || init.stderr || init.stdout}`);
  check(existsSync(environment.DB_SQLITE_PATH), 'Initializer must use the isolated database path');

  const port = await unusedPort();
  const base = `http://127.0.0.1:${port}/index.php/api`;
  server = spawn(php, ['-S', `127.0.0.1:${port}`, 'scripts/lab6_dev_router.php'], {
    cwd: root, env: environment, windowsHide: true, stdio: ['ignore', 'pipe', 'pipe'],
  });
  server.stdout.on('data', data => { output += data; });
  server.stderr.on('data', data => { output += data; });
  server.on('error', error => { output += String(error); });

  async function request(method, route, { token, body, raw, headers = {} } = {}) {
    const response = await fetch(base + route, {
      method,
      headers: {
        Accept: 'application/json', Origin: origin,
        ...(body !== undefined || raw !== undefined ? { 'Content-Type': 'application/json' } : {}),
        ...(token ? { Authorization: `Bearer ${token}` } : {}), ...headers,
      },
      body: raw !== undefined ? raw : body !== undefined ? JSON.stringify(body) : undefined,
      signal: AbortSignal.timeout(8000),
    });
    const text = await response.text();
    let json;
    try { json = text ? JSON.parse(text) : null; }
    catch { assert.fail(`${method} ${route} returned non-JSON (${response.status}): ${text.slice(0, 250)}`); }
    return { status: response.status, headers: response.headers, json };
  }

  let ready = false;
  for (let attempt = 0; attempt < 50; attempt++) {
    try {
      await fetch(base + '/products', { signal: AbortSignal.timeout(800) });
      ready = true;
      break;
    } catch { await delay(100); }
  }
  assert.ok(ready, `PHP HTTP server did not start: ${output.slice(-1500)}`);

  const product = {
    product_name: 'Notebook & Pen <Special> "A"',
    description: 'Plain text <script>alert("x")</script> & café\nSecond line',
    price: '149.95', quantity: 7,
  };

  for (const [method, route, body] of [
    ['GET', '/products'], ['GET', '/products/1'], ['POST', '/products', product],
    ['PUT', '/products/1', product], ['PATCH', '/products/1', { quantity: 3 }],
    ['DELETE', '/products/1'],
  ]) {
    const response = await request(method, route, { body });
    check(response.status === 401, `${method} ${route} must reject anonymous users with 401`);
  }
  check((await request('GET', '/auth/me')).status === 401, 'User profile requires authentication');
  check((await request('POST', '/auth/logout')).status === 401, 'Logout requires authentication');
  check((await request('POST', '/auth/login', { body: { username, password: 'incorrect' } })).status === 401,
    'Incorrect password rejected');
  check((await request('POST', '/auth/login', { raw: '{bad json' })).status === 400, 'Malformed JSON rejected');
  check((await request('POST', '/auth/login', { raw: '[]' })).status === 400, 'JSON arrays rejected');

  const login = await request('POST', '/auth/login', { body: { username, password } });
  check(login.status === 200, 'Valid seeded credentials accepted');
  check(typeof login.json.access_token === 'string' && typeof login.json.refresh_token === 'string', 'Both tokens returned');
  check(login.json.user?.username === username, 'Login returns the database user');
  check(Number(login.json.expires_in) > 0, 'Token expiry supplied');
  const token = login.json.access_token;
  const refreshToken = login.json.refresh_token;
  check((await request('GET', '/auth/me', { token })).json.user?.username === username, 'Authenticated profile returns user');
  check((await request('GET', '/products', { token: 'not-a-token' })).status === 401, 'Malformed bearer rejected');
  check((await request('GET', '/products', { token: refreshToken })).status === 401, 'Refresh tokens cannot authorize CRUD');

  const list = await request('GET', '/products', { token });
  check(list.status === 200 && Array.isArray(list.json.data), 'Authenticated list succeeds');
  const initialLength = list.json.data.length;

  const invalidProducts = [
    { ...product, product_name: '' },
    { ...product, product_name: 'x'.repeat(101) },
    { ...product, price: -1 },
    { ...product, price: 'abc' },
    { ...product, price: '100000000.00' },
    { ...product, quantity: -1 },
    { ...product, quantity: 1.5 },
    { ...product, quantity: 'abc' },
  ];
  for (const [index, body] of invalidProducts.entries()) {
    const invalid = await request('POST', '/products', { token, body });
    check(invalid.status === 422, `Invalid product ${index + 1} receives 422`);
    check(typeof invalid.json.error === 'string', `Invalid product ${index + 1} has readable error`);
  }
  check((await request('POST', '/products', { token, raw: '{bad json' })).status === 400, 'Malformed product JSON rejected');

  const created = await request('POST', '/products', { token, body: product });
  check(created.status === 201, `Product creation returns 201 (got ${created.status}: ${JSON.stringify(created.json)})`);
  const id = created.json.data?.id;
  check(Number.isInteger(Number(id)) && Number(id) > 0, 'Created product has an ID');
  const fetched = await request('GET', `/products/${id}`, { token });
  check(fetched.status === 200, 'Created product is retrievable');
  check(fetched.json.data.product_name === product.product_name, 'Product names are not HTML encoded in storage');
  check(fetched.json.data.description === product.description, 'Description special characters round-trip');
  check(Number(fetched.json.data.price) === 149.95 && Number(fetched.json.data.quantity) === 7, 'Price and quantity round-trip');
  check(Boolean(fetched.json.data.created_at), 'Creation timestamp supplied');

  const editedProduct = { ...product, product_name: 'Updated notebook', price: 299.5, quantity: 0 };
  const edited = await request('PUT', `/products/${id}`, { token, body: editedProduct });
  check(edited.status === 200 && edited.json.data.product_name === editedProduct.product_name, 'Full update succeeds');
  const patched = await request('PATCH', `/products/${id}`, { token, body: { quantity: 12 } });
  check(patched.status === 200 && Number(patched.json.data.quantity) === 12, 'Partial update succeeds');
  check(patched.json.data.product_name === editedProduct.product_name, 'Partial update preserves other fields');
  check((await request('PUT', `/products/${id}`, { token, body: { quantity: 5 } })).status === 422, 'PUT requires all fields');
  check((await request('GET', '/products/2147483647', { token })).status === 404, 'Missing product returns 404');
  check((await request('DELETE', '/products/2147483647', { token })).status === 404, 'Deleting missing product returns 404');
  const removed = await request('DELETE', `/products/${id}`, { token });
  check(removed.status === 200, 'Delete succeeds');
  check((await request('GET', `/products/${id}`, { token })).status === 404, 'Deleted product is not retrievable');
  check((await request('GET', '/products', { token })).json.data.length === initialLength, 'CRUD returns isolated database to original row count');

  const rotated = await request('POST', '/auth/refresh', { body: { refresh_token: refreshToken } });
  check(rotated.status === 200 && rotated.json.access_token !== token, 'Refresh rotates the access token');
  check(rotated.json.refresh_token !== refreshToken, 'Refresh rotates the refresh token');
  check((await request('POST', '/auth/refresh', { body: { refresh_token: refreshToken } })).status === 401,
    'Used refresh token cannot be replayed');
  check((await request('GET', '/products', { token })).status === 401, 'Rotation revokes previous access token');
  const rotatedToken = rotated.json.access_token;
  check((await request('GET', '/products', { token: rotatedToken })).status === 200, 'Rotated access token is usable');
  const logout = await request('POST', '/auth/logout', {
    token: rotatedToken, body: { refresh_token: rotated.json.refresh_token },
  });
  check(logout.status === 200, 'Logout succeeds');
  check((await request('GET', '/products', { token: rotatedToken })).status === 401, 'Logout revokes access immediately');
  check((await request('POST', '/auth/refresh', { body: { refresh_token: rotated.json.refresh_token } })).status === 401,
    'Logout revokes refresh token');

  const preflight = await request('OPTIONS', '/products', { headers: {
    'Access-Control-Request-Method': 'POST', 'Access-Control-Request-Headers': 'authorization,content-type',
  } });
  check(preflight.status === 204, 'CORS preflight succeeds without authentication');
  check(preflight.headers.get('access-control-allow-origin') === origin, 'Configured frontend origin is allowed');
  check(preflight.headers.get('access-control-allow-methods')?.includes('PATCH'), 'CORS allows required HTTP methods');
  const forbiddenOrigin = await request('OPTIONS', '/products', { headers: { Origin: 'https://untrusted.example' } });
  check(forbiddenOrigin.headers.get('access-control-allow-origin') !== 'https://untrusted.example', 'Unconfigured CORS origin not reflected');
  console.log(`PASS: ${count} Lab 6 isolated HTTP assertions (auth, validation, CRUD, token rotation, logout, CORS).`);
} catch (error) {
  console.error(`FAIL: ${error.message}`);
  if (output) console.error(output.slice(-3000));
  process.exitCode = 1;
} finally {
  if (server && server.exitCode === null) {
    server.kill();
    await Promise.race([new Promise(resolve => server.once('exit', resolve)), delay(2000)]);
  }
  // This directory was created above exclusively for this test run.
  await rm(temporary, { recursive: true, force: true, maxRetries: 3, retryDelay: 150 });
}
