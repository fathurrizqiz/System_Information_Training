import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/Playwright', // Pastikan folder ini sudah sesuai
  fullyParallel: true,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:8000',
    trace: 'on-first-retry',
  },
  // ... sisanya
});