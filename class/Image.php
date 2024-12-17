<?php
require_once __DIR__ . "/Objet.php";

class Image extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($input) {
        try {
            $query = "INSERT INTO image (annonce_id, image_url) 
                      VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            $annonce_id = $input['annonce_id'];
            $image_url = $input['image_url'];
           

            $stmt->bind_param('is', $annonce_id, $image_url);

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Image ajoutée avec succès",
                    'image_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de l'ajout de l'image: " . $stmt->error
                ]);
                return false;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => "Erreur : " . $e->getMessage()
            ]);
            return false;
        }
    }

    public function update($input) {
        try {
            $query = "UPDATE image 
                      SET image_url = ?
                      WHERE image_id = ?";
            $stmt = $this->conn->prepare($query);

            $image_url = $input['image_url'];
            $image_id = $input['image_id'];

            $stmt->bind_param('ssi', $image_url, $image_id);

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Image mise à jour avec succès"
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de la mise à jour de l'image: " . $stmt->error
                ]);
                return false;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => "Erreur : " . $e->getMessage()
            ]);
            return false;
        }
    }
}
?>