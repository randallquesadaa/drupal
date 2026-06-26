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

// Pantheon maneja los permisos de archivos.
$settings['skip_permissions_hardening'] = TRUE;
