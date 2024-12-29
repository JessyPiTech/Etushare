<?php
require_once __DIR__ . "/Objet.php";  

class Friend extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }


    public function createRespond($input) {
        $action = $input['notification_action'];
        $friendRequestId = $input['notification_id'];
        
        $response = ['success' => false];
    
        try {
           
            $statusQuery = "SELECT user_friend_status 
                            FROM user_friend 
                            WHERE user_friend_id = ?";
            $statusStmt = $this->conn->prepare($statusQuery);
            $statusStmt->bind_param('i', $friendRequestId);
            $statusStmt->execute();
            $statusResult = $statusStmt->get_result()->fetch_assoc();
    
            if (!$statusResult) {
                throw new Exception("Demande d'ami introuvable.");
            }
    
            if ($statusResult['user_friend_status'] === 'accept') {
                throw new Exception("Cette demande a déjà été acceptée.");
            } elseif ($statusResult['user_friend_status'] === 'reject') {
                throw new Exception("Cette demande a déjà été refusée.");
            }
    
           
            if ($action === 'accept') {
                
                $query = "UPDATE user_friend 
                          SET user_friend_status = 'accept', user_friend_notif = 1 
                          WHERE user_friend_id = ?";
            } elseif ($action === 'reject') {
                $query = "UPDATE user_friend 
                          SET user_friend_status = 'reject', user_friend_notif = 1
                          WHERE user_friend_id = ?";
            } else {
                throw new Exception("Action non valide. Utilisez 'accept' ou 'reject'.");
            }
    
            $stmt =  $this->conn->prepare($query);
            $stmt->bind_param('i', $friendRequestId);
    
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                throw new Exception("Échec du traitement de la demande d'ami.");
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
    
        echo json_encode($response);
        exit;
    }


  

    

    public function createRequest($input) {
        try {
            $query = "SELECT user_id FROM user WHERE user_id IN (?, ?)";
            $stmt = $this->conn->prepare($query);
            $user_id_1 = $input['user_id_1'];
            $user_id_2 = $input['user_id_2'];
            $stmt->bind_param('ii', $user_id_1, $user_id_2);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows < 2) {
                $this->sendErrorResponse(404, "Un ou plusieurs utilisateurs n'existent pas.");
                return false;
            }
    
            $query = "INSERT INTO user_friend (user_id_1, user_id_2, user_friend_icon, user_friend_status, user_friend_notif) 
                      VALUES (?, ?, ?, 'pending', 0)";
            $stmt = $this->conn->prepare($query);
    
            $user_friend_icon = $input['user_friend_icon'] ?? "./upload/default.png";
    
            $stmt->bind_param('iis', $user_id_1, $user_id_2, $user_friend_icon);
            $result = $stmt->execute();
    
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Demande d'amitié créée avec succès",
                    'user_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de la création de demande d'amitié: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
            return false;
        }
    }

    
    
    public function update($input) {
        try {
            $query = "UPDATE user_friend SET user_id_1 = ?, user_id_2 = ?, 
            user_friend_icon = ? WHERE user_friend_id = ?";
        
            $stmt = $this->conn->prepare($query);
        
            $user_id_1 = $input['user_id_1'];
            $user_id_2 = $input['user_id_2'];
            $user_friend_icon = $input['user_friend_icon'];
            $user_friend_id = $input['user_friend_id'];
        
            $stmt->bindParam('iisi', $user_id_1, $user_id_2, $user_friend_icon , $user_friend_id);

            $result = $stmt->execute();
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Amitié mise à jour avec succès"
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de la mise à jour de l'amitié: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
            return false;
        }
    }

    public function getFriend($current_user_id, $target_user_id) {
        $query = "SELECT user_friend_status, user_friend_id
                  FROM user_friend 
                  WHERE (user_id_1 = ? AND user_id_2 = ?) 
                     OR (user_id_1 = ? AND user_id_2 = ?)";
                     
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $current_user_id, $target_user_id, $target_user_id, $current_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
    
            return [
                'status' => $row['user_friend_status'] == 'accept' ? 'friends' : 'pending',
                'user_friend_id' => $row['user_friend_id']
            ];
        }
    
        return [
            'status' => 'not_friends',
            'user_friend_id' => null
        ];
    }
    public function getFriendStatus($current_user_id, $target_user_id) {
        $query = "SELECT user_friend_status 
                  FROM user_friend 
                  WHERE (user_id_1 = ? AND user_id_2 = ?) 
                     OR (user_id_1 = ? AND user_id_2 = ?)";
                     
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $current_user_id, $target_user_id, $target_user_id, $current_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['user_friend_status'] == 'accept'? 'friends' : 'pending';
        }
    
        return 'not_friends';
    }
}
?>
