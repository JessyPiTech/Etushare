<?php
require_once __DIR__ . "/Objet.php";

class Participant extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }


    public function createRequest($input) {
        try {
            $query = "SELECT user_id FROM annonce WHERE annonce_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $input['annonce_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $annonce_owner = $result->fetch_assoc()['user_id'];
    
            $query = "INSERT INTO annonce_participant (user_id, annonce_id, annonce_participant_status, annonce_participant_notif) 
                      VALUES (?, ?, 'pending', 0)";
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
                sendErrorResponse(500, "Échec de l'ajout de la participation: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            sendErrorResponse(500, "Erreur : " . $e->getMessage());
            return false;
        }
    }


    public function creatRespond($conn, $input) {
        $action = $input['notification_action'];
        $participantId = $input['notification_id'];
        $annonce_id = $input['annonce_id'];
        $sender_id = $input['user_id'];
        $participant_id = $input['participant_id'];
        
        $response = ['success' => false];
    
        try {
            if ($action === 'accept') {
                $valueQuery = "SELECT a.annonce_value
                             FROM annonce a 
                             WHERE a.annonce_id = ?";
                $valueStmt = $conn->prepare($valueQuery);
                $valueStmt->bind_param('i', $annonce_id);
                $valueStmt->execute();
                $annonceResult = $valueStmt->get_result()->fetch_assoc();

                if (!$annonceResult) {
                    throw new Exception("Annonce introuvable");
                }
                $amount = $annonceResult['annonce_value'];
                $participantQuery = "SELECT annonce_participant_status 
                                   FROM annonce_participant 
                                   WHERE annonce_participant_id = ?";
                $participantStmt = $conn->prepare($participantQuery);
                $participantStmt->bind_param('i', $participantId);
                $participantStmt->execute();
                $participantResult = $participantStmt->get_result()->fetch_assoc();

                if ($participantResult['annonce_participant_status'] === 'accept') {
                    throw new Exception("Cette participation a déjà été acceptée");
                }

                $user = new User($conn);
                $userBalance = $user->getUserEtucoin($sender_id);

                if ($userBalance < $amount) {
                    throw new Exception("Solde insuffisant pour accepter cette participation");
                }
                $transfere = new Transfere($conn);
                $transferResult = $transfere->createTransfer($conn, $annonce_id, $sender_id, $participant_id, $amount);

                if (!$transferResult) {
                    throw new Exception("Échec de la création du transfert");
                }

                $query = "UPDATE annonce_participant 
                         SET annonce_participant_status = 'accept',
                            annonce_participant_notif = 1
                         WHERE annonce_participant_id = ?";
            } else {
                $query = "UPDATE annonce_participant 
                         SET annonce_participant_status = 'reject',
                         annonce_participant_notif = 1
                         WHERE annonce_participant_id = ?";
            }
    
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $participantId);
    
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                throw new Exception('Échec de la mise à jour du statut de participation');
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = $e->getMessage();
        }
    
        echo json_encode($response);
        exit;
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