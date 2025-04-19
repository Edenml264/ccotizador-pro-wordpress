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
    // Endpoint principal: guarda en historial y envía correos (admin y cliente)
    register_rest_route('cotizador/v1', '/guardar/', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function($request) {
            global $wpdb;
            $params = $request->get_json_params();

            $tabla = $wpdb->prefix . 'cotizaciones';

            // Crear archivo HTML de la cotización si existe el campo
            $html_url = '';
            if (!empty($params['archivo_html'])) {
                $upload_dir = wp_upload_dir();
                $filename = 'cotizacion_' . time() . '_' . uniqid() . '.html';
                $file_path = $upload_dir['path'] . '/' . $filename;
                file_put_contents($file_path, $params['archivo_html']);
                $html_url = $upload_dir['url'] . '/' . $filename;
            }
            $pdf_url = isset($params['archivo']) ? esc_url_raw($params['archivo']) : '';
            $wpdb->insert($tabla, array(
                'fecha' => current_time('mysql'),
                'cliente' => sanitize_text_field($params['nombre']),
                'email' => sanitize_email($params['correo']),
                'monto' => sanitize_text_field($params['total']),
                'archivo' => $html_url ? $html_url : $pdf_url
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
            // Generar tabla HTML con los datos del formulario para el cuerpo del correo admin
            $tabla_campos = '<table style="border-collapse:collapse;max-width:480px;width:100%;margin:22px 0 18px 0;font-size:1.08em;">';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Nombre</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['nombre']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Correo</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['correo']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Teléfono</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['telefono']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Tipo de Sitio</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['tipoSitio']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Precio Tipo Sitio</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['precioTipoSitio']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Número de páginas</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['numPaginas']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Diseño</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['diseno']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Pagos en línea</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['pagos']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">SEO</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['seo']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Mantenimiento</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['mantenimiento']) . '</td></tr>';
            $tabla_campos .= '<tr><td style="font-weight:bold;padding:6px 10px;border:1px solid #ddd;background:#f0f4f8;">Total</td><td style="padding:6px 10px;border:1px solid #ddd;">' . esc_html($params['total']) . '</td></tr>';
            $tabla_campos .= '</table>';
            $cuerpo_admin = strtr(get_option('cotizador_cuerpo_admin', 'Has recibido una nueva cotización. Ver detalles adjuntos.'), $vars) . $tabla_campos;
            $cuerpo_cliente = strtr(get_option('cotizador_cuerpo_cliente', '¡Gracias por tu interés! Te enviamos tu cotización personalizada adjunta.'), $vars);
            // Enviar al admin
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $attachments = array();
            if ($html_url) {
                $attachments[] = cotizador_url_to_path($html_url);
            }
            wp_mail($mail_admin, $asunto_admin, $cuerpo_admin . '<br><br>' . $plantilla_html, $headers, $attachments);
            // Enviar al cliente
            if (is_email($data['email'])) {
                wp_mail($data['email'], $asunto_cliente, $cuerpo_cliente . '<br><br>' . $plantilla_html, $headers, $attachments);
            }
            return array('success' => true);
        }
    ));
    // Endpoint secundario: solo envía correo, sin guardar en historial (opcional, útil para pruebas)
    register_rest_route('cotizador/v1', '/enviar', array(
        'methods' => 'POST',
        'callback' => function($request) {
            $params = $request->get_json_params();
            $to = get_option('cotizador_mail_admin', get_option('admin_email'));
            $subject = 'Nueva cotización desde el Cotizador Web';
            $message = "Nombre: {$params['nombre']}\n";
            $message .= "Correo: {$params['correo']}\n";
            $message .= "Teléfono: {$params['telefono']}\n";
            $message .= "Tipo de Sitio: {$params['tipoSitio']}\n";
            $message .= "Número de páginas: {$params['numPaginas']}\n";
            $message .= "Diseño personalizado: {$params['diseno']}\n";
            $message .= "Pagos en línea: {$params['pagos']}\n";
            $message .= "SEO: {$params['seo']}\n";
            $message .= "Mantenimiento: {$params['mantenimiento']}\n";
            $message .= "Total: {$params['total']}\n";
            $attachments = array();
            if (!empty($params['pdf'])) {
                $pdf_data = base64_decode($params['pdf']);
                $tmp_pdf = tempnam(sys_get_temp_dir(), 'cotizacion_') . '.pdf';
                file_put_contents($tmp_pdf, $pdf_data);
                $attachments[] = $tmp_pdf;
            }
            $headers = array('Content-Type: text/plain; charset=UTF-8');
            $sent = wp_mail($to, $subject, $message, $headers, $attachments);
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    @unlink($file);
                }
            }
            return array('success' => $sent ? true : false);
        },
        'permission_callback' => '__return_true'
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
                        echo '<td>';
                        if ($cot->archivo) {
                            echo '<a href="' . esc_url($cot->archivo) . '" class="button button-primary" download style="margin-right:5px;">Descargar PDF</a>';
                        } else {
                            echo '<span style="color:#888; margin-right:5px;">No disponible</span>';
                        }
                        echo '<button class="button button-secondary ver-detalles-cotizacion" data-cotizacion="' . esc_attr(json_encode($cot)) . '">Ver Detalles</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No hay cotizaciones registradas aún.</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <div id="modal-detalles-cotizacion" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:10000;align-items:center;justify-content:center;">
            <div style="background:#fff;padding:30px 40px;border-radius:10px;max-width:500px;width:90%;position:relative;">
                <button id="cerrar-modal-cotizacion" style="position:absolute;top:10px;right:10px;" class="button">Cerrar</button>
                <div id="contenido-modal-cotizacion"></div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.ver-detalles-cotizacion').forEach(btn => {
                btn.addEventListener('click', function() {
                    let cot = JSON.parse(this.getAttribute('data-cotizacion'));
                    let html = `<h2>Detalles de Cotización</h2>` +
                        `<p><strong>Fecha:</strong> ${cot.fecha}</p>` +
                        `<p><strong>Cliente:</strong> ${cot.cliente}</p>` +
                        `<p><strong>Email:</strong> ${cot.email}</p>` +
                        `<p><strong>Monto:</strong> ${cot.monto}</p>` +
                        (cot.archivo ? `<p><strong>PDF:</strong> <a href='${cot.archivo}' target='_blank'>Descargar</a></p>` : `<p><strong>PDF:</strong> No disponible</p>`);
                    document.getElementById('contenido-modal-cotizacion').innerHTML = html;
                    document.getElementById('modal-detalles-cotizacion').style.display = 'flex';
                });
            });
            document.getElementById('cerrar-modal-cotizacion').addEventListener('click', function() {
                document.getElementById('modal-detalles-cotizacion').style.display = 'none';
            });
        });
        </script>
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