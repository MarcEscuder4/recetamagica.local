<?php
// Config CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

require_once "../../vendor/autoload.php";

$request = $_SERVER['REQUEST_URI'];

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

$chunks = explode("/", $request);

switch ($chunks[2] ?? '') {
    case '':
    case '/':
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit();

        // VDBFDBFDGSBFDBFGB
    case 'pajaros':
        if (!empty($chunks[4])) {
            $pajaroId = $chunks[3];
            if ($_SERVER["REQUEST_METHOD"] == "GET" && $chunks[4] == "avistamientos") {
                // Llama al método para obtener los datos de un pajaro específico
                echo AvistamientosController::getAvistamientosId($pajaroId, AvistamientosController::JSON);
            } else if ($_SERVER["REQUEST_METHOD"] == "GET" && $chunks[4] == "datos") {
                // Llama al método para obtener los datos de un pajaro específico
                echo DatosController::getDatosIdPajaro($pajaroId, DatosController::JSON);
            }
        } else if (!empty($chunks[3])) {
            $pajaroId = $chunks[3];
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener los datos de un pajaro específico
                echo PajaroController::getPajaroId($pajaroId, PajaroController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "PATCH") {
                // Llama al método para actualizar los datos de un pajaro
                echo PajaroController::patchPajaroIdUpdate($pajaroId, PajaroController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
                // Llama al método para eliminar un pajaro
                echo PajaroController::deletePajaroById($pajaroId);
            }
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener todos los pajaros
                echo PajaroController::getPajaro(PajaroController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Llama al método para crear un nuevo pajaro
                echo PajaroController::postNewPajaro(PajaroController::JSON);
            }
        }
        exit();
    case 'datos':
        if (!empty($chunks[3])) {
            $datosId = $chunks[3];
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener los datos de un pajaro específico
                echo DatosController::getDatosId($datosId, DatosController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "PATCH") {
                // Llama al método para actualizar los datos de un pajaro
                echo DatosController::patchDatosIdUpdate($datosId, DatosController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
                // Llama al método para eliminar un pajaro
                echo DatosController::deleteDatosById($datosId);
            }
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener todos los pajaros
                echo DatosController::getDatos(DatosController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Llama al método para crear un nuevo pajaro
                echo DatosController::postNewDatos(DatosController::JSON);
            }
        }
        exit();
    case 'lugares':
        if (!empty($chunks[4])) {
            $lugarId = $chunks[3];
            if ($_SERVER["REQUEST_METHOD"] == "GET" && $chunks[4] == "pajaros") {
                // Llama al método para obtener los pajaros de ese lugar
                echo AvistamientosController::getAvistamientosIdLugar($lugarId, AvistamientosController::JSON);
            }
        } else if (!empty($chunks[3])) {
            $lugarId = $chunks[3];
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener los datos de un pajaro específico
                echo LugaresController::getLugaresId($lugarId, LugaresController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "PATCH") {
                // Llama al método para actualizar los datos de un pajaro
                echo LugaresController::patchLugaresIdUpdate($lugarId, LugaresController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
                // Llama al método para eliminar un pajaro
                echo LugaresController::deleteLugaresById($lugarId);
            }
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener todos los pajaros
                echo LugaresController::getLugares(LugaresController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Llama al método para crear un nuevo pajaro
                echo LugaresController::postNewLugares(LugaresController::JSON);
            }
        }
        exit();
    case 'avistamientos':
        if (!empty($chunks[3])) {
            $avistamientoId = $chunks[3];

            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener los datos de un avistamiento específico
                echo AvistamientosController::getAvistamientosId($avistamientoId, AvistamientosController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "PATCH") {
                // Llama al método para actualizar los datos del avistamiento
                echo AvistamientosController::patchAvistamientosIdUpdate($avistamientoId, AvistamientosController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
                // Llama al método para eliminar un avistamiento por id_pajaro e id_lugar
                // Se asume que el id_pajaro y el id_lugar se pasan en la URL de la siguiente forma:
                // /avistamientos/{id_pajaro}/{id_lugar}
                $idPajaro = $chunks[3]; // id_pajaro
                echo AvistamientosController::deleteAvistamientosById($idPajaro);
            }
        } elseif(!empty($chunks[4])){
            if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
                // Llama al método para eliminar un avistamiento por id_pajaro e id_lugar
                // Se asume que el id_pajaro y el id_lugar se pasan en la URL de la siguiente forma:
                // /avistamientos/{id_pajaro}/{id_lugar}
                if (!empty($chunks[4])) {
                    $idPajaro = $chunks[3]; // id_pajaro
                    $idLugar = $chunks[4];  // id_lugar
                    echo AvistamientosController::deleteAvistamientosByIdPajaroIdLugar($idPajaro, $idLugar);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros de id_pajaro o id_lugar.']);
                }
            }
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                // Llama al método para obtener todos los avistamientos
                echo AvistamientosController::getAvistamientos(AvistamientosController::JSON);
            } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Llama al método para crear un nuevo avistamiento
                echo AvistamientosController::postNewAvistamientos(AvistamientosController::JSON);
            }
        }
        exit();
    case 'sesion':
        echo "<br>";
        print_r('<b>Datos sesión TEST:</b>');
        echo "<br>";
        echo "<b>¿Está logueado? </b>" . (SessionController::isLoggedIn() ? "<br>Sí" : "<br>No");
        echo "<br>";
        print_r('<b>Sesión: </b>');
        echo "<br>";
        ;
        print_r($_SESSION);
        echo "<br>";
        print_r('<b>Token en cookie jwt: </b>');
        echo $_COOKIE['jwt'];
        echo "<br>";
        exit();
    default:
        http_response_code(404);
        echo json_encode(["error" => "Not Found"]);
        exit();
}
