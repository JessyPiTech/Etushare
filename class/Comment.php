<?php
require_once __DIR__ . "/Objet.php";

class Comment extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($input) {
        try {
            $query = "INSERT INTO annonce_commantaire ( annonce_id, user_id, commantaire_text, commantaire_date) 
                      VALUES (?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($query);

            $user_id = $input['user_id'];
            $annonce_id = $input['annonce_id'];
            $commantaire_text = $input['commantaire_text'];

            $stmt->bind_param('iis', $annonce_id, $user_id,  $commantaire_text);

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Commentaire ajouté avec succès",
                    'commantaire_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de l'ajout du commentaire: " . $stmt->error
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
            $query = "UPDATE annonce_commantaire 
                      SET commantaire_text = ?, commantaire_date = NOW() 
                      WHERE commantaire_id = ?";
            $stmt = $this->conn->prepare($query);

            $commantaire_text = $input['commantaire_text'];
            $commantaire_id = $input['commantaire_id'];

            $stmt->bind_param('si', $commantaire_text, $commantaire_id);

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Commentaire mis à jour avec succès"
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de la mise à jour du commentaire: " . $stmt->error
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