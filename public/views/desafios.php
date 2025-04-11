<?php 
// Carga Twig
$twig = require_once __DIR__ . '/../../config/twig.php';
// Renderitzar la plantilla
echo $twig->render('desafios.html', [ ]);