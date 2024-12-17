<?php
require_once __DIR__ . "/Objet.php";

class Like extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($input) {
        try {
            $query = "INSERT INTO annonce_like (user_id, annonce_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);

            $user_id = $input['user_id'];
            $annonce_id = $input['annonce_id'];

            $stmt->bind_param('ii', $user_id, $annonce_id);

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Like ajouté avec succès",
                    'like_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de l'ajout du like: " . $stmt->error
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
        // Pour les likes, il n'y a généralement pas de mise à jour
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "Mise à jour de like non supportée"
        ]);
        return false;
    }
    public function countLikesForAnnonce($annonce_id) {
        $query = "SELECT COUNT(*) as like_count FROM annonce_like WHERE annonce_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $annonce_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['like_count'];
    }
}
?>