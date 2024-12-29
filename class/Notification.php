<?php 
require_once __DIR__ . "/Objet.php";
require_once __DIR__ . "/Transfere.php";
require_once __DIR__ . "/User.php";

class Notification extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }



    function fetchLikes($conn, $user_id) {
        $query = "SELECT al.annonce_like_id, u.user_name AS sender_name, a.annonce_title
                  FROM annonce_like al
                  JOIN user u ON u.user_id = al.user_id
                  JOIN annonce a ON a.annonce_id = al.annonce_id
                  WHERE a.user_id = ? AND al.annonce_like_notif = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch likes');
        }
    }

    function fetchAvis($conn, $user_id) {
        $query = "SELECT ua.user_avis_id, u.user_name AS sender_name, ua.avis_text, ua.user_avis_note
                  FROM user_avis ua
                  JOIN user u ON u.user_id = ua.user_id_1
                  WHERE ua.user_id_2 = ? AND ua.user_avis_notif = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch avis');
        }
    }

    function fetchFriendRequests($conn, $user_id) {
        $query = "SELECT uf.user_friend_id, u.user_name AS sender_name, uf.user_friend_status
                  FROM user_friend uf
                  JOIN user u ON u.user_id = uf.user_id_1
                  WHERE uf.user_id_2 = ? AND uf.user_friend_notif = 0 AND uf.user_friend_status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch friend requests');
        }
    }

    function fetchFriendResponds($conn, $user_id) {
        $query = "SELECT uf.user_friend_id, u.user_name AS sender_name, uf.user_friend_status AS friend_status 
                  FROM user_friend uf
                  JOIN user u ON u.user_id = uf.user_id_2
                  WHERE uf.user_id_1 = ? AND uf.user_friend_notif = 1 AND uf.user_friend_status IN ('accept', 'reject')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch friend responses');
        }
    }

    function fetchParticipantsRequests($conn, $user_id) {
        $query = "SELECT ap.annonce_participant_id, u.user_name AS sender_name, a.annonce_title, a.annonce_id,
                         ap.user_id AS participant_id, ap.annonce_participant_status 
                  FROM annonce_participant ap
                  JOIN user u ON u.user_id = ap.user_id
                  JOIN annonce a ON a.annonce_id = ap.annonce_id
                  WHERE a.user_id = ? AND ap.annonce_participant_notif = 0 AND ap.annonce_participant_status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch participant requests');
        }
    }

    function fetchParticipantsResponds($conn, $user_id) {
        $query = "SELECT ap.annonce_participant_id, u.user_name AS sender_name, a.annonce_title, 
        ap.user_id AS participant_id, ap.annonce_participant_status AS participant_status, a.annonce_id
        FROM annonce_participant ap
        JOIN annonce a ON a.annonce_id = ap.annonce_id
        JOIN user u ON u.user_id = a.user_id
        WHERE ap.user_id = ? 
        AND ap.annonce_participant_notif = 1 
        AND ap.annonce_participant_status IN ('accept', 'reject')
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch participant responses');
        }
    }

    function fetchMessages($conn, $user_id) {
        $query = "SELECT ufm.user_friend_message_id, u.user_name AS sender_name, ufm.user_friend_message_text
                  FROM user_friend_message ufm
                  JOIN user u ON u.user_id = ufm.user_id
                  JOIN user_friend uf ON uf.user_friend_id = ufm.user_friend_id
                  WHERE (uf.user_id_1 = ? OR uf.user_id_2 = ?) 
                  AND ufm.user_friend_message_notif = 0 
                  AND ufm.user_id != ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $user_id, $user_id, $user_id);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->sendErrorResponse(500, 'Failed to fetch messages');
        }
    }

    function getNotifications($conn, $user_id) {
        $notifications = [];

        $likes = $this->fetchLikes($conn, $user_id);
        foreach ($likes as $row) {
            $notifications[] = [
                'id' => $row['annonce_like_id'], 
                'type' => 'Like', 
                'sender_name' => $row['sender_name'], 
                'annonce_title' => $row['annonce_title']
            ];
        }

        $avisNotifications = $this->fetchAvis($conn, $user_id);
        foreach ($avisNotifications as $row) {
            $notifications[] = [
                'id' => $row['user_avis_id'], 
                'type' => 'Avis', 
                'sender_name' => $row['sender_name'], 
                'avis_text' => $row['avis_text'], 
                'avis_note' => $row['user_avis_note'],
            ];
        }

        $friendRequests = $this->fetchFriendRequests($conn, $user_id);
        foreach ($friendRequests as $row) {
            $notifications[] = [
                'id' => $row['user_friend_id'], 
                'type' => 'Friend_Request', 
                'sender_name' => $row['sender_name'],
            ];
        }

        $friendResponds = $this->fetchFriendResponds($conn, $user_id);
        foreach ($friendResponds as $row) {
            $notifications[] = [
                'id' => $row['user_friend_id'], 
                'type' => 'Friend_Respond', 
                'sender_name' => $row['sender_name'],
                'status' => $row['friend_status'], 
            ];
        }

        $participants = $this->fetchParticipantsRequests($conn, $user_id);
        foreach ($participants as $row) {
            $notifications[] = [
                'id' => $row['annonce_participant_id'],
                'type' => 'Participant_Request',
                'participant_name' => $row['sender_name'],
                
                'annonce_title' => $row['annonce_title'],
                'annonce_id' => $row['annonce_id'],
                'participant_id' => $row['participant_id'],
            ];
        }
        $participants = $this->fetchParticipantsResponds($conn, $user_id);
        foreach ($participants as $row) {
            $notifications[] = [
                'id' => $row['annonce_participant_id'], 
                'type' => 'Participant_Respond', 
                'participant_name' => $row['sender_name'], 
             
                'annonce_title' => $row['annonce_title'], 
                'annonce_id' => $row['annonce_id'],
                'status' => $row['participant_status'], 
            ];
        }

        $messages = $this->fetchMessages($conn, $user_id);
        foreach ($messages as $row) {
            $notifications[] = [
                'id' => $row['user_friend_message_id'], 
                'type' => 'Message', 
                'sender_name' => $row['sender_name'], 
                'message_text' => $row['user_friend_message_text']
            ];
        }

        return $notifications;
    }

    function markAsViewed($conn, $input) {
        $type = $input['notification_type_2'];
        $notification_id = $input['notification_id'];

        $queries = [
            'Avis' => "UPDATE user_avis SET user_avis_notif = 2 WHERE user_avis_id = ?",
            'Like' => "UPDATE annonce_like SET annonce_like_notif = 2 WHERE annonce_like_id = ?",
            'Friend_Respond' => "UPDATE user_friend SET user_friend_notif = 2 WHERE user_friend_id = ?",
            'Participant_Respond' => "UPDATE annonce_participant SET annonce_participant_notif = 2 WHERE annonce_participant_id = ?",
            'Transfer' => "UPDATE transfert SET transfert_notif = 2 WHERE transfert_id = ?",
            'Message' => "UPDATE user_friend_message SET user_friend_message_notif = 2 WHERE user_friend_message_id = ?",
        ];
    
        if (!isset($queries[$type])) {
            $this->sendErrorResponse(400, 'Unknown notification type');
            return;
        }

        $query = $queries[$type];
        $stmt = $conn->prepare($query);
    
        switch ($type) {
            case 'Avis':
            case 'Like':
            case 'Friend_Respond':
            case 'Participant_Respond':
            case 'Transfer':
            case 'Message':
                $stmt->bind_param('i', $notification_id);
                break;
        }
    
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            $this->sendErrorResponse(500, 'Failed to mark notification as viewed');
        }
    }
}
?>
