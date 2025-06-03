<?php

declare(strict_types=1);

class RecipeController {

    private $connection;
    private static ? self $instance = null;

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

    // INICI RECIPE CONTROLLER //
    public static function recipeUpload($data) {
        $instance = self::getInstance();

        // FALTA OBTENER EL id DEL USUARIO
        try {
            $sql = "INSERT INTO recipes (
                        name, description, photo, 
                        category, style, difficulty, 
                        steps, total_time, challenge_id
                    ) VALUES (
                        :name, :description, :photo, 
                        :category, :style, :difficulty,
                        :steps, :total_time, :challenge_id
                    )";

            $stmt = $instance->connection->prepare($sql);
            $stmt->bindValue(':name',           $data['name']);
            $stmt->bindValue(':description',    $data['description']);
            $stmt->bindValue(':photo',          $data['photo'], $data['photo'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':category',       $data['category']);
            $stmt->bindValue(':style',          $data['style']);
            $stmt->bindValue(':difficulty',     $data['difficulty']);
            $stmt->bindValue(':steps',          $data['steps']);
            $stmt->bindValue(':total_time',     $data['total_time']);
            $stmt->bindValue(':challenge_id',   $data['challenge_id']);
            $stmt->execute();

            return true;

        } catch (PDOException $e) {
            error_log("Error al subir la receta: " . $e->getMessage());

            return false;

        }

    }

    public static function getRecipesById($userId) {
        try {
            $sql = "SELECT * FROM recipes 
                    JOIN users ON recipes.user_created = users.id
                    WHERE user_created = :id";

            $instance = self::getInstance();
            // FALTA preparar el statement:
            // $stmt = $pdo->prepare($sql);
            
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get recipe by userId error: " . $e->getMessage());
            return null;
        }
    }


    public static function updateRecipesById(int $userId, array $data): bool {
        try {
            $instance = self::getInstance();
            $pdo = $instance->connection;

            // CONSULTA BASE
            $sql = "UPDATE recipes SET 
                        name = :name,
                        description = :description,
                        photo = :photo,
                        category = :category,
                        style = :style,
                        difficulty = :difficulty,
                        steps = :steps,
                        total_time = :total_time,
                        challenge_id = :challenge_id";

            /*
            if (!empty($data['avatar'])) {
                $sql .= ", avatar = :avatar";
            }
            */

            $sql .= " WHERE user_created = :id";

            // Preparar sentencia
            $stmt = $pdo->prepare($sql);

            // Bind obligatorios
            $stmt->bindValue(':name',           $data['name'],          PDO::PARAM_STR);
            $stmt->bindValue(':description',    $data['description'],   PDO::PARAM_STR);
            $stmt->bindValue(':photo',          $data['photo'],         PDO::PARAM_STR);
            $stmt->bindValue(':category',       $data['category'],      PDO::PARAM_STR);
            $stmt->bindValue(':style',          $data['style'],         PDO::PARAM_STR);
            $stmt->bindValue(':difficulty',     $data['difficulty'],    PDO::PARAM_STR);
            $stmt->bindValue(':steps',          $data['steps'],         PDO::PARAM_STR);
            $stmt->bindValue(':total_time',     $data['total_time'],    PDO::PARAM_STR);
            $stmt->bindValue(':challenge_id',   $data['challenge_id'],  PDO::PARAM_STR);

            $stmt->bindValue(':id',             $userId,                PDO::PARAM_INT);

            /* // EJEMPLO PARAMETRO OPCIONAL
            if (!empty($data['avatar'])) {
                $stmt->bindValue(':avatar', $data['avatar'], PDO::PARAM_STR);
            }
            */

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error al subir la receta: " . $e->getMessage());

            return false;

        }
        
    }

    public static function updateRecipesStepsById(int $userId, array $data): bool {
        try {
            $instance = self::getInstance();
            $pdo = $instance->connection;

            // CONSULTA SEGONA PART FOR, FOR EACH STEP DO
            $sql = "UPDATE recipe_steps SET 
                        id = :id,
                        recipe_id = :recipe_id,
                        step_number = :step_number,
                        description = :description,
                        image = :image,
                        time = :time";

            // FALTA AÑADIR CONDICIÓN PARA OBTENER LA RECETA QUE ESTÁ EN PROCESO 
            $sql .= " WHERE user_created = :id";

            // Preparar sentencia
            $stmt = $pdo->prepare($sql);

            // Bind obligatorios
            $stmt->bindValue(':recipe_id',      $data['recipe_id'],     PDO::PARAM_STR);
            $stmt->bindValue(':step_number',    $data['step_number'],   PDO::PARAM_STR);
            $stmt->bindValue(':description',    $data['description'],   PDO::PARAM_STR);
            $stmt->bindValue(':image',          $data['image'],         $data['image'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':time',           $data['time'],          PDO::PARAM_STR);

            $stmt->bindValue(':id',             $userId,                PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error al subir los pasos de la receta: " . $e->getMessage());

            return false;

        }
        
    }
}


    /* 

    // ----------------------------------------------------------------------- //

    */



    
