<?php
require_once __DIR__ . "/Objet.php";
require_once __DIR__ . "/Transfere.php";
require_once __DIR__ . "/User.php";

class Notification extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }


    

    function handleLikeNotification($conn, $input) {
        $query = "UPDATE annonce_like SET annonce_like_notif = 0 WHERE annonce_like_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $input['notification_id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Échec de la mise à jour de la notification']);
        }
    }

    

    function fetchFriendRequests($conn, $user_id) {
        $query = "SELECT uf.user_friend_id, u.user_name as sender_name 
                FROM user_friend uf 
                JOIN user u ON u.user_id = uf.user_id_2 
                WHERE (uf.user_id_1 = ? AND uf.user_friend_notif = 1) 
                OR (uf.user_id_2 = ? AND uf.user_friend_notif = 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    function fetchLikes($conn, $user_id) {
        $query = "SELECT al.annonce_like_id, u.user_name as sender_name, a.annonce_title 
                FROM annonce_like al 
                JOIN user u ON u.user_id = al.user_id 
                JOIN annonce a ON a.annonce_id = al.annonce_id 
                WHERE a.user_id = ? AND al.annonce_like_notif = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function fetchParticipants($conn, $user_id) {
        $query = "SELECT ap.annonce_participant_id, u.user_name as sender_name, a.annonce_title, 
                        a.annonce_id, ap.user_id as participant_id 
                FROM annonce_participant ap
                JOIN user u ON u.user_id = ap.user_id
                JOIN annonce a ON a.annonce_id = ap.annonce_id
                WHERE a.user_id = ? AND ap.annonce_participant_notif = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    function fetchUserNotifications($conn, $user_id) {
        $notifications = [];
    
        $friendRequests = $this->fetchFriendRequests($conn, $user_id);
        foreach ($friendRequests as $row) {
            $notifications[] = [
                'id' => $row['user_friend_id'],
                'type' => 'Friend_Respond',
                'sender_name' => $row['sender_name']
            ];
        }
    
        $likes = $this->fetchLikes($conn, $user_id);
        foreach ($likes as $row) {
            $notifications[] = [
                'id' => $row['annonce_like_id'],
                'type' => 'like',
                'sender_name' => $row['sender_name'],
                'annonce_title' => $row['annonce_title']
            ];
        }
    
        $participants = $this->fetchParticipants($conn, $user_id);
        foreach ($participants as $row) {
            $notifications[] = [
                'id' => $row['annonce_participant_id'],
                'type' => 'Participant_Respond',
                'participant_name' => $row['sender_name'],
                'annonce_title' => $row['annonce_title'],
                'annonce_id' => $row['annonce_id'],
                'participant_id' => $row['participant_id']
            ];
        }
    
        return $notifications;
    }
}