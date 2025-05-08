<?php
// Config CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

//print_r(__DIR__);
require_once "../../vendor/autoload.php";
$viewDir = '/views/';

$request = $_SERVER['REQUEST_URI'];

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}


if (preg_match('/^\/api\/arboles(\/)?$/', $request, $matches)) {
    echo ArbolController::getArboles(ArbolController::JSON);
} else if (preg_match('/^\/api\/arboles\/(\d+)$/', $request, $matches)) {

    //print_r(__DIR__ . "/.." . $viewDir);
    $idArbol = $matches[1]; // Obtienes el ID del árbol
    echo ArbolController::getArbolId($idArbol, ArbolController::JSON);

    //require __DIR__ . "/.." . $viewDir . 'tree.php';
}