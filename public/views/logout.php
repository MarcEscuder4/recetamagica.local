<?php

require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';

// Cerrar la sesión
SessionController::userLogout();

// Redirigir a la página de administración después de procesar el formulario
header("Location: /");
exit;