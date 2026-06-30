// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Partysmith end-to-end tests.
 *
 * The application is served by a virtual host (see CLAUDE.md). Override the
 * target with BASE_URL when running against a different host, e.g.
 *   BASE_URL=http://localhost:8888 npm run test:e2e
 *
 * Chromium is pre-installed in this environment; PLAYWRIGHT_BROWSERS_PATH
 * already points Playwright at it, so no `playwright install` is needed.
 */
const baseURL = process.env.BASE_URL || 'http://partyplanner.test';

module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
