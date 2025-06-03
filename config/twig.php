<?php

require_once '../vendor/autoload.php';

// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'autoescape' => 'html',
    'debug' => true,
]);

// Extensiones y filtros
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Implementación para traducciones
class GettextExtension extends \Twig\Extension\AbstractExtension {
    public function getFilters() {
        return [
            new \Twig\TwigFilter('trans', 'gettext'),
        ];
    }
}
$twig->addExtension(new GettextExtension());

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PASAR TODA LA SESIÓN como variable global
$twig->addGlobal('session', $_SESSION);

// También pasar errores/mensajes individuales si quieres (opcional)
$twig->addGlobal('error', $_SESSION['error'] ?? null);
$twig->addGlobal('success_message', $_SESSION['success_message'] ?? null);
$twig->addGlobal('success', $_SESSION['success'] ?? null);

return $twig;
