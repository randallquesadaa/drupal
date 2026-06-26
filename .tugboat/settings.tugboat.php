<?php

/**
 * Settings específicos de Tugboat.
 *
 * Este archivo se copia automáticamente a
 * web/sites/default/settings.tugboat.php durante el build (ver el paso
 * "build" en .tugboat/config.yml). NO lo edites directamente en el sitio
 * instalado — editá este archivo fuente en .tugboat/.
 *
 * CAMBIAR: ajustá lo que corresponda según tu proyecto. Los puntos
 * marcados abajo son los más comunes a personalizar.
 */

// ── Base de datos ──────────────────────────────────────────────────────
// "mysql" es el nombre del servicio definido en .tugboat/config.yml.
// Usuario, clave y base son siempre "tugboat" por defecto en la imagen
// tugboatqa/mariadb — no son secretos reales, son fijos del contenedor.
$databases['default']['default'] = [
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

// ── Hash salt único por preview ────────────────────────────────────────
// TUGBOAT_REPO_ID es distinto para cada preview, así que cada uno tiene
// su propio salt sin que tengas que generarlo a mano.
$settings['hash_salt'] = hash('sha256', getenv('TUGBOAT_REPO_ID'));

// ── Hosts confiables ────────────────────────────────────────────────────
// Los previews de Tugboat usan URLs *.tugboatqa.com. Sin esto, Drupal
// rechaza la request por seguridad (trusted host security).
$settings['trusted_host_patterns'] = [
  '.+\.tugboatqa\.com$',
];

// ── Config sync directory ──────────────────────────────────────────────
// CAMBIAR: si tu carpeta de config exportada vive en otro lado, ajustá
// esta ruta. Por defecto en Drupal 11 con drupal/recommended-project
// suele ser "../config/sync" relativo al docroot, y normalmente esto ya
// está definido en tu settings.php real — en ese caso, BORRÁ esta línea
// para no pisarlo.
// $settings['config_sync_directory'] = getenv('TUGBOAT_ROOT') . '/config/sync';

// ── Archivos privados (si los usás) ────────────────────────────────────
// CAMBIAR: descomentá y ajustá si tu sitio usa un directorio de archivos
// privados fuera del docroot.
// $settings['file_private_path'] = getenv('TUGBOAT_ROOT') . '/files-private';

// ── Permisos ────────────────────────────────────────────────────────────
// Evita que Drupal "endurezca" los permisos de sites/default, lo cual
// rompe el flujo de builds repetidos de Tugboat.
$settings['skip_permissions_hardening'] = TRUE;

// ── Memoria en CLI (drush, composer) ───────────────────────────────────
// Evita errores de memory_limit al correr drush/composer dentro del
// contenedor durante el build.
if (PHP_SAPI === 'cli') {
  ini_set('memory_limit', '-1');
}

// ── Mails: nunca enviar mails reales desde un preview ──────────────────
// CAMBIAR: si usás Swiftmailer/Symfony Mailer en vez del sistema de mail
// nativo de Drupal, ajustá la clave de config correspondiente.
$config['system.mail']['interface']['default'] = 'test_mail_collector';

// ── Performance: igual que en producción, para que Lighthouse mida algo
// realista si activaste la integración de Lighthouse en Tugboat. ───────
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;

// ── Evitar que motores de búsqueda indexen previews ────────────────────
$config['system.site']['page']['403'] = NULL;
// CAMBIAR: si querés, agregá un header X-Robots-Tag desde Apache/.htaccess
// en vez de acá, es más confiable.
