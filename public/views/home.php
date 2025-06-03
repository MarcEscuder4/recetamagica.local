<?php
require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/DataBaseController.php'; // Ajusta si tu path es diferente

// Obtener conexión a la base de datos
$db = DatabaseController::getInstance()->getConnection();

try {
    // Obtener dos frases aleatorias
    $stmt = $db->query("SELECT phrase, author FROM phrases ORDER BY RAND() LIMIT 2");
    $phrases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Manejo por si hay menos de 2 frases
    $phrase1 = $phrases[0] ?? ['phrase' => 'Sin frase.', 'author' => 'Desconocido'];
    $phrase2 = $phrases[1] ?? ['phrase' => 'Sin frase.', 'author' => 'Desconocido'];

} catch (PDOException $e) {
    error_log("Error al obtener las frases: " . $e->getMessage());
    $phrase1 = ['phrase' => 'Error cargando frase.', 'author' => 'Sistema'];
    $phrase2 = ['phrase' => 'Error cargando frase.', 'author' => 'Sistema'];
}

echo $twig->render('home.html', [
    'phrase1' => $phrase1['phrase'],
    'author1' => $phrase1['author'],
    'phrase2' => $phrase2['phrase'],
    'author2' => $phrase2['author'],
]);
