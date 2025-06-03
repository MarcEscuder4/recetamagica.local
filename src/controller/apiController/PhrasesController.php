<?php
session_start();

require_once __DIR__ . '/../config/twig.php';
require_once __DIR__ . '/../controllers/DatabaseController.php';

$db = DatabaseController::getInstance()->getConnection();

try {
    $stmt = $db->query("SELECT phrase, author FROM phrases ORDER BY RAND() LIMIT 1");
    $randomPhrase = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Phrase fetch error: " . $e->getMessage());
    $randomPhrase = [
        'phrase' => 'No se pudo cargar una frase.',
        'author' => 'Desconocido'
    ];
}

// Render the template and pass the random phrase
echo $twig->render('your-template.html.twig', [
    'phrase' => $randomPhrase['phrase'],
    'author' => $randomPhrase['author']
]);
