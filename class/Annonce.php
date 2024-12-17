<?php
require_once __DIR__ . "/Objet.php";  

class Annonce extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }
    public function create($input) {
        $this->conn->begin_transaction();
    
        try {
            $query = "INSERT INTO annonce (user_id, annonce_participant_number, annonce_title, annonce_description, annonce_value, category_id)
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
    
            $user_id = $input['user_id'];
            $annonce_participant_number = $input['annonce_participant_number'];
            $annonce_title = $input['annonce_title'];
            $annonce_description = $input['annonce_description'];
            $annonce_value = $input['annonce_value'];
            $category_id = $input['category_id'];
    
            $stmt->bind_param('iissii', $user_id, $annonce_participant_number, $annonce_title, $annonce_description, $annonce_value, $category_id);
    
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la création de l'annonce");
            }
    
            $annonce_id = $this->conn->insert_id;
            if (isset($input['files']['annonce_image'])) {
                $image_path = handle_image_upload($input['files']['annonce_image']);
                
                if ($image_path) {
                    $image_query = "INSERT INTO annonce_image (annonce_id, image_lien) VALUES (?, ?)";
                    $image_stmt = $this->conn->prepare($image_query);
                    $image_stmt->bind_param('is', $annonce_id, $image_path);
                    
                    if (!$image_stmt->execute()) {
                        throw new Exception("Erreur lors de l'enregistrement de l'image");
                    }
                }
            }

            $this->conn->commit();
    
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'message' => 'Annonce créée avec succès', 
                'annonce_id' => $annonce_id
            ]);
            return true;
    
        } catch (Exception $e) {
            $this->conn->rollback();
            
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    public function update($input) {
        $this->conn->begin_transaction();
    
        try {
            $query = "UPDATE annonce 
                      SET  
                          annonce_participant_number = ?, 
                          annonce_title = ?, 
                          annonce_description = ?, 
                          annonce_value = ?, 
                          category_id = ?
                      WHERE annonce_id = ?";
            
            $stmt = $this->conn->prepare($query);
    
            $user_id = $input['user_id'];
            $annonce_participant_number = $input['annonce_participant_number'];
            $annonce_title = $input['annonce_title'];
            $annonce_description = $input['annonce_description'];
            $annonce_value = $input['annonce_value'];
            $category_id = $input['category_id'];
            $annonce_id = $input['annonce_id'];
    
            $stmt->bind_param('issiii', $annonce_participant_number, $annonce_title, $annonce_description, $annonce_value, $category_id, $annonce_id);
    
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la mise à jour de l'annonce");
            }
    
            if (isset($input['files']['annonce_image'])) {
                $image_path = handle_image_upload($input['files']['annonce_image']);
    
                if ($image_path) {
                    $image_check_query = "SELECT image_id FROM annonce_image WHERE annonce_id = ?";
                    $check_stmt = $this->conn->prepare($image_check_query);
                    $check_stmt->bind_param('i', $annonce_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
    
                    if ($check_result->num_rows > 0) {
                        $image_update_query = "UPDATE annonce_image 
                                               SET image_lien = ? 
                                               WHERE annonce_id = ?";
                        $update_stmt = $this->conn->prepare($image_update_query);
                        $update_stmt->bind_param('si', $image_path, $annonce_id);
    
                        if (!$update_stmt->execute()) {
                            throw new Exception("Erreur lors de la mise à jour de l'image");
                        }
                    } else {
                        $image_insert_query = "INSERT INTO annonce_image (annonce_id, image_lien) VALUES (?, ?)";
                        $insert_stmt = $this->conn->prepare($image_insert_query);
                        $insert_stmt->bind_param('is', $annonce_id, $image_path);
    
                        if (!$insert_stmt->execute()) {
                            throw new Exception("Erreur lors de l'ajout de l'image");
                        }
                    }
                }
            }
    
            $this->conn->commit();

            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => 'Annonce mise à jour avec succès', 
                'annonce_id' => $annonce_id
            ]);
            return true;
    
        } catch (Exception $e) {
            $this->conn->rollback();
    
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    function getAnnonces($conn, $type, $input = null, $limit = 9, $offset = 0) { 
        try {

                $limit = $input['limit'] ?? $limit;  
                $offset = $input['offset'] ?? $offset;
                $userId = $input['user_id'] ;
                $categoryId = $input['category_id'] ?? null;
            
    
            $query = "";
            $params = [];
            $types = '';
        
            switch ($type) {
                case "all":
                    if (is_null($userId)) {
                        throw new Exception("User ID is required for 'all' type");
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_lien,
                            c.category_name,
                            CASE WHEN al.annonce_like_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                            CASE WHEN ap.annonce_participant_id IS NOT NULL THEN 1 ELSE 0 END as is_participant,
                            COUNT(DISTINCT al.annonce_like_id) as like_count,
                            COUNT(DISTINCT ap.annonce_participant_id) as participant_count
                        FROM annonce a
                        LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                        LEFT JOIN annonce_like al ON a.annonce_id = al.annonce_id AND al.user_id = ?
                        LEFT JOIN annonce_participant ap ON a.annonce_id = ap.annonce_id AND ap.user_id = ?
                        LEFT JOIN category c ON a.category_id = c.category_id  -- Jointure avec la table category
                        GROUP BY a.annonce_id, ai.image_name, ai.image_lien, c.category_name
                        ORDER BY a.annonce_id DESC
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $limit, $offset];
                    $types = 'iiii';
                    break;
            
                case "category":
                    if (is_null($userId) || is_null($categoryId)) {
                        throw new Exception("User ID and Category ID are required for 'category' type");
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_lien,
                            c.category_name, 
                            CASE WHEN al.annonce_like_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                            CASE WHEN ap.annonce_participant_id IS NOT NULL THEN 1 ELSE 0 END as is_participant,
                            COUNT(DISTINCT al.annonce_like_id) as like_count,
                            COUNT(DISTINCT ap.annonce_participant_id) as participant_count
                        FROM annonce a
                        
                        LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                        LEFT JOIN annonce_like al ON a.annonce_id = al.annonce_id AND al.user_id = ?
                        LEFT JOIN annonce_participant ap ON a.annonce_id = ap.annonce_id AND ap.user_id = ?
                        LEFT JOIN category c ON a.category_id = c.category_id 
                        WHERE a.category_id = ?  
                        GROUP BY a.annonce_id, ai.image_name, ai.image_lien, c.category_name
                        ORDER BY a.annonce_id DESC 
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $categoryId, $limit, $offset];
                    $types = 'iiiii';
                    break;
            
                case "like":
                    if (is_null($userId)) {
                        throw new Exception("User ID is required for 'like' type");
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_lien,
                            c.category_name, 
                            CASE WHEN al.annonce_like_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                            CASE WHEN ap.annonce_participant_id IS NOT NULL THEN 1 ELSE 0 END as is_participant,
                            COUNT(DISTINCT al.annonce_like_id) as like_count,
                            COUNT(DISTINCT ap.annonce_participant_id) as participant_count
                        FROM annonce_like al
                        JOIN annonce a ON al.annonce_id = a.annonce_id
                        LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                        LEFT JOIN annonce_like al2 ON a.annonce_id = al2.annonce_id AND al2.user_id = ?
                        LEFT JOIN annonce_participant ap ON a.annonce_id = ap.annonce_id AND ap.user_id = ?
                        LEFT JOIN category c ON a.category_id = c.category_id  -- Jointure avec la table category
                        WHERE al.user_id = ? 
                        GROUP BY a.annonce_id, ai.image_name, ai.image_lien
                        ORDER BY a.annonce_id DESC 
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $userId, $limit, $offset];
                    $types = 'iiiii';
                    break;
            
                case "participant":
                    if (is_null($userId)) {
                        throw new Exception("User ID is required for 'participant' type");
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_lien,
                            c.category_name, 
                            CASE WHEN al.annonce_like_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                            CASE WHEN ap.annonce_participant_id IS NOT NULL THEN 1 ELSE 0 END as is_participant,
                            COUNT(DISTINCT al.annonce_like_id) as like_count,
                            COUNT(DISTINCT ap.annonce_participant_id) as participant_count
                        FROM annonce_participant ap
                        JOIN annonce a ON ap.annonce_id = a.annonce_id
                        LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                        LEFT JOIN annonce_like al ON a.annonce_id = al.annonce_id AND al.user_id = ?
                        LEFT JOIN annonce_participant ap2 ON a.annonce_id = ap2.annonce_id AND ap2.user_id = ?
                        LEFT JOIN category c ON a.category_id = c.category_id  -- Jointure avec la table category
                        WHERE ap.user_id = ? 
                        GROUP BY a.annonce_id, ai.image_name, ai.image_lien
                        ORDER BY a.annonce_id DESC 
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $userId, $limit, $offset];
                    $types = 'iiiii';
                    break;            
                default:
                    throw new Exception("Invalid load type");
            }
    
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) {
                throw new Exception("Error during query execution: " . $stmt->error);
            }
    
            $result = $stmt->get_result();
            $annonce = [];
            while ($row = $result->fetch_assoc()) {
                $row['image_name'] = $row['image_name'] ?? null;
                $row['image_lien'] = $row['image_lien'] ?? null;
                
                $row['is_liked'] = (bool)($row['is_liked'] ?? false);
                $row['is_participant'] = (bool)($row['is_participant'] ?? false);
                $row['like_count'] = (int)$row['like_count'];
                $row['participant_count'] = (int)$row['participant_count'];
                $row['category_name'] = $row['category_name'] ?? null;
                $annonce[] = $row;
            }
    
            return $annonce;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getUserAnnonce($conn, $userId, $input, $limit = 10, $offset = 0) {
        try {
            $limit = $input['limit'] ?? $limit;  
            $offset = $input['offset'] ?? $offset;

            if (is_null($userId)) {
                throw new Exception("L'ID utilisateur est requis.");
            }

            $query = "
                SELECT 
                    a.*, 
                    ai.image_name, 
                    ai.image_lien,
                    c.category_name,
                    CASE WHEN al.annonce_like_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                    CASE WHEN ap.annonce_participant_id IS NOT NULL THEN 1 ELSE 0 END as is_participant,
                    COUNT(DISTINCT al.annonce_like_id) as like_count,
                    COUNT(DISTINCT ap.annonce_participant_id) as participant_count
                FROM annonce a
                LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                LEFT JOIN annonce_like al ON a.annonce_id = al.annonce_id AND al.user_id = ?
                LEFT JOIN annonce_participant ap ON a.annonce_id = ap.annonce_id AND ap.user_id = ?
                LEFT JOIN category c ON a.category_id = c.category_id
                WHERE a.user_id = ?
                GROUP BY a.annonce_id, ai.image_name, ai.image_lien, c.category_name
                ORDER BY a.annonce_id DESC
                LIMIT ? OFFSET ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param('iiiii', $userId, $userId, $userId, $limit, $offset);
    
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de l'exécution de la requête : " . $stmt->error);
            }

            $result = $stmt->get_result();
            $annonces = [];
            while ($row = $result->fetch_assoc()) {
                $row['image_name'] = $row['image_name'] ?? null;
                $row['image_lien'] = $row['image_lien'] ?? null;
                $row['category_name'] = $row['category_name'] ?? null;
                $row['is_liked'] = (bool)($row['is_liked'] ?? false);
                $row['is_participant'] = (bool)($row['is_participant'] ?? false);
                $row['like_count'] = (int)$row['like_count'];
                $row['participant_count'] = (int)$row['participant_count'];
    
                $annonces[] = $row;
            }
    
            return $annonces;
        } catch (Exception $e) {
            throw new Exception("Erreur dans getUserAnnonce : " . $e->getMessage());
        }
    }

    public function getAnnonceById($conn, $annonce_id) {
        $query = "SELECT a.*, u.user_name, u.user_image_profil, c.category_name, 
                  (SELECT COUNT(*) FROM annonce_like al WHERE al.annonce_id = a.annonce_id) as like_count,
                  (SELECT COUNT(*) FROM annonce_participant ap WHERE ap.annonce_id = a.annonce_id) as participant_count,
                  EXISTS(SELECT 1 FROM annonce_like al WHERE al.annonce_id = a.annonce_id AND al.user_id = ?) as is_liked,
                  EXISTS(SELECT 1 FROM annonce_participant ap WHERE ap.annonce_id = a.annonce_id AND ap.user_id = ?) as is_participant
                  FROM annonce a
                  JOIN user u ON a.user_id = u.user_id
                  JOIN category c ON a.category_id = c.category_id
                  WHERE a.annonce_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $annonce_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    
    
   

}
?>