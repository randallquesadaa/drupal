<?php

/**
 * Configuracion de Drupal para entornos Pantheon.
 *
 * Este archivo es incluido por settings.php solo cuando
 * $_ENV['PANTHEON_ENVIRONMENT'] esta definido.
 */

// Base de datos: Pantheon expone las credenciales via $_ENV.
$databases['default']['default'] = [
  'driver'    => 'mysql',
  'database'  => $_ENV['DB_NAME'],
  'username'  => $_ENV['DB_USER'],
  'password'  => $_ENV['DB_PASSWORD'],
  'host'      => $_ENV['DB_HOST'],
  'port'      => $_ENV['DB_PORT'],
  'prefix'    => '',
  'collation' => 'utf8mb4_general_ci',
];

// Hash salt unico por sitio, provisto por Pantheon.
$settings['hash_salt'] = $_ENV['DRUPAL_HASH_SALT'];

// Hosts de confianza para todos los subdominios de Pantheon.
$settings['trusted_host_patterns'] = [
  '^.+\.pantheonsite\.io$',
  '^.+\.pantheon\.io$',
  '^.+\.getpantheon\.com$',
];

// Rutas de archivos dentro del servidor de Pantheon.
$settings['file_public_path']  = 'sites/default/files';
$settings['file_private_path'] = '/srv/bindings/' . $_ENV['PANTHEON_BINDING'] . '/files/private';
$settings['file_temp_path']    = '/srv/bindings/' . $_ENV['PANTHEON_BINDING'] . '/tmp';

// Deshabilitar verificacion de permisos de archivos (Pantheon los maneja).
$settings['skip_permissions_hardening'] = TRUE;

// Configuracion de cache con Redis si esta disponible en el plan.
// Descomentar si tu plan de Pantheon incluye Redis.
// if (isset($_ENV['CACHE_HOST'])) {
//   $settings['redis.connection']['interface'] = 'PhpRedis';
//   $settings['redis.connection']['host']      = $_ENV['CACHE_HOST'];
//   $settings['redis.connection']['port']      = $_ENV['CACHE_PORT'];
//   $settings['redis.connection']['password']  = $_ENV['CACHE_PASSWORD'];
//   $settings['cache']['default'] = 'cache.backend.redis';
// }
