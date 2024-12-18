<?php
require_once __DIR__ . "/Objet.php";  

class Friend extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }


    public function create($input) {
        try {
            $query = "INSERT INTO user_friends (user_id_1, user_id_2, user_friend_icon)
            VALUES (?, ?, ?)";

            $stmt = $this->conn->prepare($query);

            $user_id_1 = $input['user_id_1'];
            $user_id_2 = $input['user_id_2'];
            $user_friend_icon = $input['user_friend_icon'];

            $stmt->bindParam('iis',$user_id_1, $user_id_2, $user_friend_icon);
            $result = $stmt->execute();
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Amitié créée avec succès",
                    'user_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                $this->sendErrorResponse(500, "Échec de la création de l'amitié: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Erreur : " . $e->getMessage());
            return false;
        }
    }

    public function createFriendRequestWithNotification($sender_id, $receiver_id) {
        $query = "SELECT * FROM user_friend WHERE user_id_1 = ? AND user_id_2 = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $sender_id, $receiver_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false;
        }

        $createQuery = "INSERT INTO user_friend (user_id_1, user_id_2, user_friend_notif) VALUES (?, ?, 1)";
        $stmt = $this->conn->prepare($createQuery);
        $stmt->bind_param('ii', $sender_id, $receiver_id);
        $stmt->execute();

        return $stmt->insert_id;
    }
    
    public function update($input) {
        try {
            $query = "UPDATE user_friends SET user_id_1 = ?, user_id_2 = ?, 
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

    public function checkFriendStatus($current_user_id, $target_user_id) {
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
            return $row['user_friend_status'] == 1 ? 'friends' : 'pending';
        }
    
        return 'not_friends';
    }
}
?>
