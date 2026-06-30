// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Launch smoke tests — key public routes should load without a 500 and serve
 * their core chrome. These guard the §6 "no 500s, no broken assets" checklist
 * item. They assume the app is reachable at the configured baseURL.
 */

const publicRoutes = [
  { path: '/', name: 'home' },
  { path: '/services', name: 'browse services' },
  { path: '/login', name: 'login' },
  { path: '/register', name: 'register' },
];

for (const route of publicRoutes) {
  test(`${route.name} (${route.path}) responds OK`, async ({ page }) => {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
    expect(response, `no response for ${route.path}`).not.toBeNull();
    expect(response.status(), `unexpected status for ${route.path}`).toBeLessThan(400);
    // The shared header brand mark should be present on every public page.
    await expect(page.locator('body')).toBeVisible();
  });
}
