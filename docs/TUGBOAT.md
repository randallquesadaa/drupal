# Tugboat — Previews automáticos por Pull Request

## Qué es y por qué importa en el pipeline

Todo lo que armamos hasta ahora (lint, tests, Lighthouse, BackstopJS) corre
**contra código**, en un runner efímero de GitHub Actions. Nadie del equipo
ve el sitio funcionando. Tugboat resuelve exactamente ese hueco: por cada
Pull Request, levanta una copia completa y funcional del sitio Drupal
(con su propia base de datos) en una URL pública temporal, y la destruye
cuando el PR se cierra.

Esto es lo que falta para que un diseñador, un editor de contenido o un
Product Manager puedan **ver y tocar** el cambio antes de aprobarlo, sin
tener que levantar nada en su máquina.

## Qué incluye este paquete

- `.tugboat/config.yml` — configuración completa para **Drupal 11 + PHP 8.5**
  (servicios PHP/Apache + MySQL, instalación automática del sitio, y una
  etapa `online` que corre tests automatizados contra el preview ya
  levantado).
- `.tugboat/settings.tugboat.php` — settings específicos del entorno
  Tugboat (base de datos, trusted hosts, hash salt, etc).

## Paso a paso para activarlo

1. **Creá una cuenta** en [dashboard.tugboatqa.com](https://dashboard.tugboatqa.com).
   Tugboat tiene un tier gratuito con límites de previews simultáneos —
   revisá los límites actuales en su página de pricing, porque cambian
   con el tiempo.

2. **Conectá tu repositorio**: Add Project → elegí GitHub → autorizá el
   GitHub App de Tugboat → seleccioná el repo de tu sitio Drupal.

3. **Confirmá que `.tugboat/config.yml` esté en la raíz del repo** (no
   dentro de `web/`, ni de ninguna subcarpeta — siempre en la raíz).

4. **Incluí el settings file** desde tu `settings.php` real. Al final de
   tu `web/sites/default/settings.php`, agregá (si no lo tenés ya):
   ```php
   if (getenv('TUGBOAT_PREVIEW_ID')) {
     include __DIR__ . '/settings.tugboat.php';
   }
   ```
   *(El propio `config.yml` de este paquete también intenta agregar esta
   línea automáticamente si no la encuentra — pero es más prolijo
   tenerla ya en tu repo.)*

5. **Hacé un Pull Request de prueba.** Tugboat va a:
   - Detectar el PR automáticamente (vía webhook).
   - Construir el preview siguiendo `init` → `update` → `build` → `online`.
   - Comentar el link del preview directo en el Pull Request de GitHub.

6. **Activá las integraciones extra** (opcional, gratis dentro del tier):
   en el dashboard de Tugboat → tu proyecto → Settings, podés activar:
   - **Visual Diffs**: compara capturas de pantalla entre el preview y la
     rama base, marca diferencias visuales automáticamente.
   - **Google Lighthouse Integration**: corre Lighthouse contra el
     preview y reporta el score directo en el PR.

## Qué pasa en cada etapa del build (resumen)

| Etapa | Cuándo corre | Qué hace en este config |
|---|---|---|
| `init` | Solo la primera vez que se crea el preview | Instala extensiones PHP, configura Apache, linkea el docroot |
| `update` | Cada vez que hay un commit nuevo en el PR | `composer install` |
| `build` | Después de `update` | Copia settings, instala o actualiza el sitio (`drush site-install` o `drush updatedb` + `config:import`), arregla permisos de `files/` |
| `online` | Cuando el preview YA está público y respondiendo | Smoke test de la home, PHPUnit (Unit/Kernel), espacio para Playwright/Cypress |

## Lo que tenés que CAMBIAR para tu proyecto

| Dónde | Qué revisar |
|---|---|
| `image: tugboatqa/php:8.5-apache-bookworm` | Si tu Drupal 11 todavía corre en PHP 8.3/8.4 en producción, usá esa misma versión acá para que el preview sea representativo |
| `ln -snf "${TUGBOAT_ROOT}/web" "${DOCROOT}"` | Cambiá `web` si tu docroot tiene otro nombre |
| `drush site-install standard` | Cambiá `standard` por tu perfil de instalación real |
| `image: tugboatqa/mariadb:10.11` | Cambiá si tu stack usa otra versión certificada |
| Bloque de PHPUnit en `online` | Ajustá rutas si tus tests no están donde asume el ejemplo |
| Bloque de Playwright comentado en `online` | Descomentá y ajustá si querés correr los E2E de `tests/playwright/` contra cada preview |

## Tugboat vs. los workflows de deploy del resto del paquete

No son lo mismo y no compiten entre sí:

- **Tugboat**: previews efímeros por PR, para revisión humana antes de
  mergear. No es deploy a producción.
- **`deploy-*.yml`** (Acquia, Pantheon, Platform.sh, Lagoon, SSH genérico):
  deploy real a producción/staging, después de que el PR ya se mergeó.

Un flujo de equipo completo normalmente usa ambos: Tugboat para que el
equipo revise visualmente el PR, y uno de los `deploy-*.yml` para llevar
el cambio a producción una vez aprobado y mergeado.

## Troubleshooting común

- **El build falla en `composer install` por memoria**: subí el plan de
  Tugboat o reducí dependencias; `COMPOSER_MEMORY_LIMIT=-1` ya está
  seteado en este config, pero el contenedor en sí tiene un límite de RAM
  según tu plan.
- **El sitio se instala pero da error 500 en la URL pública**: revisá que
  `trusted_host_patterns` en `settings.tugboat.php` incluya el dominio de
  Tugboat (`.tugboatqa.com`), y que el include desde `settings.php` esté
  bien puesto.
- **El preview no se crea en absoluto**: confirmá que `.tugboat/config.yml`
  ya estaba en el repo ANTES de abrir el Pull Request — si lo agregás
  después, tenés que cerrar y reabrir el PR para que Tugboat lo detecte.
