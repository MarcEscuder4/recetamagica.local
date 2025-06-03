<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_NOTICE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__.'/../../config/twig.php';

$twigVars = [
    'error'            => $_SESSION['error']           ?? '',
    'success_message'  => $_SESSION['success_message'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Validaciones rápidas
    $dificultades = ['Fácil','Media','Difícil'];
    if (
        empty($_POST['name']) ||
        !in_array($_POST['difficulty'], $dificultades, true) ||
        empty($_POST['ingredients'])
    ) {
        $_SESSION['error'] = 'Formulario incompleto o valores no válidos.';
        header('Location: /crear-receta/formulario-1');
        exit;
    }

    // 2. Subir (o al menos validar) la foto
    $photoPath = null;
    if (!empty($_FILES['photo']['name'])) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir la imagen.';
            header('Location: /crear-receta/formulario-1');
            exit;
        }

        $extOk = ['jpg','jpeg','png','gif','webp'];
        $ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extOk, true)) {
            $_SESSION['error'] = 'Formato de imagen no permitido.';
            header('Location: /crear-receta/formulario-1');
            exit;
        }

        // Guardamos la foto en /public/uploads/tmp/ para no “comprometer” nada aún
        $tmpDir = __DIR__.'/../../public/uploads/tmp/';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

        $safeName  = uniqid('draft_', true).".".$ext;
        $fullTmp   = $tmpDir.$safeName;

        move_uploaded_file($_FILES['photo']['tmp_name'], $fullTmp);
        $photoPath = '/uploads/tmp/'.$safeName; // ruta relativa para luego
    }

    // 3. Construimos el borrador y lo guardamos en sesión
    $_SESSION['recipe_draft'] = [
        'name'        => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'photo'       => $photoPath,
        'categories'  => $_POST['category']  ?? [],
        'styles'      => $_POST['styles']    ?? [],
        'difficulty'  => $_POST['difficulty'],
        'numSteps'    => (int)($_POST['numSteps'] ?? 0),
        'total_time'  => (int)($_POST['total_time'] ?? 0),
        'ingredients' => $_POST['ingredients'] ?? [],
        // challenge_id 
    ];

    header('Location: /crear-receta/formulario-2');
    exit;
}

/* --- GET: pinta la plantilla --- */
echo $twig->render('steps.html');

unset($_SESSION['error'], $_SESSION['success_message']);
