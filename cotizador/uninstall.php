<?php
// Seguridad: evitar acceso directo
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Eliminar tabla personalizada de cotizaciones
global $wpdb;
$tabla = $wpdb->prefix . 'cotizaciones';
$wpdb->query("DROP TABLE IF EXISTS $tabla");

// Eliminar archivos PDF generados por el plugin (carpeta uploads/cotizaciones)
$upload_dir = wp_upload_dir();
$cotizaciones_dir = $upload_dir['basedir'] . '/cotizaciones';
if (is_dir($cotizaciones_dir)) {
    $files = glob($cotizaciones_dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    @rmdir($cotizaciones_dir);
}

// Opcional: eliminar opciones del plugin
delete_option('cotizador_mail_admin');
delete_option('cotizador_asunto_admin');
delete_option('cotizador_asunto_cliente');
delete_option('cotizador_cuerpo_admin');
delete_option('cotizador_cuerpo_cliente');
delete_option('cotizador_tipositio');
delete_option('cotizador_diseno');
delete_option('cotizador_pagos');
delete_option('cotizador_seo');
delete_option('cotizador_mantenimiento');
