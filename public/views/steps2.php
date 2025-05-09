<?php 
// Cargar Twig y renderizar la plantilla
$twig = require_once __DIR__ . '/../../config/twig.php';
echo $twig->render('steps2.html', [ ]);