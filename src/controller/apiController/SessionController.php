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

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        if ($instance->exist($data['username']) || $instance->existByEmail($data['email'])) {
            return "Username or email already exists.";
        }

        try {
            $sql = "INSERT INTO users (
                username, name, surname1, surname2, email, password,
                country, birthdate, genre, points, role, avatar, token, jwt
            ) VALUES (
                :username, :name, :surname1, :surname2, :email, :password,
                :country, :birthdate, :genre, 0, 'user', NULL, '', ''
            )";                      

            $stmt = $instance->connection->prepare($sql);
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':surname1', $data['surname1']);
            $stmt->bindValue(':surname2', $data['surname2']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindValue(':country', $data['country']);
            $stmt->bindValue(':birthdate', $data['birthdate']);
            $stmt->bindValue(':genre', $data['genre']);
            $stmt->execute();

            return true;

        } catch (PDOException $e) {
            error_log("Signup error: " . $e->getMessage());
            return false;
        }
    }

    public static function userLogin($username, $password) {
        $instance = self::getInstance();

        if (!$instance->exist($username)) {
            return false;
        }

        try {
            $sql = "SELECT id, username, email, role, password FROM users WHERE username = :username";
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
                self::createSecureCookie("jwt", self::createJWT(), time() + (86400 * 30), "/");
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
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'exp' => time() + (86400 * 30)
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

    private static function getSecretKey() {
        $dotenv = Dotenv\Dotenv::createImmutable("../");
        $dotenv->load();
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
            false,      // secure (false for localhost)
            true        // httponly
        );
    }
}
