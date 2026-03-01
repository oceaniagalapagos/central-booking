<?php
namespace CentralBooking\REST;

use WP_REST_Request;
use WP_REST_Response;

class EndpointsRoosevelt
{
    public function init_endpoints()
    {
        RegisterRoute::register(
            'contact',
            'POST',
            fn(WP_REST_Request $request) =>
            $this->send_personal_mail($request)
        );
    }

    private function send_personal_mail(WP_REST_Request $request): WP_REST_Response
    {
        $data = $request->get_json_params();

        if (!isset($data['message'], $data['email'])) {
            return new WP_REST_Response(['error' => 'Faltan campos: message y email'], 400);
        }

        $message = sanitize_textarea_field($data['message']);
        $sender_email = sanitize_email($data['email']);

        if (empty($message) || empty($sender_email)) {
            return new WP_REST_Response(['error' => 'Mensaje y email son requeridos'], 400);
        }

        $subject = $data['subject'] ?? "Nuevo mensaje de contacto - " . date('Y-m-d H:i');

        $email_body = "Nuevo mensaje de contacto:\n\n";
        $email_body .= "De: {$sender_email}\n";
        $email_body .= "Fecha: " . current_time('Y-m-d H:i:s') . "\n\n";
        $email_body .= "Mensaje:\n";
        $email_body .= $message;

        $headers = [
            "From: Contacto <noreply@" . parse_url(get_site_url(), PHP_URL_HOST) . ">",
            "Reply-To: {$sender_email}"
        ];

        $sent = wp_mail(
            'rooseveltabrigo@gmail.com',
            $subject,
            $email_body,
            $headers
        );

        if ($sent) {
            return new WP_REST_Response(['success' => true, 'message' => 'Mensaje enviado'], 200);
        } else {
            return new WP_REST_Response(['error' => 'Error enviando mensaje'], 500);
        }
    }
}