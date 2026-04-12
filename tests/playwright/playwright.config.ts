import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  baseURL: process.env.ELGG_BASE_URL || 'http://elgg',
  timeout: 30000,
  use: {
    ignoreHTTPSErrors: true,
  },
  workers: 1,
  projects: [{ name: 'chromium', use: { browserName: 'chromium' } }],
});
