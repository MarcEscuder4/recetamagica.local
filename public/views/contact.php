<?php 
// Carga Twig
$twig = require_once __DIR__ . '/../../config/twig.php';
// Renderizar la plantilla
echo $twig->render('contact.html', [ ]);

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Capturar el token de reCAPTCHA v3
    $recaptchaResponse = $_POST['recaptcha_response'];

    // La clave secreta proporcionada por Google (no la compartas)
    $secretKey = "6LeIxAcTAAAAAJcZVRqyHh71UMI32Z0f_0fR8rzj";

    // La URL de Google para verificar el reCAPTCHA
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    // Los parámetros para enviar a Google
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        // También puedes agregar el IP del usuario si lo deseas
        // 'remoteip' => $_SERVER['REMOTE_ADDR']
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
        echo "La validación de reCAPTCHA ha fallado. Por favor, intenta nuevamente.";
    } else {
        // Si la validación es exitosa, continuar con el procesamiento del formulario
        // Aquí puedes manejar el envío del formulario (correo, base de datos, etc.)
        echo "Formulario enviado exitosamente. ¡Gracias por contactar!";
    }
}
?>
