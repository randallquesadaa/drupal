<?php

/**
 * Configuracion de Drupal para entornos Pantheon.
 *
 * Incluido por settings.php cuando $_ENV['PANTHEON_ENVIRONMENT'] existe.
 */

// Base de datos via variables de entorno de Pantheon.
if (isset($_ENV['DB_HOST'])) {
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
}

// Hash salt provisto por Pantheon.
if (isset($_ENV['DRUPAL_HASH_SALT'])) {
  $settings['hash_salt'] = $_ENV['DRUPAL_HASH_SALT'];
}

// Hosts de confianza para subdominios de Pantheon.
$settings['trusted_host_patterns'] = [
  '^.+\.pantheonsite\.io$',
  '^.+\.pantheon\.io$',
  '^.+\.getpantheon\.com$',
];

// Rutas de archivos.
$settings['file_public_path'] = 'sites/default/files';
if (isset($_ENV['PANTHEON_BINDING'])) {
  $settings['file_private_path'] = '/srv/bindings/' . $_ENV['PANTHEON_BINDING'] . '/files/private';
  $settings['file_temp_path']    = '/srv/bindings/' . $_ENV['PANTHEON_BINDING'] . '/tmp';
}
else {
  $settings['file_private_path'] = 'sites/default/files/private';
  $settings['file_temp_path']    = '/tmp';
}

// Pantheon maneja los permisos de archivos.
$settings['skip_permissions_hardening'] = TRUE;

// Configuracion de Redis (descomentar si tu plan incluye Redis).
// if (isset($_ENV['CACHE_HOST'])) {
//   $settings['redis.connection']['interface'] = 'PhpRedis';
//   $settings['redis.connection']['host']      = $_ENV['CACHE_HOST'];
//   $settings['redis.connection']['port']      = $_ENV['CACHE_PORT'];
//   $settings['redis.connection']['password']  = $_ENV['CACHE_PASSWORD'];
//   $settings['cache']['default'] = 'cache.backend.redis';
// }
