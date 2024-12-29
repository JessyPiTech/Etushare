<?php
require_once __DIR__ . "/Objet.php";

class Avis extends Objet {

    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($input) {
        try {
            $query = "INSERT INTO user_avis (annonce_id, user_id_1, user_id_2, avis_text, user_avis_note, user_avis_notif) 
                      VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                $this->sendErrorResponse(500, "Erreur lors de la préparation de la requête : " . $this->conn->error);
            }

            $user_id_1 = $input['user_id_1'];
            $user_id_2 = $input['user_id_2'];
            $annonce_id = $input['annonce_id'];
            $avis_text = $input['avis_text'];
            $user_avis_note = $input['user_avis_note'];

            $stmt->bind_param('iiisi', $annonce_id, $user_id_1, $user_id_2, $avis_text, $user_avis_note);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => "Avis ajouté avec succès",
                    'user_avis_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de l'ajout de l'avis : " . $stmt->error);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
        }
    }

    public function update($input) {
        try {
            $query = "UPDATE user_avis 
                      SET avis_text = ?, user_avis_note = ?
                      WHERE user_avis_id = ?";
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                $this->sendErrorResponse(500, "Erreur lors de la préparation de la requête : " . $this->conn->error);
            }

            $avis_text = $input['avis_text'];
            $user_avis_note = $input['user_avis_note'];
            $user_avis_id = $input['user_avis_id'];

            $stmt->bind_param('sii', $avis_text, $user_avis_note, $user_avis_id);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => "Avis mis à jour avec succès"
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de la mise à jour de l'avis : " . $stmt->error);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
        }
    }
}
?>