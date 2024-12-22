<?php
require_once __DIR__ . "/Objet.php";  
require_once __DIR__ . "/Image.php";  

class Annonce extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    private function handleAnnonceImage($annonce_id, $file, $title) {
        // Faudra tout changer ici si on veux mettre plusieur image par annonce.
        if (!isset($file['annonce_image']) || $file['annonce_image']['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $image = new Image($this->conn);
        $image_path = handle_image_upload($file['annonce_image']);
        
        $check_query = "SELECT image_id FROM annonce_image WHERE annonce_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bind_param('i', $annonce_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        try {
            if ($result->num_rows > 0) {
                $image->update($annonce_id, $image_path);
            } else {
                $image->create($annonce_id, $image_path, $title);
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors du traitement de l'image: " . $e->getMessage());
        }
    }

    public function create($input) {
        $this->conn->begin_transaction();
    
        try {
            $query = "INSERT INTO annonce (user_id, annonce_participant_number, annonce_title, 
                     annonce_description, annonce_value, category_id)
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('iissii', 
                $input['user_id'],
                $input['annonce_participant_number'],
                $input['annonce_title'],
                $input['annonce_description'],
                $input['annonce_value'],
                $input['category_id']
            );
    
            $stmt->execute();
            $annonce_id = $this->conn->insert_id;

            if (isset($input['files'])) {
                $this->handleAnnonceImage($annonce_id, $input['files'], $input['annonce_title']);
            }

            $this->conn->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Annonce créée avec succès', 
                'annonce_id' => $annonce_id
            ]);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Erreur lors de la création de l'annonce: " . $e->getMessage());
        }
    }

    public function update($input) {
        $this->conn->begin_transaction();
    
        try {
            $query = "UPDATE annonce 
                      SET annonce_participant_number = ?, 
                          annonce_title = ?, 
                          annonce_description = ?, 
                          annonce_value = ?, 
                          category_id = ?
                      WHERE annonce_id = ?";
                      
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('issiii',
                $input['annonce_participant_number'],
                $input['annonce_title'],
                $input['annonce_description'],
                $input['annonce_value'],
                $input['category_id'],
                $input['annonce_id']
            );
    
            $stmt->execute();

            if (isset($input['files'])) {
                $this->handleAnnonceImage($input['annonce_id'], $input['files'], $input['annonce_title']);
            }

            $this->conn->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Annonce mise à jour avec succès', 
                'annonce_id' => $input['annonce_id']
            ]);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Erreur lors de la mise à jour de l'annonce: " . $e->getMessage());
        }
    }

    function getAnnonces($conn, $type, $input = null, $limit = 9, $offset = 0) { 
        try {
            $limit = $input['limit'] ?? $limit;  
            $offset = $input['offset'] ?? $offset;
            $userId = $input['user_id'];
            $categoryId = $input['category_id'] ?? null;

            $query = "";
            $params = [];
            $types = '';
        
            switch ($type) {
                case "all":
                    if (is_null($userId)) {
                        $this->sendErrorResponse(400, "User ID is required for 'all' type");
                        return false;
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_path,
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
                        GROUP BY a.annonce_id, ai.image_name, ai.image_path, c.category_name
                        ORDER BY a.annonce_id DESC
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $limit, $offset];
                    $types = 'iiii';
                    break;
                case "category":
                    if (is_null($userId) || is_null($categoryId)) {
                        $this->sendErrorResponse(400, "User ID and Category ID are required for 'category' type");
                        return false;
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_path,
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
                        GROUP BY a.annonce_id, ai.image_name, ai.image_path, c.category_name
                        ORDER BY a.annonce_id DESC 
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $categoryId, $limit, $offset];
                    $types = 'iiiii';
                    break;
                case "like":
                    if (is_null($userId)) {
                        $this->sendErrorResponse(400, "User ID is required for 'like' type");
                        return false;
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_path,
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
                        LEFT JOIN category c ON a.category_id = c.category_id
                        WHERE al.user_id = ? 
                        GROUP BY a.annonce_id, ai.image_name, ai.image_path
                        ORDER BY a.annonce_id DESC 
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $userId, $limit, $offset];
                    $types = 'iiiii';
                    break;
                case "participant":
                    if (is_null($userId)) {
                        $this->sendErrorResponse(400, "User ID is required for 'participant' type");
                        return false;
                    }
                    $query = "
                        SELECT 
                            a.*, 
                            ai.image_name, ai.image_path,
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
                        LEFT JOIN category c ON a.category_id = c.category_id 
                        WHERE ap.user_id = ? 
                        GROUP BY a.annonce_id, ai.image_name, ai.image_path
                        ORDER BY a.annonce_id DESC 
                        LIMIT ? OFFSET ?";
                    $params = [$userId, $userId, $userId, $limit, $offset];
                    $types = 'iiiii';
                    break;            
                default:
                    $this->sendErrorResponse(400, "Invalid load type");
                    return false;
            }

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) {
                $this->sendErrorResponse(500, "Erreur lors de l'exécution de la requête : " . $stmt->error);
                return false;
            }
    
            $result = $stmt->get_result();
            $annonce = [];
            while ($row = $result->fetch_assoc()) {
                $row['image_name'] = $row['image_name'] ?? null;
                $row['image_path'] = $row['image_path'] ?? null;
                $row['is_liked'] = (bool)($row['is_liked'] ?? false);
                $row['is_participant'] = (bool)($row['is_participant'] ?? false);
                $row['like_count'] = (int)$row['like_count'];
                $row['participant_count'] = (int)$row['participant_count'];
                $row['category_name'] = $row['category_name'] ?? null;
                $annonce[] = $row;
            }
    
            return $annonce;
        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
            return false;
        }
    }

    public function getUserAnnonce($conn, $userId, $input, $limit = 10, $offset = 0) {
        try {
            $limit = $input['limit'] ?? $limit;  
            $offset = $input['offset'] ?? $offset;

            if (is_null($userId)) {
                $this->sendErrorResponse(400, "L'ID utilisateur est requis.");
                return false;
            }

            $query = "
                SELECT 
                    a.*, 
                    ai.image_name, 
                    ai.image_path,
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
                GROUP BY a.annonce_id, ai.image_name, ai.image_path, c.category_name
                ORDER BY a.annonce_id DESC
                LIMIT ? OFFSET ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param('iiiii', $userId, $userId, $userId, $limit, $offset);

            if (!$stmt->execute()) {
                $this->sendErrorResponse(500, "Erreur lors de l'exécution de la requête : " . $stmt->error);
                return false;
            }

            $result = $stmt->get_result();
            $annonce = [];
            while ($row = $result->fetch_assoc()) {
                $row['image_name'] = $row['image_name'] ?? null;
                $row['image_path'] = $row['image_path'] ?? null;
                $row['is_liked'] = (bool)$row['is_liked'];
                $row['is_participant'] = (bool)$row['is_participant'];
                $row['like_count'] = (int)$row['like_count'];
                $row['participant_count'] = (int)$row['participant_count'];
                $row['category_name'] = $row['category_name'] ?? null;
                $annonce[] = $row;
            }

            return $annonce;

        } catch (Exception $e) {
            $this->sendErrorResponse(500, $e->getMessage());
            return false;
        }
    }



    public function getAnnonceById($conn, $annonce_id) {
        $query = "SELECT a.*, u.user_name, u.user_image_profil, c.category_name, ai.image_path, ai.image_name,
                  (SELECT COUNT(*) FROM annonce_like al WHERE al.annonce_id = a.annonce_id) as like_count,
                  (SELECT COUNT(*) FROM annonce_participant ap WHERE ap.annonce_id = a.annonce_id) as participant_count,
                  EXISTS(SELECT 1 FROM annonce_like al WHERE al.annonce_id = a.annonce_id AND al.user_id = ?) as is_liked,
                  EXISTS(SELECT 1 FROM annonce_participant ap WHERE ap.annonce_id = a.annonce_id AND ap.user_id = ?) as is_participant
                  FROM annonce a
                  JOIN user u ON a.user_id = u.user_id
                  JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                  JOIN category c ON a.category_id = c.category_id
                  WHERE a.annonce_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $annonce_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
