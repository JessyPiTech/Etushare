<?php
require_once __DIR__ . "/Objet.php";

class Participant extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function sendErrorResponse($code, $message) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }

    public function create($input) {
        try {
            $query = "SELECT user_id FROM annonce WHERE annonce_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $input['annonce_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $annonce_owner = $result->fetch_assoc()['user_id'];
    
            $query = "INSERT INTO annonce_participant (user_id, annonce_id, annonce_participant_status, annonce_participant_notif) 
                      VALUES (?, ?, 'pending', 1)";
            $stmt = $this->conn->prepare($query);
    
            $user_id = $input['user_id'];
            $annonce_id = $input['annonce_id'];
    
            $stmt->bind_param('ii', $user_id, $annonce_id);
    
            $result = $stmt->execute();
    
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Participation ajoutée avec succès",
                    'annonce_participant_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de l'ajout de la participation: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
            return false;
        }
    }

    public function addParticipantWithNotification($sender_id, $annonce_id, $receiver_id) {
        $createQuery = "INSERT INTO annonce_participant (user_id, annonce_id, annonce_participant_user_id_2, annonce_participant_status, annonce_participant_notif) VALUES (?, ?, ?, 'pending', 1)";
        $stmt = $this->conn->prepare($createQuery);
        $stmt->bind_param('iii', $sender_id, $annonce_id, $receiver_id);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function update($input) {
        try {
            $query = "UPDATE annonce_participant 
                      SET annonce_participant_status = ?, 
                          annonce_participant_notif = 0 
                      WHERE annonce_participant_id = ?";
            $stmt = $this->conn->prepare($query);
    
            $annonce_participant_status = $input['notification_action'] === 'accept' ? 'confirm' : 'rejected';
            $annonce_participant_id = $input['notification_id'];
    
            $stmt->bind_param('si', $annonce_participant_status, $annonce_participant_id);
    
            $result = $stmt->execute();
    
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Participation mise à jour avec succès"
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de la mise à jour de la participation: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
            return false;
        }
    }

    public function countParticipantsForAnnonce($annonce_id) {
        $query = "SELECT COUNT(*) as participant_count FROM annonce_participant WHERE annonce_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $annonce_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['participant_count'];
    }
}
?>