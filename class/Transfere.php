<?php
require_once __DIR__ . "/Objet.php";

class Transfere extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }
    public function create($conn, $annonce_id, $sender_id, $participant_id, $amount) {
        $query = "INSERT INTO transfere (user_id_1, user_id_2, annonce_id, transfer_amount, transfer_status) 
                  VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiid', $sender_id, $participant_id, $annonce_id, $amount);
        
        if ($stmt->execute()) {
            $updateSenderQuery = "UPDATE user SET user_balance = user_balance - ? WHERE user_id = ?";
            $updateSenderStmt = $conn->prepare($updateSenderQuery);
            $updateSenderStmt->bind_param('di', $amount, $sender_id);
            $updateSenderStmt->execute();

            return $stmt->insert_id;
        }
        return false;
    }

    // dans le cas ou il accepet
    public function validateTransfer($conn, $transfer_id) {
        $conn->begin_transaction();

        try {
            $query = "SELECT user_id_2, transfer_amount FROM transfere WHERE transfer_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $transfer_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result) {
                throw new Exception("Transfer not found");
            }

            $participant_id = $result['user_id_2'];
            $amount = $result['transfer_amount'];
            $updateTransferQuery = "UPDATE transfere 
                SET transfere = 1, transfer_status = 'approved', transfer_time = NOW() 
                WHERE transfer_id = ?";
            $updateTransferStmt = $conn->prepare($updateTransferQuery);
            $updateTransferStmt->bind_param('i', $transfer_id);
            $updateTransferStmt->execute();
            $updateParticipantQuery = "UPDATE user SET user_balance = user_balance + ? WHERE user_id = ?";
            $updateParticipantStmt = $conn->prepare($updateParticipantQuery);
            $updateParticipantStmt->bind_param('di', $amount, $participant_id);
            $updateParticipantStmt->execute();

            $conn->commit();

            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public function rejectTransfer($conn, $transfer_id) {
        $conn->begin_transaction();

        try {
            $query = "SELECT user_id_1, transfer_amount FROM transfere WHERE transfer_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $transfer_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result) {
                throw new Exception("Transfer not found");
            }

            $sender_id = $result['user_id_1'];
            $amount = $result['transfer_amount'];
            $updateTransferQuery = "UPDATE transfere 
                SET transfer_status = 'rejected', transfer_time = NOW() 
                WHERE transfer_id = ?";
            $updateTransferStmt = $conn->prepare($updateTransferQuery);
            $updateTransferStmt->bind_param('i', $transfer_id);
            $updateTransferStmt->execute();
            $refundQuery = "UPDATE user SET user_balance = user_balance + ? WHERE user_id = ?";
            $refundStmt = $conn->prepare($refundQuery);
            $refundStmt->bind_param('di', $amount, $sender_id);
            $refundStmt->execute();
            $conn->commit();

            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public function fetchValidatedParticipants($conn, $annonce_id, $current_user_id) {
        $creatorQuery = "SELECT user_id FROM annonce WHERE annonce_id = ?";
        $creatorStmt = $conn->prepare($creatorQuery);
        $creatorStmt->bind_param('i', $annonce_id);
        $creatorStmt->execute();
        $creatorResult = $creatorStmt->get_result()->fetch_assoc();
        $isCreator = ($creatorResult['user_id'] == $current_user_id);
        $query = "SELECT u.user_id, u.user_name, u.user_image_profil, 
                         t.transfer_id, t.transfer_status, t.transfer_amount, t.transfer_time
                  FROM transfere t
                  JOIN user u ON t.user_id_2 = u.user_id
                  WHERE t.annonce_id = ? AND t.transfer_status = 'approved'";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $annonce_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $participants = [];
        while ($row = $result->fetch_assoc()) {
            $participants[] = $row;
        }
    
        return [
            'participants' => $participants,
            'isCreator' => $isCreator
        ];
    }
}
?>