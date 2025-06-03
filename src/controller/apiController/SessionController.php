<?php

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

    private static function ensureSessionStarted() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function userSignUp($data) {
        $instance = self::getInstance();

        // Validaciones
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Formato de e-mail inválido';
        }
        if ($instance->exist($data['username']) || $instance->existByEmail($data['email'])) {
            return 'El nombre de usuario o e-mail ya existe';
        }

        try {
            $sql = "INSERT INTO users (
                        username, name, surname1, surname2, email, password,
                        country, lang, birthdate, genre, points, role, avatar, token, jwt
                    ) VALUES (
                        :username, :name, :surname1, :surname2, :email, :password,
                        :country, :lang, :birthdate, :genre, 0, 'user', :avatar, '', ''
                    )";

            $stmt = $instance->connection->prepare($sql);
            $stmt->bindValue(':username',  $data['username']);
            $stmt->bindValue(':name',      $data['name']);
            $stmt->bindValue(':surname1',  $data['surname1']);
            $stmt->bindValue(':surname2',  $data['surname2']);
            $stmt->bindValue(':email',     $data['email']);
            $stmt->bindValue(':password',  password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindValue(':country',   $data['country']);
            $stmt->bindValue(':lang',      $data['lang']);
            $stmt->bindValue(':birthdate', $data['birthdate']);
            $stmt->bindValue(':genre',     $data['genre']);
            $stmt->bindValue(':avatar',    $data['avatar'], $data['avatar'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->execute();

            return true;

        } catch (PDOException $e) {
            error_log("Error de registro: " . $e->getMessage());
            return false;
        }
    }

    public static function userLogin($username, $password) {
        $instance = self::getInstance();
        $userIdentifier = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        try {
            $sql = "SELECT id, username, email, role, password FROM users WHERE $userIdentifier = :username";
            $stmt = $instance->connection->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if ($user && password_verify($password, $user->password)) {
                self::ensureSessionStarted();
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;
                $_SESSION['user_role'] = $user->role;

                self::generateSessionToken($user);
                self::createSecureCookie("jwt", self::createJWT(), time() + (86400 * 30));

                return true;
            }

            return false;

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public static function userLogout() {
        self::ensureSessionStarted();
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $stmt = self::getInstance()->connection->prepare("UPDATE users SET token = '', jwt = '' WHERE id = :id");
            $stmt->bindValue(':id', $userId);
            $stmt->execute();
        }

        $_SESSION = [];
        session_unset();
        session_destroy();

        setcookie("token", "", time() - 3600, "/");
        setcookie("jwt", "", time() - 3600, "/");
    }

    private static function generateSessionToken($user) {
        $token = bin2hex(random_bytes(16));
        setcookie("token", $token, time() + (86400 * 30), "/");

        $stmt = self::getInstance()->connection->prepare("UPDATE users SET token = :token WHERE id = :id");
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':id', $user->id);
        $stmt->execute();
    }

    public static function createJWT() {
        self::ensureSessionStarted();

        if (!isset($_SESSION['user_id'])) return null;

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'user_id'  => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'exp'      => time() + (86400 * 30)
        ];

        $jwt = self::generateJWT($header, $payload, self::getSecretKey());

        $stmt = self::getInstance()->connection->prepare("UPDATE users SET jwt = :jwt WHERE id = :id");
        $stmt->bindValue(':jwt', $jwt);
        $stmt->bindValue(':id', $_SESSION['user_id']);
        $stmt->execute();

        return $jwt;
    }

    public static function verifyTokenCookie() {
        if (!isset($_COOKIE['token'])) return false;

        $token = $_COOKIE['token'];
        $stmt = self::getInstance()->connection->prepare("SELECT id, username FROM users WHERE token = :token");
        $stmt->bindValue(":token", $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if ($user) {
            self::ensureSessionStarted();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            return true;
        }

        setcookie("token", "", time() - 3600, "/");
        return false;
    }

    public static function verifyJWTCookie() {
        if (!isset($_COOKIE['jwt'])) return false;

        $jwt = $_COOKIE['jwt'];
        $secret = self::getSecretKey();

        if (!self::verifyJWT($jwt, $secret)) {
            setcookie("jwt", "", time() - 3600, "/");
            return false;
        }

        $stmt = self::getInstance()->connection->prepare("SELECT id, username FROM users WHERE jwt = :jwt");
        $stmt->bindValue(":jwt", $jwt);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if ($user) {
            self::ensureSessionStarted();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            return true;
        }

        setcookie("jwt", "", time() - 3600, "/");
        return false;
    }

    public static function isLoggedIn() {
        self::ensureSessionStarted();
        return !empty($_SESSION['user_id']) || self::verifyJWTCookie() || self::verifyTokenCookie();
    }

    public static function exist($username) {
        try {
            $stmt = self::getInstance()->connection->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Exist check error: " . $e->getMessage());
            return false;
        }
    }

    public static function existByEmail($email) {
        try {
            $stmt = self::getInstance()->connection->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Exist email error: " . $e->getMessage());
            return false;
        }
    }

    public static function getUserById($userId) {
        try {
            $stmt = self::getInstance()->connection->prepare("
                SELECT id, username, name, surname1, surname2, email, country, lang, birthdate, genre, role, points, avatar
                FROM users
                WHERE id = :id
            ");
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza los datos básicos de un usuario por su ID
     *
     * @param int $userId
     * @param array $data [name, surname1, surname2, birthdate, genre, country, avatar]
     * @return bool
     */
    public static function updateUserById(int $userId, array $data): bool {
        try {
            $instance = self::getInstance();
            $pdo = $instance->connection;

            // Preparar consulta base
            $sql = "UPDATE users SET 
                        name = :name,
                        surname1 = :surname1,
                        surname2 = :surname2,
                        birthdate = :birthdate,
                        genre = :genre,
                        country = :country,
                        lang = :lang";

            // Si hay avatar, lo añadimos a la consulta
            if (!empty($data['avatar'])) {
                $sql .= ", avatar = :avatar";
            }

            $sql .= " WHERE id = :id";

            // Preparar sentencia
            $stmt = $pdo->prepare($sql);

            // Bind obligatorios
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':surname1', $data['surname1'], PDO::PARAM_STR);
            $stmt->bindValue(':surname2', $data['surname2'], PDO::PARAM_STR);
            $stmt->bindValue(':birthdate', $data['birthdate'], PDO::PARAM_STR);
            $stmt->bindValue(':genre', $data['genre'], PDO::PARAM_STR);
            $stmt->bindValue(':country', $data['country'], PDO::PARAM_STR);
            $stmt->bindValue(':lang', $data['lang'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

            // Bind opcional del avatar
            if (!empty($data['avatar'])) {
                $stmt->bindValue(':avatar', $data['avatar'], PDO::PARAM_STR);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return false;
        }
    }

    private static function getSecretKey(): string {
        if (!isset($_ENV['JWT_SECRET_KEY']) || $_ENV['JWT_SECRET_KEY'] === '') {
            throw new RuntimeException('JWT_SECRET_KEY no definido en .env');
        }
        return $_ENV['JWT_SECRET_KEY'];
    }

    private static function generateJWT($header, $payload, $secret) {
        $headerEncoded = self::base64URLEncode(json_encode($header));
        $payloadEncoded = self::base64URLEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64URLEncode($signature);
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    private static function verifyJWT($jwt, $secret) {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
        $expectedSig = self::base64URLEncode(hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true));

        if ($signatureEncoded !== $expectedSig) return false;

        $payload = json_decode(base64_decode($payloadEncoded), true);
        return isset($payload['exp']) && $payload['exp'] > time();
    }

    private static function base64URLEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public static function createSecureCookie($name, $value, $expires, $path = "/") {
        setcookie(
            $name,
            $value,
            $expires,
            $path,
            "",         // domain
            false,      // secure (set to true if HTTPS)
            true        // httponly
        );
    }
}
