<?php
require_once __DIR__ . '/DatabaseController.php';

class RecipeController {

    public function createRecipe($data, $files) {
        $db = DatabaseController::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // 1. Insert main recipe
            $stmt = $db->prepare("INSERT INTO recipes (name, description, photo, category, style, difficulty, total_time, date_created, user_created)
                                  VALUES (:name, :description, :photo, :category, :style, :difficulty, :total_time, NOW(), :user_created)");

            $photoPath = $this->handleFileUpload($files['photo'], 'uploads/photos/');

            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':photo' => $photoPath,
                ':category' => $data['category'],
                ':style' => $data['style'], // for legacy, but also linking to style table below
                ':difficulty' => $data['difficulty'],
                ':total_time' => $data['total_time'],
                ':user_created' => $data['user_id']
            ]);

            $recipeId = $db->lastInsertId();

            // 2. Insert recipe category
            $stmt = $db->prepare("INSERT INTO recipe_category (recipe_id, name) VALUES (:recipe_id, :name)");
            $stmt->execute([
                ':recipe_id' => $recipeId,
                ':name' => $data['category']
            ]);

            // 3. Insert style (linked by style_id)
            $stmt = $db->prepare("SELECT id FROM styles WHERE name = :name");
            $stmt->execute([':name' => $data['style']]);
            $styleRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($styleRow) {
                $styleId = $styleRow['id'];
                $stmt = $db->prepare("INSERT INTO recipe_style (recipe_id, style_id) VALUES (:recipe_id, :style_id)");
                $stmt->execute([
                    ':recipe_id' => $recipeId,
                    ':style_id' => $styleId
                ]);
            }

            // 4. Insert ingredients
            foreach ($data['ingredients'] as $ingredient) {
                $ingredientName = $ingredient['name'];
                $quantity = $ingredient['quantity'];
                $unit = $ingredient['unit'];

                // Get or insert ingredient
                $stmt = $db->prepare("SELECT id FROM ingredients WHERE name = :name");
                $stmt->execute([':name' => $ingredientName]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $ingredientId = $row['id'];
                } else {
                    $stmt = $db->prepare("INSERT INTO ingredients (name) VALUES (:name)");
                    $stmt->execute([':name' => $ingredientName]);
                    $ingredientId = $db->lastInsertId();
                }

                // Insert into recipe_ingredient
                $stmt = $db->prepare("INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity, unit)
                                      VALUES (:recipe_id, :ingredient_id, :quantity, :unit)");
                $stmt->execute([
                    ':recipe_id' => $recipeId,
                    ':ingredient_id' => $ingredientId,
                    ':quantity' => $quantity,
                    ':unit' => $unit
                ]);
            }

            // 5. Insert steps
            foreach ($data['steps'] as $step) {
                $stepNumber = $step['step_number'];
                $description = $step['description'];
                $time = $step['time'];

                $imagePath = $this->handleFileUpload($step['image'], 'uploads/steps/');

                $stmt = $db->prepare("INSERT INTO recipe_steps (recipe_id, step_number, description, image, time)
                                      VALUES (:recipe_id, :step_number, :description, :image, :time)");
                $stmt->execute([
                    ':recipe_id' => $recipeId,
                    ':step_number' => $stepNumber,
                    ':description' => $description,
                    ':image' => $imagePath,
                    ':time' => $time
                ]);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Recipe created successfully.'];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function handleFileUpload($file, $targetDir) {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $targetDir . $filename;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        move_uploaded_file($file['tmp_name'], $targetPath);
        return $targetPath;
    }
}
