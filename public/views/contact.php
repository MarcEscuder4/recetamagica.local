<?php
require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$error = null;
$success = null;

print_r($_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    print_r("CONTACTOOOOOO");
    // Validar reCAPTCHA
    $recaptchaResponse = $_POST['recaptcha_response'] ?? '';
    $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'];

    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseKeys = json_decode($response, true);

    if (!isset($responseKeys['success']) || $responseKeys['success'] !== true) {
        $error = "La validación de reCAPTCHA ha fallado. Inténtalo nuevamente.";
    } else {
        // Recoger y sanitizar datos
        $motivo = htmlspecialchars($_POST['motivo'] ?? '');
        $nombre = htmlspecialchars($_POST['username'] ?? '');
        $telefono = htmlspecialchars($_POST['telefono'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $preferencia = htmlspecialchars($_POST['preferencia'] ?? '');
        $mensaje = htmlspecialchars($_POST['mensaje'] ?? '');

        if (!$email) {
            $error = "El correo electrónico no es válido.";
        } else {
            try {

                /*Configuracion de variables para enviar el correo*/
				$mail_username=$_ENV['MAIL_USERNAME'];//Correo electronico saliente ejemplo: tucorreo@gmail.com
				$mail_userpassword=$_ENV['MAIL_PASSWORD'];;//Tu contraseña de gmail
				$mail_addAddress='mescuder@elpuig.xeill.net';//correo electronico que recibira el mensaje
				$template=  "Nombre: $nombre\n" .
                            "Teléfono: $telefono\n" .
                            "Correo: $email\n" .
                            "Preferencia de contacto: $preferencia\n\n" .
                            "Mensaje:\n$mensaje";
				
				/*Inicio captura de datos enviados por $_POST para enviar el correo */
				$mail_setFromEmail=$_ENV['MAIL_FROM'];
				$mail_setFromName=$_ENV['MAIL_FROM_NAME'];
				$txt_message=$mensaje;
				$mail_subject="Nuevo mensaje de contacto: $motivo";
				
				sendemail($mail_username,$mail_userpassword,$mail_setFromEmail,$mail_setFromName,$mail_addAddress,$txt_message,$mail_subject,$template);//Enviar el mensaje
/*
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = $_ENV['MAIL_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['MAIL_USERNAME'];
                $mail->Password = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $_ENV['MAIL_PORT'];

                // Remitente y destinatario
                $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
                $mail->addAddress('mescuder@elpuig.xeill.net'); // Cambia si lo necesitas

                // Contenido del correo
                $mail->Subject = "Nuevo mensaje de contacto: $motivo";
                $mail->Body = 
                    "Nombre: $nombre\n" .
                    "Teléfono: $telefono\n" .
                    "Correo: $email\n" .
                    "Preferencia de contacto: $preferencia\n\n" .
                    "Mensaje:\n$mensaje";

                $mail->send(); 
                */
                $success = "Formulario enviado correctamente. ¡Gracias por contactarnos!";
            } catch (Exception $e) {
                $error = "No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}";
            }
        }
    }
}

// Renderizar plantilla con mensajes
echo $twig->render('contact.html', [
    'error' => $error,
    'success' => $success
]);

function sendemail($mail_username,$mail_userpassword,$mail_setFromEmail,$mail_setFromName,$mail_addAddress,$txt_message,$mail_subject, $template){
	require 'PHPMailer/PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->isSMTP();                            // Establecer el correo electrónico para utilizar SMTP
	$mail->Host = 'smtp.gmail.com';             // Especificar el servidor de correo a utilizar 
	$mail->SMTPAuth = true;                     // Habilitar la autenticacion con SMTP
	$mail->Username = $mail_username;          // Correo electronico saliente ejemplo: tucorreo@gmail.com
	$mail->Password = $mail_userpassword; 		// Tu contraseña de gmail
	$mail->SMTPSecure = 'tls';                  // Habilitar encriptacion, `ssl` es aceptada
	$mail->Port = 587;                          // Puerto TCP  para conectarse 
    // Ruta del archivo de log
    $logFile = '/var/log/phpmailer.log'; // <-- Esta es TU ruta de log

    $mail->Debugoutput = function($str, $level) use ($logFile) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " [Level $level]: $str\n", FILE_APPEND);
    };


	$mail->setFrom($mail_setFromEmail, $mail_setFromName);//Introduzca la dirección de la que debe aparecer el correo electrónico. Puede utilizar cualquier dirección que el servidor SMTP acepte como válida. El segundo parámetro opcional para esta función es el nombre que se mostrará como el remitente en lugar de la dirección de correo electrónico en sí.
	$mail->addReplyTo($mail_setFromEmail, $mail_setFromName);//Introduzca la dirección de la que debe responder. El segundo parámetro opcional para esta función es el nombre que se mostrará para responder
	$mail->addAddress($mail_addAddress);   // Agregar quien recibe el e-mail enviado
	$message = file_get_contents($template);
	$message = str_replace('{{first_name}}', $mail_setFromName, $message);
	$message = str_replace('{{message}}', $txt_message, $message);
	$message = str_replace('{{customer_email}}', $mail_setFromEmail, $message);
	$mail->isHTML(true);  // Establecer el formato de correo electrónico en HTML
	
	$mail->Subject = $mail_subject;
	$mail->msgHTML($message);
	if(!$mail->send()) {
		echo '<p style="color:red">No se pudo enviar el mensaje..';
		echo 'Error de correo: ' . $mail->ErrorInfo;
		echo "</p>";
	} else {
		echo '<p style="color:green">Tu mensaje ha sido enviado!</p>';
	}
}
?>
