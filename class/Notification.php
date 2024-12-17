<?php
require_once __DIR__ . "/Objet.php";

class Notification extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }
        
    
    function handleFriendRequestNotification($conn, $input) {
        $action = $input['notification_action'];
        $friendRequestId = $input['notification_id'];
    
        if ($action === 'accept') {
            $query = "UPDATE user_friend SET user_friend_status = 1, user_friend_notif = 2 WHERE user_friend_id = ?";
        } else {
            $query = "UPDATE user_friend SET user_friend_status = 2, user_friend_notif = 2 WHERE user_friend_id = ?";
        }
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $friendRequestId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to process friend request']);
        }
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

    function handleParticipantNotification($conn, $input) {
        $action = $input['notification_action'];
        $participantId = $input['notification_id'];
        $annonce_id = $input['annonce_id'];
        $sender_id = $input['sender_id'];
        $participant_id = $input['receiver_id'];
    
        if ($action === 'accept') {
            $valueQuery = "SELECT annonce_value FROM annonce WHERE annonce_id = ?";
            $valueStmt = $conn->prepare($valueQuery);
            $valueStmt->bind_param('i', $annonce_id);
            $valueStmt->execute();
            $result = $valueStmt->get_result()->fetch_assoc();
            $amount = $result['annonce_value'];

            $transfere = new Transfere($conn);
            $transferId = $transfere->create($conn, $annonce_id, $sender_id, $participant_id, $amount);
    
            $query = "UPDATE annonce_participant SET annonce_participant_status = 'confirm', annonce_participant_notif = 2 WHERE annonce_participant_id = ?";
        } else {
            $query = "UPDATE annonce_participant SET annonce_participant_status = 'rejected', annonce_participant_notif = 2 WHERE annonce_participant_id = ?";
        }
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $participantId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Échec du traitement de la participation']);
        }
    }


    function fetchUserNotifications($conn, $user_id) {
        $notifications = [];
        $friendQuery = "SELECT uf.user_friend_id, u.user_name as sender_name 
                        FROM user_friend uf 
                        JOIN user u ON u.user_id = uf.user_id_2 
                        WHERE (uf.user_id_1 = ? AND uf.user_friend_notif = 1) 
                        OR (uf.user_id_2 = ? AND uf.user_friend_notif = 0)";
        $friendStmt = $conn->prepare($friendQuery);
        $friendStmt->bind_param('ii', $user_id, $user_id);
        $friendStmt->execute();
        $friendResult = $friendStmt->get_result();
        
        while ($row = $friendResult->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['user_friend_id'],
                'type' => 'friend_request',
                'sender_name' => $row['sender_name']
            ];
        }
        $likeQuery = " SELECT 
                            al.annonce_like_id,u.user_name 
                            as sender_name,  a.annonce_title 
                            FROM annonce_like al 
                            JOIN user u ON u.user_id = al.user_id 
                            JOIN annonce a ON a.annonce_id = al.annonce_id 
                            WHERE a.user_id = ? AND al.annonce_like_notif = 1";
        $likeStmt = $conn->prepare($likeQuery);
        $likeStmt->bind_param('i', $user_id);
        $likeStmt->execute();
        $likeResult = $likeStmt->get_result();

        while ($row = $likeResult->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['annonce_like_id'],
            'type' => 'like',
            'sender_name' => $row['sender_name'],
            'annonce_title' => $row['annonce_title']
        ];
        }
        $participantQuery = "SELECT ap.annonce_participant_id, u.user_name as sender_name, a.annonce_title 
                        FROM annonce_participant ap 
                        JOIN user u ON u.user_id = ap.user_id 
                        JOIN annonce a ON a.annonce_id = ap.annonce_id 
                        WHERE a.user_id = ? AND ap.annonce_participant_notif = 1";
        $participantStmt = $conn->prepare($participantQuery);
        $participantStmt->bind_param('i', $user_id);
        $participantStmt->execute();
        $participantResult = $participantStmt->get_result();

        while ($row = $participantResult->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['annonce_participant_id'],
            'type' => 'participant',
            'sender_name' => $row['sender_name'],
            'annonce_title' => $row['annonce_title']
        ];
        }

        return $notifications;
    }


    
}
?>