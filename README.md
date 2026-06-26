# Pipeline CI/CD para Drupal (GitHub Actions) — 100% herramientas gratuitas

Este paquete contiene un pipeline completo de calidad, testing y despliegue para
un sitio Drupal, pensado para correr en GitHub Actions usando únicamente
herramientas gratuitas (incluso para repos privados, dentro de los minutos
gratis de Actions).

## ⚠️ Antes de nada

**Este pipeline NO funciona "out of the box".** Está armado como plantilla
genérica. Tenés que adaptar rutas, versiones y nombres a TU sitio Drupal
específico. Toda la lista de cosas a cambiar está en `docs/SETUP.md`.
Buscá el texto `CAMBIAR:` dentro de cada archivo — son los puntos exactos
que hay que tocar.

## Qué incluye

| Etapa | Herramienta | Archivo |
|---|---|---|
| Lint PHP (estándares Drupal) | PHP_CodeSniffer + Coder | `phpcs.xml` |
| Análisis estático PHP | PHPStan + phpstan-drupal | `phpstan.neon` |
| Lint JS | ESLint | `.eslintrc.json` |
| Lint CSS/SCSS | Stylelint | `.stylelintrc.json` |
| Auditoría de dependencias | `composer audit` + composer-drupal-security-advisories | `.github/workflows/ci.yml` |
| Tests Unit/Kernel | PHPUnit | `.github/workflows/ci.yml` |
| Build + instalación del sitio | Drush + MySQL service | `.github/workflows/ci.yml` |
| Tests E2E (funcionales) | Playwright | `.github/workflows/e2e.yml`, `tests/playwright/` |
| Performance + Accesibilidad | Lighthouse CI + pa11y-ci | `.github/workflows/quality.yml`, `.lighthouserc.json` |
| Regresión visual | BackstopJS | `.github/workflows/quality.yml`, `tests/backstop/` |
| Cobertura de código | Codecov (gratis en repos públicos) | `.github/workflows/ci.yml` |
| Deploy | SSH/rsync, Acquia, Pantheon, Platform.sh o Lagoon — elegís uno | `.github/workflows/deploy-*.yml` + `docs/DEPLOY-OPTIONS.md` |
| Preview por PR (sitio vivo, no solo tests) | Tugboat (Drupal 11 + PHP 8.5) | `.tugboat/config.yml` + `docs/TUGBOAT.md` |

## Orden del pipeline

```
        ┌─────────────┐
        │  lint        │  phpcs · phpstan · eslint · stylelint
        └──────┬───────┘
               │
        ┌──────▼───────┐
        │  security    │  composer audit · security-advisories
        └──────┬───────┘
               │
        ┌──────▼───────┐
        │ unit-kernel  │  PHPUnit Unit + Kernel + cobertura
        │ + build-site │  drush site-install + config:import
        └──────┬───────┘
               │
       ┌───────┴────────┐
       ▼                ▼
┌─────────────┐   ┌──────────────┐
│  e2e tests  │   │  quality     │  Lighthouse · pa11y · BackstopJS
│  (Playwright)│   │  (en paralelo)│
└──────┬──────┘   └──────┬───────┘
       └────────┬────────┘
                ▼
         ┌─────────────┐        ┌───────────────────┐
         │   deploy     │        │  Tugboat preview  │
         │ (elegí 1 de  │        │  (en paralelo,    │
         │  5 opciones) │        │  apenas se abre   │
         │ solo en main │        │  el PR — no       │
         └─────────────┘        │  depende de CI)   │
                                 └───────────────────┘
```

Tugboat corre **independiente** del resto del pipeline: se dispara apenas
se abre el PR (no espera a que termine `ci.yml`), porque su objetivo es
dar feedback visual rápido, no gatear un merge.

## Instalación rápida

1. Copiá toda la carpeta `.github/`, los archivos de config (`phpcs.xml`,
   `phpstan.neon`, `.eslintrc.json`, `.stylelintrc.json`, `.lighthouserc.json`)
   y la carpeta `tests/` a la raíz de tu repo Drupal.
2. Abrí `docs/SETUP.md` y seguí el checklist paso a paso.
3. Para el deploy, abrí `docs/DEPLOY-OPTIONS.md`, elegí el workflow que
   corresponde a tu hosting (Acquia, Pantheon, Platform.sh, Lagoon o SSH
   genérico) y borrá los otros cuatro de `.github/workflows/`.
4. Para previews automáticos por Pull Request, copiá la carpeta
   `.tugboat/` a la raíz de tu repo y seguí `docs/TUGBOAT.md`.
5. Agregá los *secrets* necesarios en GitHub (Settings → Secrets and variables
   → Actions). La lista completa está en `docs/SETUP.md` y `docs/DEPLOY-OPTIONS.md`.
6. Hacé un commit y un PR de prueba para ver los workflows correr.
7. Ajustá umbrales (cobertura, performance, accesibilidad) según tu realidad
   — están pensados como punto de partida exigente pero realista, no como
   ley física.

## Costo

Todo lo usado acá es gratis:
- GitHub Actions: 2,000 min/mes gratis en repos privados, ilimitado en públicos.
- Lighthouse CI, pa11y-ci, BackstopJS, PHPStan, PHPCS, ESLint, Stylelint, PHPUnit, Playwright: open source, sin costo.
- Codecov y SonarCloud: gratis para repos **públicos** (si tu repo es privado,
  Codecov tiene un free tier limitado y SonarCloud no es gratis — están
  marcados como opcionales).
- Tugboat: tiene un tier gratuito con límite de previews simultáneos
  (revisá tugboatqa.com/pricing para los números actuales, cambian con
  el tiempo). Si tu equipo es grande o necesita muchos previews a la vez,
  puede que necesites un plan pago.

## Filosofía del pipeline

- **Falla rápido y barato primero**: lint y análisis estático antes que nada,
  porque son los más rápidos y detectan el 80% de los problemas comunes.
- **Nada de "funciona en mi máquina"**: el sitio se instala desde cero en cada
  corrida (`drush site-install` + `config:import`), así detectás configuración
  rota antes de que llegue a producción.
- **Visual y performance no son opcionales**: un sitio puede pasar todos los
  tests funcionales y verse roto o ser lentísimo. Lighthouse y BackstopJS
  cubren ese hueco.
- **Deploy gateado**: nada se despliega si algún check falló.
