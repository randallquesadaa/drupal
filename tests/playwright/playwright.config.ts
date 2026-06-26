import { defineConfig, devices } from '@playwright/test';

// CAMBIAR: BASE_URL apunta al servidor que levanta el workflow e2e.yml
// vía "drush runserver". Si testeás contra una preview de PR o staging,
// cambiá esta variable de entorno en el workflow en vez de acá.
const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8080';

export default defineConfig({
  testDir: './specs',
  fullyParallel: true,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? [['html'], ['list']] : 'list',

  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    // CAMBIAR: descomentá si querés correr también en otros navegadores
    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },
    // {
    //   name: 'webkit',
    //   use: { ...devices['Desktop Safari'] },
    // },
  ],
});
