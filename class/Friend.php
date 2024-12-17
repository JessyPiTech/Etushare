<?php
// Inclure la classe Objet dans le même répertoire que User.php
require_once __DIR__ . "/Objet.php";  

class Friend extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($input) {
        try {
            // Utilisation de la connexion pour insérer un utilisateur dans la base de données
            $query = "INSERT INTO user_friends (user_id_1, user_id_2, user_friend_icon)
            VALUES (?, ?, ?)";

            $stmt = $this->conn->prepare($query);

            // Extraction des données de $input
            $user_id_1 = $input['user_id_1'];
            $user_id_2 = $input['user_id_2'];
            $user_friend_icon = $input['user_friend_icon'];

            // Liaison des paramètres
            $stmt->bindParam('iis',$user_id_1, $user_id_2, $user_friend_icon);

            // Exécution de la requête
            $result = $stmt->execute();
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "amitier créé avec succès",
                    'user_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de la création de d'amitier: " . $stmt->error
                ]);
                return false;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => "Erreur : " . $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function update($input) {
        try {
            $query = "UPDATE user_friends SET user_id_1 = ?, user_id_2 = ?, 
            user_friend_icon = ? WHERE user_friend_id = ?";
        
            $stmt = $this->conn->prepare($query);
        
            // Extraction des données de $input
            $user_id_1 = $input['user_id_1'];
            $user_id_2 = $input['user_id_2'];
            $user_friend_icon = $input['user_friend_icon'];
            $user_friend_id = $input['user_friend_id']; // L'ID de l'enregistrement à mettre à jour
        
            // Liaison des paramètres
            $stmt->bindParam('iisi', $user_id_1, $user_id_2, $user_friend_icon , $user_friend_id);
        
            
            // Exécution de la requête
            $result = $stmt->execute();
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "amitier mis à jour avec succès"
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de la mise à jour de l'amitier: " . $stmt->error
                ]);
                return false;
            }
        }catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => "Erreur : " . $e->getMessage()
            ]);
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