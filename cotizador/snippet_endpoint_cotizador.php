<?php
// Endpoint REST para recibir datos y PDF desde el cotizador JS y enviar por correo con adjunto
add_action('rest_api_init', function () {
    register_rest_route('cotizador/v1', '/enviar', array(
        'methods' => 'POST',
        'callback' => 'enviar_cotizacion_email_pdf',
        'permission_callback' => '__return_true'
    ));
});

function enviar_cotizacion_email_pdf($request) {
    $params = $request->get_json_params();

    $to = 'info@edenmendez.com'; // Cambia aquí tu correo fijo
    $subject = 'Nueva cotización desde el Cotizador Web';

    // Construir mensaje de texto
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

    // Si viene el PDF en base64, crear archivo temporal y adjuntarlo
    $log_file = sys_get_temp_dir() . '/cotizador_debug.log';
    file_put_contents($log_file, "\n--- NUEVA PETICIÓN ---\n", FILE_APPEND);
    if (!empty($params['pdf'])) {
        file_put_contents($log_file, "Base64 recibido, length: ".strlen($params['pdf'])."\n", FILE_APPEND);
        $pdf_data = base64_decode($params['pdf']);
        $tmp_pdf = tempnam(sys_get_temp_dir(), 'cotizacion_') . '.pdf';
        file_put_contents($tmp_pdf, $pdf_data);
        file_put_contents($log_file, "Archivo temporal creado: $tmp_pdf, size: ".filesize($tmp_pdf)."\n", FILE_APPEND);
        $attachments[] = $tmp_pdf;
    } else {
        file_put_contents($log_file, "NO se recibió base64 PDF\n", FILE_APPEND);
    }
    file_put_contents($log_file, "Attachments: ".print_r($attachments, true)."\n", FILE_APPEND);

    $headers = array('Content-Type: text/plain; charset=UTF-8');

    $sent = wp_mail($to, $subject, $message, $headers, $attachments);

    // Eliminar archivo temporal
    if (!empty($attachments)) {
        foreach ($attachments as $file) {
            @unlink($file);
        }
    }

    if ($sent) {
        return array('success' => true);
    } else {
        return array('success' => false);
    }
}
