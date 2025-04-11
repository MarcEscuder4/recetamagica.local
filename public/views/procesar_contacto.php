<?php
// Iniciar sesión o cualquier configuración necesaria
session_start();

// Verificar si el formulario fue enviado (es decir, si el método es POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Capturar los valores del formulario
    $motivo = $_POST['motivo'];
    $nombre = $_POST['username'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $preferencia = $_POST['preferencia'];
    $mensaje = $_POST['mensaje'];

    // Capturar el valor del reCAPTCHA
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // La clave secreta proporcionada por Google (no la compartas)
    $secretKey = "TU_CLAVE_SECRETA";

    // La URL de Google para verificar el reCAPTCHA
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    // Los parámetros para enviar a Google
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse
    ];

    // Usar cURL para enviar la solicitud a Google y obtener la respuesta
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // Ejecutar la solicitud y decodificar la respuesta de Google
    $response = curl_exec($ch);
    curl_close($ch);
    $responseKeys = json_decode($response, true);

    // Validar la respuesta de Google
    if (intval($responseKeys["success"]) !== 1) {
        // Si la validación falla, muestra un mensaje de error
        $_SESSION['error'] = "La validación de reCAPTCHA ha fallado. Por favor, intenta nuevamente.";
        header("Location: /contacto"); // Redirigir de nuevo al formulario de contacto
        exit;
    } else {
        // Si la validación es exitosa, continuar con el procesamiento del formulario
        // Ejemplo: Enviar un correo electrónico

        $to = "tucorreo@dominio.com"; // Dirección de correo a la que se enviará el formulario
        $subject = "Nuevo mensaje de contacto de " . $nombre;
        $message = "
        <html>
        <head>
            <title>Nuevo mensaje de contacto</title>
        </head>
        <body>
            <p>Motivo del contacto: $motivo</p>
            <p>Nombre: $nombre</p>
            <p>Teléfono: $telefono</p>
            <p>Email: $email</p>
            <p>Preferencia de respuesta: $preferencia</p>
            <p>Mensaje: $mensaje</p>
        </body>
        </html>
        ";

        // Para enviar un correo HTML, se deben establecer los encabezados adecuados
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";

        // Puedes añadir más encabezados si es necesario (ej. "From", "Reply-To", etc.)
        $headers .= "From: $email" . "\r\n";

        // Enviar el correo
        if (mail($to, $subject, $message, $headers)) {
            // Si el correo se envió correctamente, redirigir al usuario con un mensaje de éxito
            $_SESSION['success'] = "Gracias por tu mensaje. Nos pondremos en contacto contigo pronto.";
            header("Location: /contacto");
        } else {
            // Si el correo no se envió, mostrar error
            $_SESSION['error'] = "Hubo un error al enviar tu mensaje. Por favor, intenta nuevamente.";
            header("Location: /contacto");
        }
        exit;
    }
}
?>
