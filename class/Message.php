
<?php
require_once __DIR__ . "/Objet.php";

class Message extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function getFriendshipId($user_id_1, $user_id_2) {
        $query = "SELECT user_friend_id FROM user_friend 
                 WHERE (user_id_1 = ? AND user_id_2 = ?) 
                 OR (user_id_1 = ? AND user_id_2 = ?)
                 AND user_friend_status = 'accept'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iiii', $user_id_1, $user_id_2, $user_id_2, $user_id_1);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['user_friend_id'];
        }
        return null;
    }

    public function create($input) {
        try {
            $user_friend_id = $this->getFriendshipId($input['user_id'], $input['target_user_id']);
            
            if (!$user_friend_id) {
                throw new Exception("No valid friendship found between users");
            }

            $query = "INSERT INTO user_friend_message 
                      (user_friend_id, user_id, user_friend_message_text, user_friend_message_notif) 
                      VALUES (?, ?, ?,  1)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('iis', $user_friend_id, $input['user_id'], $input['message_text']);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => "Message sent successfully",
                    'message_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                throw new Exception("Failed to send message: " . $stmt->error);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Error: " . $e->getMessage());
        }
    }

    public function getMessages($user_id_1, $user_id_2) {
        try {
            $user_friend_id = $this->getFriendshipId($user_id_1, $user_id_2);
            
            if (!$user_friend_id) {
                throw new Exception("No valid friendship found between users");
            }

            $query = "SELECT m.*, u.user_name, u.user_image_profil 
                     FROM user_friend_message m 
                     JOIN user u ON m.user_id = u.user_id 
                     WHERE m.user_friend_id = ?
                     ORDER BY m.user_friend_message_time ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $user_friend_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Query execution failed");
            }

            $result = $stmt->get_result();
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            return $messages;
        } catch (Exception $e) {
            throw new Exception("Error fetching messages: " . $e->getMessage());
        }
    }
}