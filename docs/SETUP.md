# Checklist de configuración — qué cambiar para que esto funcione en TU Drupal

Marcá cada ítem a medida que lo resolvés. Todos los archivos tienen comentarios
`# CAMBIAR:` o `// CAMBIAR:` en los puntos exactos.

## 1. Estructura del repo

- [ ] **Definí dónde vive el docroot.** Drupal puede estar en:
  - raíz del repo (`/`)
  - `/web` (lo más común con `drupal/recommended-project`)
  - `/docroot` (común en Acquia/Pantheon)

  Buscá la variable `DRUPAL_ROOT` en `.github/workflows/ci.yml` y `e2e.yml`,
  y poné el valor correcto (por defecto está en `web`).

- [ ] **Confirmá el manager de paquetes JS** (npm, yarn o pnpm) si tu tema/
  módulo custom tiene `package.json`. Los workflows usan `npm` por defecto.

## 2. Versiones

- [ ] **Versión de PHP**: editá `php-version` en `ci.yml` y `e2e.yml`.
  Default: `8.3`. Si soportás varias versiones, descomentá la matrix que
  está comentada en `ci.yml`.
  > Si vas a usar `.tugboat/config.yml` con Drupal 11 + PHP 8.5, **alineá
  > esta versión** con la imagen `tugboatqa/php:8.5-apache-bookworm` para
  > que CI y los previews de Tugboat corran sobre lo mismo. La acción
  > `shivammathur/setup-php` suele soportar versiones nuevas de PHP poco
  > después de su release — si `8.5` todavía no aparece disponible para
  > vos, usá `8.4` mientras tanto en ambos lugares.

- [ ] **Versión de Drupal core**: no hay que fijarla en el workflow (la trae
  `composer.json`), pero revisá que tu `composer.json` raíz tenga el
  `minimum-stability` y `require` correctos.

- [ ] **Versión de Node** (para eslint/stylelint/playwright/backstop):
  editá `node-version` en los workflows. Default: `20`.

## 3. Base de datos / instalación del sitio

- [ ] En `ci.yml`, el servicio `mysql` usa usuario `drupal` / password `drupal`
  / base `drupal`. Si tu sitio necesita otro motor (PostgreSQL, SQLite),
  cambiá el bloque `services:` completo — hay un ejemplo comentado para
  PostgreSQL.

- [ ] **Perfil de instalación**: la línea
  `drush site-install YOUR_PROFILE` — reemplazá `YOUR_PROFILE` por tu
  perfil real (`standard`, `minimal`, o el nombre de tu perfil custom).

- [ ] Si tu sitio usa **Config Split** o configuración por entorno, ajustá
  el paso `drush config:import` agregando `--partial` o el flag que
  corresponda.

- [ ] **Settings.php para CI**: vas a necesitar un `settings.ci.php` (o
  variables de entorno) que apunte a la base de datos del servicio MySQL.
  Hay un ejemplo en `docs/settings.ci.php.example` — copialo a
  `web/sites/default/settings.ci.php` y referencialo desde tu
  `settings.php` con algo como:
  ```php
  if (getenv('CI')) {
    include __DIR__ . '/settings.ci.php';
  }
  ```

## 4. Lint y estándares

- [ ] `phpcs.xml`: la línea `<file>web/modules/custom</file>` y
  `<file>web/themes/custom</file> — ajustá las rutas si tu docroot no es
  `web/`, y agregá/quitá rutas según dónde esté tu código custom.

- [ ] `phpstan.neon`: mismo tema, ajustá `paths:` a tus módulos/temas
  custom. También revisá el `level:` (empieza en 5; subilo gradualmente,
  level 8-9 es muy estricto para Drupal).

- [ ] `.eslintrc.json` y `.stylelintrc.json`: si tu tema usa un framework
  CSS particular (SASS, Tailwind, etc.) puede que necesites plugins
  adicionales — están comentados los más comunes.

## 5. Secrets necesarios en GitHub

Configurá esto en **Settings → Secrets and variables → Actions → New
repository secret**:

| Secret | Para qué | Obligatorio |
|---|---|---|
| `CODECOV_TOKEN` | Subir cobertura de PHPUnit a Codecov | Opcional (recomendado) |
| `SONAR_TOKEN` | SonarCloud (solo si lo activás) | Opcional |
| `LHCI_GITHUB_APP_TOKEN` | Comentarios de Lighthouse CI en el PR | Opcional |
| `DEPLOY_SSH_KEY` / `DEPLOY_HOST` / `DEPLOY_USER` / `DEPLOY_PATH` | Solo si usás `deploy-ssh-generico.yml` | Solo si usás `deploy-ssh-generico.yml` |

> **El deploy ahora tiene 5 opciones** (SSH genérico, Acquia, Pantheon,
> Platform.sh, Lagoon), cada una en su propio archivo dentro de
> `.github/workflows/deploy-*.yml`. Elegí la que corresponde a tu hosting y
> borrá las otras cuatro. La lista completa de secrets para cada una está
> en **`docs/DEPLOY-OPTIONS.md`** — no la repetimos acá para no
> desincronizar la info.

Si tu hosting es Acquia, Pantheon, Platform.sh o Lagoon, **no uses**
`deploy-ssh-generico.yml`. Mirá `docs/DEPLOY-OPTIONS.md` para elegir el
correcto.

## 6. URLs para tests E2E, Lighthouse y BackstopJS

- [ ] `tests/playwright/playwright.config.ts`: variable `BASE_URL`
  (default `http://127.0.0.1:8080`, apuntando al server que levanta el
  mismo workflow con `drush runserver`).

- [ ] `.lighthouserc.json`: array `url` — poné la URL real que querés medir
  una vez deployado, o dejá la del servidor local de CI si solo querés
  medir contra una instalación limpia.

- [ ] `tests/backstop/backstop.json`: necesitás dos URLs: `referenceUrl`
  (tu sitio en producción/staging, la "verdad") y `url` (el ambiente que
  estás probando, ej. una preview de PR). La primera vez tenés que generar
  las imágenes de referencia corriendo `backstop reference` localmente y
  commitearlas en `tests/backstop/bitmaps_reference/`.

## 7. Umbrales (ajustables, no son obligatorios tal cual vienen)

- [ ] Cobertura mínima de PHPUnit en `ci.yml` (default: sin umbral duro,
  solo se reporta — descomentá el `fail_ci_if_error` de Codecov si querés
  que bloquee).
- [ ] Performance/Accesibilidad mínima en `.lighthouserc.json`
  (`minScore`, default 0.8 para performance y 0.9 para accesibilidad).
- [ ] Tolerancia de diff visual en `backstop.json`
  (`misMatchThreshold`, default 0.1%).

## 8. Triggers de los workflows

Por defecto:
- `ci.yml` corre en cada `push` y `pull_request`.
- `e2e.yml` y `quality.yml` corren en `pull_request` (para no gastar minutos
  en cada push a una rama de feature) — ajustable.
- El workflow de deploy que elijas (`deploy-*.yml`) corre solo cuando el
  workflow `CI` terminó con éxito en `main` (vía `workflow_run`).

Cambiá las ramas (`main`, `master`, `develop`) en el `on:` de cada archivo
según tu flujo de Git.

## 9. Primera corrida — qué esperar que falle

La primera vez que corras esto en un Drupal real, es normal que:
- PHPCS marque decenas/cientos de errores de estilo → corré
  `phpcs --standard=Drupal,DrupalPractice web/modules/custom --report=summary`
  localmente y arreglá con `phpcbf` lo automático.
- PHPStan tire muchos falsos positivos en level alto → bajá el level y subí
  gradualmente, o agregá excepciones puntuales con `// @phpstan-ignore-line`.
- BackstopJS no tenga imágenes de referencia → generálas localmente primero.
- Lighthouse falle por performance en un sitio recién instalado sin caché →
  revisá que `settings.ci.php` tenga el caché de Drupal activado igual que
  en producción.

No es necesario que todo pase en verde el primer día. La idea es subir el
piso gradualmente.
