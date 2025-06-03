<?php
declare(strict_types=1);

class RecipeController
{
    private PDO $connection;
    private static ?self $instance = null;

    private function __construct()
    {
        $this->connection = DatabaseController::getInstance()->getConnection();
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    private static function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sube una nueva receta completa con sus ingredientes.
     *
     * @param array $post  Datos de $_POST
     * @param array $files Archivos de $_FILES
     * @return int|null    Devuelve el ID de la receta creada o null en caso de error
     */
    public static function recipeUpload(array $post, array $files): ?int {
        self::ensureSessionStarted();

        // ── Validar que el usuario esté logueado ──────────────────────────────
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            error_log('recipeUpload: usuario no autenticado');
            return null;
        }

        // ── Validaciones básicas del formulario ──────────────────────────────
        $required = ['name', 'description', 'difficulty', 'total_time'];
        foreach ($required as $field) {
            if (empty($post[$field])) {
                error_log("recipeUpload: falta $field");
                return null;
            }
        }
        if (empty($post['ingredients']) || !is_array($post['ingredients'])) {
            error_log('recipeUpload: sin ingredientes');
            return null;
        }

/*── 1) Mover foto principal de tmp → uploads/recipes ─────────────*/
$photoPath = null;
if (!empty($data['photo'])) {
    $tmpAbs  = $_SERVER['DOCUMENT_ROOT'].$data['photo'];             // /public/uploads/tmp/xxx.jpg
    $ext     = pathinfo($tmpAbs, PATHINFO_EXTENSION);
    $newName = uniqid('recipe_', true).'.'.$ext;
    $destRel = '/uploads/recipes/'.$newName;                          // ⇐ misma raíz que los pasos
    $destAbs = $_SERVER['DOCUMENT_ROOT'].$destRel;

    if (!is_dir(dirname($destAbs))) {
        mkdir(dirname($destAbs), 0755, true);
    }
    if (!rename($tmpAbs, $destAbs)) {
        throw new RuntimeException('No se pudo mover la imagen principal');
    }
    $photoPath = $destRel;                                           // ⇐ ruta que irá a la BD
}


        // ── Preparar datos ───────────────────────────────────────────────────
        $name        = trim($post['name']);
        $description = trim($post['description']);
        $difficulty  = $post['difficulty'];
        $totalTime   = (int)$post['total_time'];
        $styles      = $post['styles']    ?? [];
        $categories  = $post['category']  ?? [];
        $numSteps    = (int)($post['numSteps'] ?? 0);

        // Guarda estilos y categorías como JSON en la misma tabla.
        // Si usas tablas pivote, reemplaza esta parte por los inserts correspondientes.
        $stylesJson     = json_encode($styles, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $categoriesJson = json_encode($categories, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        // ── Inserción en BD ──────────────────────────────────────────────────
        $instance = self::getInstance();
        $pdo      = $instance->connection;

        try {
            $pdo->beginTransaction();

            // 1) Insertar receta principal
            $sql = "INSERT INTO recipes (
                        user_created, name, description, photo,
                        category, style, difficulty,
                        steps, total_time, challenge_id
                    ) VALUES (
                        :user_created, :name, :description, :photo,
                        :category, :style, :difficulty,
                        :steps, :total_time, :challenge_id
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_created'  => $userId,
                ':name'          => $name,
                ':description'   => $description,
                ':photo'         => $photoPath,
                ':category'      => $categoriesJson,
                ':style'         => $stylesJson,
                ':difficulty'    => $difficulty,
                ':steps'         => $numSteps,     // por ahora solo nº de pasos
                ':total_time'    => $totalTime,
                ':challenge_id'  => null,          // tu lógica de retos
            ]);

            $recipeId = (int)$pdo->lastInsertId();

            // 2) Insertar ingredientes
            $ingSql = "INSERT INTO recipe_ingredients (
                           recipe_id, name, quantity, unit
                       ) VALUES (:recipe_id, :name, :quantity, :unit)";
            $ingStmt = $pdo->prepare($ingSql);

            foreach ($post['ingredients'] as $row) {
                if (empty($row['name'])) continue;
                $ingStmt->execute([
                    ':recipe_id' => $recipeId,
                    ':name'      => trim($row['name']),
                    ':quantity'  => (float)($row['quantity'] ?? 0),
                    ':unit'      => $row['unit'] ?? '',
                ]);
            }

            $pdo->commit();
            return $recipeId;

        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log("recipeUpload: " . $e->getMessage());
            return null;
        }
    }
    
    public static function getRecipesByName(string $name): array {
        try {
            $instance = self::getInstance();
            $pdo = $instance->connection;

            $sql = "SELECT * FROM recipes WHERE name LIKE :name";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR); // ← comodines aquí
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al obtener recetas por nombre: " . $e->getMessage());
            return [];
        }
    }

    public static function getRecipesById(int $userId): array {
        try {
            $instance = self::getInstance();
            $pdo = $instance->connection;

            $sql = "SELECT * FROM recipes WHERE user_created = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Para múltiples recetas

        } catch (PDOException $e) {
            error_log("Error al obtener recetas por usuario: " . $e->getMessage());
            return []; // Mejor devolver array vacío
        }
    }


   public static function getRecipeById(int $id): ?array {
    try {
        $instance = self::getInstance();
        $pdo = $instance->connection;

        $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipe) return null;

        // Evita colisión con steps (int)
        if (isset($recipe['steps'])) {
            $recipe['num_steps'] = $recipe['steps'];
            unset($recipe['steps']);
        }

        // Ingredientes
        $stmtIng = $pdo->prepare("SELECT * FROM recipe_ingredients WHERE recipe_id = :id");
        $stmtIng->execute([':id' => $id]);
        $recipe['ingredients'] = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

        // Pasos
        $stmtSteps = $pdo->prepare("SELECT * FROM recipe_steps WHERE recipe_id = :id ORDER BY step_number ASC");
        $stmtSteps->execute([':id' => $id]);
        $recipe['steps'] = $stmtSteps->fetchAll(PDO::FETCH_ASSOC);

        return $recipe;

    } catch (PDOException $e) {
        error_log("Error al obtener receta por ID: " . $e->getMessage());
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
                        recipe_id = :recipe_id,
                        step_number = :step_number,
                        description = :description,
                        image = :image,
                        time = :time";

            // FALTA AÑADIR CONDICIÓN PARA OBTENER LA RECETA QUE ESTÁ EN PROCESO 
            $sql .= " WHERE recipe_id = :recipe_id AND step_number = :step_number";

            // Preparar sentencia
            $stmt = $pdo->prepare($sql);

            // Bind obligatorios
            $stmt->bindValue(':recipe_id',      $data['recipe_id'],     PDO::PARAM_STR);
            $stmt->bindValue(':step_number',    $data['step_number'],   PDO::PARAM_STR);
            $stmt->bindValue(':description',    $data['description'],   PDO::PARAM_STR);
            $stmt->bindValue(':image',          $data['image'],         $data['image'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':time',           $data['time'],          PDO::PARAM_STR);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error al subir los pasos de la receta: " . $e->getMessage());

            return false;

        }
        
    }

    public static function getAllRecipes(): array {
        try {
            $instance = self::getInstance();
            $pdo = $instance->connection;
            $stmt = $pdo->query("SELECT * FROM recipes");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todas las recetas: " . $e->getMessage());
            return [];
        }
    }


    /**
 * Guarda la receta y todos sus pasos.
 *
 * @param array $data  Datos de $_SESSION['recipe_draft']
 * @param array $steps Datos de $_SESSION['recipe_steps']
 * @return int         ID de la receta creada
 * @throws Exception   Propaga cualquier error para rollback
 */
public function saveFullRecipe(array $data, array $steps): int
{
    self::ensureSessionStarted();
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new RuntimeException('Usuario no autenticado');
    }

    $pdo = $this->connection;
    $pdo->beginTransaction();

    try {
        /* ── 1) Mover foto principal de tmp → uploads/recipes ───────────── */
        $photoPath = null;
        if (!empty($data['photo'])) {
            $tmpAbs   = $_SERVER['DOCUMENT_ROOT'].$data['photo'];          // /public/uploads/tmp/xxx.jpg
            $ext      = pathinfo($tmpAbs, PATHINFO_EXTENSION);
            $newName  = uniqid('recipe_', true).'.'.$ext;
            $destRel  = '/img/recipe/'.$newName;
            $destAbs  = $_SERVER['DOCUMENT_ROOT'].$destRel;
            if (!rename($tmpAbs, $destAbs)) {
                throw new RuntimeException('No se pudo mover la imagen principal');
            }
            $photoPath = '/img/recipe/' . $name;;
        }

        /* ── 2) Insertar en recipes ────────────────────────────────────── */
        $stmt = $pdo->prepare("
            INSERT INTO recipes (
                user_created, name, description, photo,
                category, style, difficulty,
                steps, total_time, challenge_id
            ) VALUES (
                :user_created, :name, :description, :photo,
                :category, :style, :difficulty,
                :steps, :total_time, :challenge_id
            )
        ");

        $stmt->execute([
            ':user_created' => $userId,
            ':name'         => $data['name'],
            ':description'  => $data['description'],
            ':photo'        => $photoPath,
            ':category'     => json_encode($data['categories'], JSON_UNESCAPED_UNICODE),
            ':style'        => json_encode($data['styles'],     JSON_UNESCAPED_UNICODE),
            ':difficulty'   => $data['difficulty'],
            ':steps'        => count($steps),
            ':total_time'   => $data['total_time'],
            ':challenge_id' => null
        ]);

        $recipeId = (int)$pdo->lastInsertId();

        /* ── 3) Insertar pasos ─────────────────────────────────────────── */
        $stmtStep = $pdo->prepare("
            INSERT INTO recipe_steps (
                recipe_id, step_number, description, image, time
            ) VALUES (
                :recipe_id, :step_number, :description, :image, :time
            )
        ");

        foreach ($steps as $i => $step) {
            /* Mover imagen del paso si existe */
            $stepImgRel = null;
            if (!empty($step['photo'])) {
                $tmpAbs  = $_SERVER['DOCUMENT_ROOT'].$step['photo'];
                $ext     = pathinfo($tmpAbs, PATHINFO_EXTENSION);
                $newName = uniqid("step{$i}_", true).'.'.$ext;
                $stepImgRel = '/uploads/recipes/steps/'.$newName;
                $destAbs    = $_SERVER['DOCUMENT_ROOT'].$stepImgRel;
                if (!is_dir(dirname($destAbs))) {
                    mkdir(dirname($destAbs), 0755, true);
                }
                rename($tmpAbs, $destAbs);
            }

            $seconds = ($step['minutes'] ?? 0) * 60 + ($step['seconds'] ?? 0);

            $stmtStep->execute([
                ':recipe_id'   => $recipeId,
                ':step_number' => $i + 1,
                ':description' => $step['description'],
                ':image'       => $stepImgRel,
                ':time'        => $seconds
            ]);
        }

        $pdo->commit();
        return $recipeId;

    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}


}


    /* 

    // ----------------------------------------------------------------------- //

    */



    
