import { test, expect } from '@playwright/test';

// Estos son tests de EJEMPLO. CAMBIAR por los flujos reales de tu sitio
// (login, formulario de contacto, checkout, búsqueda, etc).

test('la home carga correctamente', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/.+/); // CAMBIAR: poné un regex/texto real de tu <title>
  // Verifica que no haya un error fatal de PHP visible en pantalla.
  await expect(page.locator('body')).not.toContainText('Fatal error');
});

test('la página de login existe y tiene el formulario esperado', async ({ page }) => {
  await page.goto('/user/login');
  await expect(page.locator('#edit-name')).toBeVisible();
  await expect(page.locator('#edit-pass')).toBeVisible();
});

test('un usuario puede loguearse', async ({ page }) => {
  // CAMBIAR: usuario/clave creados en el paso "drush site-install" del workflow.
  await page.goto('/user/login');
  await page.fill('#edit-name', 'admin');
  await page.fill('#edit-pass', 'admin');
  await page.click('#edit-submit');
  await expect(page.locator('body')).toContainText('admin');
});

test('no hay errores 404 en los enlaces principales del menú', async ({ page }) => {
  await page.goto('/');
  const links = await page.locator('nav a').all();
  for (const link of links.slice(0, 5)) { // CAMBIAR el límite si querés revisar más
    const href = await link.getAttribute('href');
    if (href && href.startsWith('/')) {
      const response = await page.request.get(href);
      expect(response.status(), `El link ${href} debería responder 200`).toBeLessThan(400);
    }
  }
});
