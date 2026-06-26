# Opciones de deploy — elegí UNA y borrá las demás

En `.github/workflows/` vas a encontrar **cinco** archivos de deploy. Están
separados a propósito para que sea fácil de entender: elegís el que
corresponde a tu hosting, lo configurás, y **borrás los otros cuatro**
(o los dejás pero deshabilitados — ver más abajo).

| Archivo | Hosting | Cómo dispara el deploy |
|---|---|---|
| `deploy-ssh-generico.yml` | Servidor propio / VPS / hosting tradicional | rsync + SSH |
| `deploy-acquia.yml` | Acquia Cloud | git push al remoto de Acquia |
| `deploy-pantheon.yml` | Pantheon | git push + Terminus CLI |
| `deploy-platformsh.yml` | Platform.sh / Upsun | Platform.sh CLI |
| `deploy-lagoon.yml` | Lagoon (amazee.io o self-hosted) | Lagoon API (GraphQL) |

## ¿Cómo elijo?

Buscá dónde corre tu sitio en producción hoy. Si no estás seguro, mirá:
- La URL de tu panel de administración del hosting.
- El archivo `settings.php` — Acquia y Pantheon suelen tener bloques de
  código específicos (`if (file_exists('/var/www/site-php')...`  para
  Acquia, variables `$_ENV['PANTHEON_ENVIRONMENT']` para Pantheon).
- Si tu equipo simplemente conecta vía FTP/SSH a un servidor "normal" →
  `deploy-ssh-generico.yml`.

## Importante: muchas plataformas YA hacen esto solas

Acquia, Pantheon, Platform.sh y Lagoon están diseñadas para detectar un
push a tu repositorio (ya sea el de GitHub directamente, o un remoto Git
propio de la plataforma) y deployar automáticamente. **Estos workflows
existen para los casos en que querés que ese deploy dependa explícitamente
de que tu pipeline de CI (lint, tests, etc.) haya pasado en verde.**

Si no te importa tanto ese control fino y confiás en tus tests locales o
en revisiones de PR, una alternativa más simple y con menos partes móviles
es:
1. Configurar la integración nativa de tu hosting con GitHub (botón
   "Connect" en el dashboard de la plataforma).
2. Usar **branch protection rules** en GitHub (Settings → Branches → Add
   rule en `main`) para exigir que el workflow `CI` pase antes de poder
   hacer merge.

De esa forma, nada llega a `main` sin pasar los checks, y la plataforma de
hosting deploya sola sin que necesites mantener un workflow de deploy.

## Cómo deshabilitar los workflows que no uses

Tenés dos opciones:

**Opción A (recomendada): borrarlos.**
```bash
rm .github/workflows/deploy-acquia.yml
rm .github/workflows/deploy-pantheon.yml
rm .github/workflows/deploy-platformsh.yml
rm .github/workflows/deploy-lagoon.yml
# (dejás solo el que vas a usar)
```

**Opción B: dejarlos pero que no se disparen nunca.**
Renombrá la extensión para que GitHub Actions no los reconozca:
```bash
mv .github/workflows/deploy-acquia.yml .github/workflows/deploy-acquia.yml.disabled
```
Esto es útil si querés guardarlos como referencia por si cambiás de
hosting en el futuro.

## Tabla de secrets por opción

### SSH genérico
| Secret | Descripción |
|---|---|
| `DEPLOY_SSH_KEY` | Llave privada SSH |
| `DEPLOY_HOST` | IP o dominio del servidor |
| `DEPLOY_USER` | Usuario SSH |
| `DEPLOY_PATH` | Ruta absoluta del sitio |

### Acquia
| Secret | Descripción |
|---|---|
| `ACQUIA_SSH_KEY` | Llave privada SSH registrada en Acquia |
| `ACQUIA_GIT_URL` | URL del remoto git de Acquia |

### Pantheon
| Secret | Descripción |
|---|---|
| `PANTHEON_SSH_KEY` | Llave privada SSH registrada en Pantheon |
| `PANTHEON_SITE_UUID` | UUID del sitio |
| `TERMINUS_TOKEN` | Machine token de Pantheon |

### Platform.sh
| Secret | Descripción |
|---|---|
| `PLATFORMSH_CLI_TOKEN` | API token de Platform.sh |
| `PLATFORMSH_SSH_KEY` | Llave privada SSH registrada |
| `PLATFORMSH_PROJECT_ID` | ID del proyecto |

### Lagoon
| Secret | Descripción |
|---|---|
| `LAGOON_API_TOKEN` | Token de la API GraphQL de Lagoon |
| `LAGOON_PROJECT_NAME` | Nombre del proyecto en Lagoon |

Cada archivo `.yml` tiene, al final, el mismo detalle de secrets y los
pasos previos a hacer en el panel de cada plataforma antes de que el
workflow funcione.

## Ninguna de estas es tu plataforma

Si tu hosting no está en la lista (ej. WP Engine no aplica a Drupal, pero
sí podría ser un hosting custom, Docker/Kubernetes propio, AWS, etc.),
usá `deploy-ssh-generico.yml` como base si tenés acceso SSH, o avisame
para armar una versión específica.
