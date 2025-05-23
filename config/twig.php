<?php

require_once '../vendor/autoload.php';

// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Desactivar caché en desarrollo (en producción usa una ruta de caché)
    'autoescape' => 'html', // Escapar automáticamente para seguridad (recomendado)
    'debug' => true, // Activar modo debug para desarrollo
]);

// Añadir extensión de depuración (solo en desarrollo)
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Filtro para usar gettext {{ "Texto a traducir" | trans }}
class GettextExtension extends \Twig\Extension\AbstractExtension {
    public function getFilters() {
        return [
            new \Twig\TwigFilter('trans', 'gettext'),
        ];
    }
}

// Añadir el filtro de gettext a Twig
$twig->addExtension(new GettextExtension());

// Iniciamos la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pasar variables de sesión a Twig como globales
if (isset($_SESSION['error'])) {
    $twig->addGlobal('error', $_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $twig->addGlobal('success', $_SESSION['success']);
}

// Limpiar las variables de sesión después de cargarlas
function clearSessionMessages() {
    unset($_SESSION['error'], $_SESSION['success']);
}
clearSessionMessages();

// Devolver la instancia de Twig
return $twig;