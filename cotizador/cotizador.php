<?php
/*
Plugin Name: Cotizador Pro – Cotizaciones Personalizadas en PDF
Description: Cotizador profesional y personalizable para WordPress. Permite a tus clientes generar cotizaciones de servicios, recibirlas en PDF por correo, y gestiona un historial completo desde el panel de administración. Incluye generación automática de PDF, envío por email, historial descargable y diseño moderno adaptable a tus servicios.
Version: 1.0
Author: Eden Mendez
*/

// Crear tabla personalizada al activar el plugin
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'cotizaciones';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fecha DATETIME NOT NULL,
        cliente VARCHAR(255),
        email VARCHAR(255),
        monto VARCHAR(50),
        archivo VARCHAR(255)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// Agregar página de opciones al admin como menú principal
add_action('admin_menu', function() {
    add_menu_page(
        'Cotizador Pro', // Título de la página
        'Cotizador Pro', // Nombre en el menú
        'manage_options',          // Capacidad
        'cotizador-opciones',      // Slug
        'cotizador_opciones_page', // Función de contenido
        'dashicons-media-spreadsheet', // Icono tipo hoja de cálculo (ideal para cotizaciones)
        26                        // Posición: justo debajo de Comentarios
    );
    add_submenu_page(
        'cotizador-opciones',
        'Ajustes',
        'Ajustes',
        'manage_options',
        'cotizador-opciones',
        'cotizador_opciones_page'
    );
    add_submenu_page(
        'cotizador-opciones',
        'Historial de Cotizaciones',
        'Historial de Cotizaciones',
        'manage_options',
        'cotizador-historial',
        'cotizador_historial_page'
    );
});

// Endpoint para guardar cotizaciones y ruta PDF + enviar emails
add_action('rest_api_init', function() {
    register_rest_route('cotizador/v1', '/guardar/', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function($request) {
            global $wpdb;
            $params = $request->get_json_params();

            $tabla = $wpdb->prefix . 'cotizaciones';

            $wpdb->insert($tabla, array(
                'fecha' => current_time('mysql'),
                'cliente' => sanitize_text_field($params['nombre']),
                'email' => sanitize_email($params['correo']),
                'monto' => sanitize_text_field($params['total']),
                'archivo' => ''
            ));

            // Preparar datos para email
            $vars = array(
                '{nombre}' => sanitize_text_field($params['nombre']),
                '{email}' => sanitize_email($params['correo']),
                '{monto}' => sanitize_text_field($params['total']),
                '{fecha}' => date('Y-m-d H:i')
            );

            // Leer plantilla HTML
            $plantilla_path = dirname(__FILE__) . '/../plantilla-cotizador/';
            $plantilla_html = file_exists($plantilla_path) ? file_get_contents($plantilla_path) : '';
            foreach ($vars as $k => $v) { $plantilla_html = str_replace($k, $v, $plantilla_html); }

            // Opciones del plugin
            $mail_admin = get_option('cotizador_mail_admin', get_option('admin_email'));
            $asunto_admin = strtr(get_option('cotizador_asunto_admin', 'Nueva cotización recibida'), $vars);
            $asunto_cliente = strtr(get_option('cotizador_asunto_cliente', 'Tu cotización personalizada'), $vars);
            $cuerpo_admin = strtr(get_option('cotizador_cuerpo_admin', 'Has recibido una nueva cotización. Ver detalles adjuntos.'), $vars);
            $cuerpo_cliente = strtr(get_option('cotizador_cuerpo_cliente', '¡Gracias por tu interés! Te enviamos tu cotización personalizada adjunta.'), $vars);
            // Enviar al admin
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $attachments = $data['archivo'] ? array( cotizador_url_to_path($data['archivo']) ) : array();
            wp_mail($mail_admin, $asunto_admin, $cuerpo_admin . '<br><br>' . $plantilla_html, $headers, $attachments);
            // Enviar al cliente
            if (is_email($data['email'])) {
                wp_mail($data['email'], $asunto_cliente, $cuerpo_cliente . '<br><br>' . $plantilla_html, $headers, $attachments);
            }
            return array('success' => true);
        }
    ));
});

// Convierte una URL de uploads a ruta absoluta
function cotizador_url_to_path($url) {
    $upload_dir = wp_upload_dir();
    if (strpos($url, $upload_dir['baseurl']) !== false) {
        return str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
    }
    return $url;
}


// Handler AJAX para guardar el PDF subido
add_action('wp_ajax_guardar_cotizacion_pdf', 'cotizador_guardar_pdf');
add_action('wp_ajax_nopriv_guardar_cotizacion_pdf', 'cotizador_guardar_pdf');
function cotizador_guardar_pdf() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado'], 403);
    }
    if (!isset($_FILES['file'])) {
        wp_send_json_error(['message' => 'No se recibió archivo'], 400);
    }
    $file = $_FILES['file'];
    $upload_dir = wp_upload_dir();
    $filename = 'cotizacion_' . time() . '.pdf';
    $target = $upload_dir['path'] . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $url = $upload_dir['url'] . '/' . $filename;
        wp_send_json(['url' => $url]);
    } else {
        wp_send_json_error(['message' => 'Error al guardar archivo'], 500);
    }
    wp_die();
}


function cotizador_historial_page() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'cotizaciones';
    $cotizaciones = $wpdb->get_results("SELECT * FROM $tabla ORDER BY fecha DESC");
    ?>
    <div class="wrap">
        <h1>Historial de Cotizaciones</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Monto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($cotizaciones) {
                    foreach ($cotizaciones as $cot) {
                        echo '<tr>';
                        echo '<td>' . esc_html($cot->fecha) . '</td>';
                        echo '<td>' . esc_html($cot->cliente) . '</td>';
                        echo '<td>' . esc_html($cot->email) . '</td>';
                        echo '<td>' . esc_html($cot->monto) . '</td>';
                        if ($cot->archivo) {
                            echo '<td><a href="' . esc_url($cot->archivo) . '" class="button" download>Descargar PDF</a></td>';
                        } else {
                            echo '<td><span style="color:#888">No disponible</span></td>';
                        }
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No hay cotizaciones registradas aún.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}



function cotizador_opciones_page() {
    if (isset($_POST['cotizador_guardar'])) {
        // Sanitizar los datos antes de guardar
        $tipositio = isset($_POST['cotizador_tipositio']) ? sanitize_textarea_field(wp_unslash($_POST['cotizador_tipositio'])) : '';
        $diseno = isset($_POST['cotizador_diseno']) ? sanitize_textarea_field(wp_unslash($_POST['cotizador_diseno'])) : '';
        $pagos = isset($_POST['cotizador_pagos']) ? sanitize_textarea_field(wp_unslash($_POST['cotizador_pagos'])) : '';
        $seo = isset($_POST['cotizador_seo']) ? sanitize_textarea_field(wp_unslash($_POST['cotizador_seo'])) : '';
        $mantenimiento = isset($_POST['cotizador_mantenimiento']) ? sanitize_textarea_field(wp_unslash($_POST['cotizador_mantenimiento'])) : '';
        $mail_admin = isset($_POST['cotizador_mail_admin']) ? sanitize_email($_POST['cotizador_mail_admin']) : '';
        $asunto_admin = isset($_POST['cotizador_asunto_admin']) ? sanitize_text_field($_POST['cotizador_asunto_admin']) : '';
        $asunto_cliente = isset($_POST['cotizador_asunto_cliente']) ? sanitize_text_field($_POST['cotizador_asunto_cliente']) : '';
        $cuerpo_admin = isset($_POST['cotizador_cuerpo_admin']) ? wp_kses_post($_POST['cotizador_cuerpo_admin']) : '';
        $cuerpo_cliente = isset($_POST['cotizador_cuerpo_cliente']) ? wp_kses_post($_POST['cotizador_cuerpo_cliente']) : '';
        update_option('cotizador_tipositio', $tipositio);
        update_option('cotizador_diseno', $diseno);
        update_option('cotizador_pagos', $pagos);
        update_option('cotizador_seo', $seo);
        update_option('cotizador_mantenimiento', $mantenimiento);
        update_option('cotizador_mail_admin', $mail_admin);
        update_option('cotizador_asunto_admin', $asunto_admin);
        update_option('cotizador_asunto_cliente', $asunto_cliente);
        update_option('cotizador_cuerpo_admin', $cuerpo_admin);
        update_option('cotizador_cuerpo_cliente', $cuerpo_cliente);
        echo '<div class="updated"><p>Opciones guardadas.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Opciones del Cotizador</h1>
        <div style="background:#f8fafd;border:1px solid #dbeafe;padding:18px 20px;margin-bottom:18px;font-size:1.15em;">
            <strong>Shortcode del formulario:</strong>
            <input type="text" value="[cotizador]" readonly style="font-size:1.1em;width:180px;background:#fff;border:1px solid #b6c2d1;padding:3px 8px;border-radius:4px;" onclick="this.select()">
            <span style="color:#666;font-size:0.96em">&nbsp;Cópialo y pégalo donde quieras mostrar el cotizador.</span>
        </div>
        <form method="post">
            <h2>Tipo de Sitio Web</h2>
            <textarea name="cotizador_tipositio" rows="4" cols="60"><?php echo esc_textarea(get_option('cotizador_tipositio', "Sitio Básico (One Page)|3000\nSitio Corporativo (3-5 páginas)|7000\nTienda en Línea|15000\nBlog o Revista Digital|10000")); ?></textarea>
            <p>Formato: Nombre|Precio (uno por línea)</p>
            <h2>Diseño Personalizado</h2>
            <textarea name="cotizador_diseno" rows="2" cols="60"><?php echo esc_textarea(get_option('cotizador_diseno', "No|0\nSí (+$3,000 MXN)|3000")); ?></textarea>
            <h2>Pagos en Línea</h2>
            <textarea name="cotizador_pagos" rows="2" cols="60"><?php echo esc_textarea(get_option('cotizador_pagos', "No|0\nSí (+$5,000 MXN)|5000")); ?></textarea>
            <h2>SEO</h2>
            <textarea name="cotizador_seo" rows="2" cols="60"><?php echo esc_textarea(get_option('cotizador_seo', "No|0\nSí (+$2,000 MXN)|2000")); ?></textarea>
            <h2>Mantenimiento Mensual</h2>
            <textarea name="cotizador_mantenimiento" rows="2" cols="60"><?php echo esc_textarea(get_option('cotizador_mantenimiento', "No|0\nSí (+$1,000 MXN)|1000")); ?></textarea>
            <hr>
            <h2>Notificaciones por Email</h2>
            <label for="cotizador_mail_admin"><strong>Correo del administrador:</strong></label><br>
            <input type="email" name="cotizador_mail_admin" id="cotizador_mail_admin" value="<?php echo esc_attr(get_option('cotizador_mail_admin', get_option('admin_email'))); ?>" style="width:340px;max-width:100%"><br><br>
            <label for="cotizador_asunto_admin"><strong>Asunto del correo al administrador:</strong></label><br>
            <input type="text" name="cotizador_asunto_admin" id="cotizador_asunto_admin" value="<?php echo esc_attr(get_option('cotizador_asunto_admin', 'Nueva cotización recibida')); ?>" style="width:340px;max-width:100%"><br><br>
            <label for="cotizador_cuerpo_admin"><strong>Cuerpo del correo al administrador:</strong></label><br>
            <textarea name="cotizador_cuerpo_admin" id="cotizador_cuerpo_admin" rows="4" cols="70"><?php echo esc_textarea(get_option('cotizador_cuerpo_admin', 'Has recibido una nueva cotización. Ver detalles adjuntos.')); ?></textarea><br><br>
            <label for="cotizador_asunto_cliente"><strong>Asunto del correo al cliente:</strong></label><br>
            <input type="text" name="cotizador_asunto_cliente" id="cotizador_asunto_cliente" value="<?php echo esc_attr(get_option('cotizador_asunto_cliente', 'Tu cotización personalizada')); ?>" style="width:340px;max-width:100%"><br><br>
            <label for="cotizador_cuerpo_cliente"><strong>Cuerpo del correo al cliente:</strong></label><br>
            <textarea name="cotizador_cuerpo_cliente" id="cotizador_cuerpo_cliente" rows="4" cols="70"><?php echo esc_textarea(get_option('cotizador_cuerpo_cliente', '¡Gracias por tu interés! Te enviamos tu cotización personalizada adjunta.')); ?></textarea><br><br>
            <input type="submit" name="cotizador_guardar" class="button button-primary" value="Guardar Opciones">
        </form>
    </div>
    <?php
}

// Shortcode para mostrar el cotizador
add_shortcode('cotizador', 'cotizador_shortcode');
function cotizador_shortcode() {
    // Obtener opciones del admin
    $tipos = explode("\n", get_option('cotizador_tipositio', "Sitio Básico (One Page)|3000\nSitio Corporativo (3-5 páginas)|7000\nTienda en Línea|15000\nBlog o Revista Digital|10000"));
    $disenos = explode("\n", get_option('cotizador_diseno', "No|0\nSí (+$3,000 MXN)|3000"));
    $pagos = explode("\n", get_option('cotizador_pagos', "No|0\nSí (+$5,000 MXN)|5000"));
    $seos = explode("\n", get_option('cotizador_seo', "No|0\nSí (+$2,000 MXN)|2000"));
    $mantenimientos = explode("\n", get_option('cotizador_mantenimiento', "No|0\nSí (+$1,000 MXN)|1000"));
    ob_start();
    ?>
    <div id="cotizador-app">
        <form id="formCotizador">
            <label for="clientName">Nombre:</label>
            <input type="text" id="clientName" name="clientName" required>
            <label for="clientEmail">Correo electrónico:</label>
            <input type="email" id="clientEmail" name="clientEmail" required>
            <label for="clientPhone">Teléfono:</label>
            <input type="tel" id="clientPhone" name="clientPhone" placeholder="(10 dígitos)" pattern="[0-9]{10}" required>
            <label>Tipo de Sitio Web:</label>
            <select id="siteType">
    <option value="3000">Sitio Básico (One Page)</option>
    <option value="7000">Sitio Corporativo (3-5 páginas)</option>
    <option value="15000">Tienda en Línea</option>
    <option value="10000">Blog o Revista Digital</option>
</select>
            <label>Número de páginas:</label>
            <input type="number" id="numPages" value="3" min="1">
            <label>¿Requiere diseño personalizado?</label>
            <select id="design">
    <option value="0">No</option>
    <option value="3000">Sí</option>
</select>
            <label>¿Pagos en línea?</label>
            <select id="payments">
    <option value="0">No</option>
    <option value="5000">Sí</option>
</select>
            <label>Optimización SEO:</label>
            <select id="seo">
    <option value="0">No</option>
    <option value="2000">Sí</option>
</select>
            <label>¿Mantenimiento mensual?</label>
            <select id="maintenance">
    <option value="0">No</option>
    <option value="1000">Sí</option>
</select>
            <h3>Total: <span id="totalAmount"></span></h3>
            <button type="button" id="sendQuote" class="button button-primary">Enviar Cotización</button>
        </form>
        <div id="cotizador-mensaje"></div>
    </div>
    <?php
    return ob_get_clean();
}

// Cargar scripts y estilos
add_action('wp_enqueue_scripts', 'cotizador_enqueue_scripts');
function cotizador_enqueue_scripts() {
    global $post;
    if (is_singular() && isset($post->post_content) && has_shortcode($post->post_content, 'cotizador')) {
        wp_enqueue_style('cotizador-css', plugins_url('assets/cotizador.css', __FILE__));
        wp_enqueue_script('cotizador-js', plugins_url('assets/cotizador.js', __FILE__), array('jquery'), null, true);
        wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), null, true);
        wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), null, true);
    }
}