<?php

// Incluir el archivo de conexión
//$pdo = require_once __DIR__ . '/../../config/conectorDatabase.php';
// user: mescuder 	email: mescuder@elpuig.xeill.net 	password: password

class SessionController {

    private $connection;

    private static $instance = null;

    private function __construct() {
        $dbController = DatabaseController::getInstance();
        $this->connection = $dbController->getConnection();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function userSignUp($username, $email, $password) {

        if (($instance = self::getInstance())->exist($username, $email)) {
            echo "Username or email already exist";
            return;
        } else {
            try  {

                $sql = "INSERT INTO User
                        (username, email, password, token, jwt) VALUES (:username, :email, :password, :token, :jwt)";
            
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $statement = ($instance = self::getInstance())->connection->prepare($sql);


                $statement->bindValue(':username', $username);
                $statement->bindValue(':email', $email);
                $statement->bindValue(':password', $hashed_password);
                $statement->bindValue(':token', "");
                $statement->bindValue(':jwt', "");
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();

    
                //echo "Usuario registrado exitosamente";
                return true;
    
              } catch(PDOException $error) {
                  // echo $sql . "<br>" . $error->getMessage();
                  return false;
              }
        }
    }

    private static function ensureSessionStarted() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function userLogin($username, $password){

        if (!($instance = self::getInstance())->exist($username)) {
            //echo "Username does not exists";
            return false;
        } else {
            try {
       
                $sql = "SELECT id, email, role, password FROM User WHERE username = :username";

                $statement = ($instance = self::getInstance())->connection->prepare($sql);
                $statement->bindValue(':username', $username);
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();
    
                $user = $statement->fetch();
    
                if ($user && password_verify($password, $user->password)) {
                    // La autenticación es correcta
                    session_start();
                    
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $user->email;
                    // Redirigir al usuario a su perfil o a la página de inicio
                    // header("Location: perfil.php");

                    // Creamos un token de session
                    self::generateSessionToken($user);
                    
                    // Creamos y guardamos el token jwt en una cookie segura
                    SessionController::createSecureCookie("jwt", self::createJWT(), time() + (86400 * 30), "/"); // 30 días
                    return true;

                } else {
                    // Usuario o contraseña incorrectos
                    //echo "Nombre de usuario o contraseña incorrectos.";
                    return false;
                }
    
              } catch(PDOException $error) {
                  echo $sql . "<br>" . $error->getMessage();
                  return false;
              }
        }
    }

    public static function userLogout() {
        session_start();
        session_destroy();
        setcookie("token", "", time() - 3600, "/"); // Eliminar cookie
        setcookie("jwt", "", time() - 3600, "/"); // Eliminar cookie
    }

    private static function generateSessionToken($user) {
           
            if (isset($_SESSION['user_id'])) {
                // Genera un token de sesión para recordar al usuario
                $token = bin2hex(random_bytes(16));
                setcookie("token", $token, time() + (86400 * 30), "/"); // 30 días

                // Guarda el token en la base de datos
                $statement = ($instance = self::getInstance())->connection->prepare("UPDATE User SET token = :token WHERE id = :id");
                $statement->bindValue(':token', $token);
                $statement->bindValue(':id', $user->id);
                
                $statement->execute();
                return true;
            } else {
                return false;
            }
    }

    public static function exist($username, $email = null) {

        if ($email === null) {

            try  {
       
                $sql = "SELECT * 
                        FROM User
                        WHERE username = :username";
            
                $statement = ($instance = self::getInstance())->connection->prepare($sql);
                $statement->bindValue(':username', $username);
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();
    
                $result = $statement->fetch();
                return !$result ? false : true;
    
              } catch(PDOException $error) {
                  echo $sql . "<br>" . $error->getMessage();
            }

        } else {

            try  {
       
                $sql = "SELECT * 
                        FROM User
                        WHERE username = :username AND email = :email";
            
                $statement = ($instance = self::getInstance())->connection->prepare($sql);
                $statement->bindValue(':username', $username);
                $statement->bindValue(':email', $email);
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();
    
                $result = $statement->fetch();
                return !$result ? false : true;
    
              } catch(PDOException $error) {
                  echo $sql . "<br>" . $error->getMessage();
              }
        }
    }

    //TODO cookie jwt
    public static function verifyTokenCookie() {

        if (isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
            
            $statement = ($instance = self::getInstance())->connection->prepare("SELECT id, username FROM User WHERE token = :token");
            $statement->bindValue(":token", $token);
            $statement->setFetchMode(PDO::FETCH_OBJ);
            $statement->execute();
            $user = $statement->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                return true;
            } else {
                // Token inválido
                setcookie("token", "", time() - 3600, "/"); // Eliminar cookie
                // header("Location: login.php");
                // exit();
                echo "Token inválido!";
                return false;
            }
        } else {
            return false;
        }
    }

    public static function verifyJWTCookie() {

        if (isset($_COOKIE['jwt'])) {
            $jwt = $_COOKIE['jwt'];

            $secret = self::getSecretKey();
        if (!self::verifyJWT($jwt, $secret)) {
            setcookie("jwt", "", time() - 3600, "/");
            return false;
        }

            
            $statement = ($instance = self::getInstance())->connection->prepare("SELECT id, username FROM User WHERE jwt = :jwt");
            $statement->bindValue(":jwt", $jwt);
            $statement->setFetchMode(PDO::FETCH_OBJ);
            $statement->execute();
            $user = $statement->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                return true;
            } else {
                // Token inválido
                setcookie("jwt", "", time() - 3600, "/"); // Eliminar cookie
                echo "JWT inválido!";
                return false;
            }
        } else {
            return false;
        }
    }

    public static function isLoggedIn() {
        self::ensureSessionStarted();
        if (!empty($_SESSION['user_id'])) {
            return true;
        }
    
        return self::verifyJWTCookie() || self::verifyTokenCookie();
    }
    

    private static function createJWT() {
        if (isset($_SESSION['user_id'])) {
            // Datos para el JWT
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];

            $payload = [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'exp' => time() + (86400 * 30) // Expira en 30 días
            ];

            // Generar el JWT
            $jwt = self::generateJWT($header, $payload, self::getSecretKey());
            // echo "JWT generado: " . $jwt . "\n";

            // Guarda el token en la base de datos
            $statement = ($instance = self::getInstance())->connection->prepare("UPDATE User SET jwt = :jwt WHERE id = :id");
            $statement->bindValue(':jwt', $jwt);
            $statement->bindValue(':id', $_SESSION['user_id']);
            $statement->execute();

            return $jwt;
        } else {
            return null;
        }
    }

    public static function getSecretKey() {
        // .env file placed in the root directory
        $dotenv = Dotenv\Dotenv::createImmutable("../");
        $dotenv->load();
        
        return $_ENV['JWT_SECRET_KEY'];
    }

    // Función para generar el JWT
    public static function generateJWT($header, $payload, $secret_key) {
        // Codificar en Base64 URL el header y el payload
        $header_encoded = self::base64URLEncode(json_encode($header));
        $payload_encoded = self::base64URLEncode(json_encode($payload));
        
        // Crear la firma
        $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret_key, true);
        $signature_encoded = self::base64URLEncode($signature);
        
        // Combinar todos los elementos en el JWT
        return "$header_encoded.$payload_encoded.$signature_encoded";
    }

    // Función para verificar el JWT
    public static function verifyJWT($jwt, $secret_key) {
        list($header_encoded, $payload_encoded, $signature_encoded) = explode('.', $jwt);
        $signature = self::base64URLEncode(hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret_key, true));

        if ($signature !== $signature_encoded) {
            return false;
        }

        // Decodificar el payload para verificar el tiempo de expiración
        $payload = json_decode(base64_decode($payload_encoded), true);
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token expirado
        }
        return true; // Token válido
    }

    // Función auxiliar para codificar en Base64 URL seguro
    public static function base64URLEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public static function createSecureCookie($cookieName, $cookieValue, $expirationTime, $path) {
        // Dominio (puede ser tu dominio o dejarlo vacío)
        $domain = '';

        // Secure: true para enviar solo sobre HTTPS
        $secure = false;

        // HttpOnly: true para evitar acceso desde JavaScript
        $httponly = true;

        // Configurar la cookie
        setcookie(
            $cookieName,       // Nombre de la cookie
            $cookieValue,      // Valor de la cookie
            $expirationTime,   // Tiempo de expiración
            $path,             // Ruta
            $domain,           // Dominio
            $secure,           // Secure
            $httponly          // HttpOnly
        );
    }

}