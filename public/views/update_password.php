<?php
session_start();

require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/DataBaseController.php'; // Ajusta si tu path es diferente

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$db = DatabaseController::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$success = '';
$error = '';

// Validar
if ($new !== $confirm) {
    $error = "Las nuevas contraseñas no coinciden.";
} else {
    // Obtener la contraseña actual del usuario
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password'])) {
        $error = "La contraseña actual no es correcta.";
    } else {
        // Actualizar nueva contraseña
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        $success = "Contraseña actualizada correctamente.";
    }
}

echo $twig->render('profile.html', [
    'password_error' => $error,
    'password_success' => $success
]);
