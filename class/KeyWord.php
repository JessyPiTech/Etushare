<?php
require_once __DIR__ . "/Objet.php";

class KeyWord extends Objet {
    public function getKeyWord($searchTerm, $limit = 10, $offset = 0) {
        try {
            $query = "WITH RankedResults AS (
                SELECT DISTINCT 
                    a.*,
                    ai.image_name, 
                    ai.image_path,
                    c.category_name,
                    CASE 
                        WHEN k.category_key_word LIKE ? THEN 1
                        WHEN a.annonce_title LIKE ? THEN 2
                        WHEN a.annonce_description LIKE ? THEN 3
                        ELSE 4
                    END as match_priority
                FROM annonce a
                INNER JOIN category c ON a.category_id = c.category_id
                LEFT JOIN category_key_word k ON c.category_id = k.category_id
                LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                WHERE 
                    k.category_key_word LIKE ? OR
                    a.annonce_title LIKE ? OR
                    a.annonce_description LIKE ?
            )
            SELECT * FROM RankedResults
            ORDER BY match_priority, annonce_time DESC
            LIMIT ? OFFSET ?";
    
            $searchPattern = "%$searchTerm%";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssssssii', 
                $searchPattern,
                $searchPattern,
                $searchPattern,
                $searchPattern,
                $searchPattern,
                $searchPattern,
                $limit,
                $offset
            );


            if (!$stmt->execute()) {
                throw new Exception("Query execution failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            
            $annonces = [];
            
            while ($row = $result->fetch_assoc()) {
                $annonces[] = $row;
            }
            $_SESSION['last_category_id'] = $annonces[0]['category_id'];
            
            $countQuery = "SELECT COUNT(DISTINCT a.annonce_id) as total 
                          FROM annonce a
                          INNER JOIN category c ON a.category_id = c.category_id
                          LEFT JOIN category_key_word k ON c.category_id = k.category_id
                          WHERE 
                            k.category_key_word LIKE ? OR
                            a.annonce_title LIKE ? OR
                            a.annonce_description LIKE ?";

            $stmtCount = $this->conn->prepare($countQuery);
            $stmtCount->bind_param('sss', $searchPattern, $searchPattern, $searchPattern);
            $stmtCount->execute();
            $totalCount = $stmtCount->get_result()->fetch_assoc()['total'];

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'annonces' => $annonces,
                'totalCount' => $totalCount
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Error: " . $e->getMessage());
        }
    }

    public function getKeyWordByCategory($searchTerm, $categoryId, $limit = 10, $offset = 0) {
        $_SESSION['last_category_id'] =  $categoryId;
        try {
            $query = "SELECT DISTINCT a.*, 
                            ai.image_name, 
                            ai.image_path,
                            c.category_name
                     FROM annonce a
                     INNER JOIN category c ON a.category_id = c.category_id
                     LEFT JOIN category_key_word k ON c.category_id = k.category_id
                     LEFT JOIN annonce_image ai ON a.annonce_id = ai.annonce_id
                     WHERE 
                        (k.category_key_word LIKE ? OR
                        a.annonce_title LIKE ? OR
                        a.annonce_description LIKE ?) AND
                        c.category_id = ?
                     ORDER BY 
                        CASE 
                            WHEN k.category_key_word LIKE ? THEN 1
                            WHEN a.annonce_title LIKE ? THEN 2
                            ELSE 3
                        END,
                        a.annonce_time DESC
                     LIMIT ? OFFSET ?";

            $searchPattern = "%$searchTerm%";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'sssisiii', 
                $searchPattern, 
                $searchPattern, 
                $searchPattern,
                $categoryId,
                $searchPattern,
                $searchPattern,
                $limit,
                $offset
            );

            if (!$stmt->execute()) {
                throw new Exception("Query execution failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $annonces = [];

            while ($row = $result->fetch_assoc()) {
                $annonces[] = $row;
            }

            // Get total count for pagination
            $countQuery = "SELECT COUNT(DISTINCT a.annonce_id) as total 
                          FROM annonce a
                          INNER JOIN category c ON a.category_id = c.category_id
                          LEFT JOIN category_key_word k ON c.category_id = k.category_id
                          WHERE 
                            (k.category_key_word LIKE ? OR
                            a.annonce_title LIKE ? OR
                            a.annonce_description LIKE ?) AND
                            c.category_id = ?";

            $stmtCount = $this->conn->prepare($countQuery);
            $stmtCount->bind_param('sssi', $searchPattern, $searchPattern, $searchPattern, $categoryId);
            $stmtCount->execute();
            $totalCount = $stmtCount->get_result()->fetch_assoc()['total'];

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'annonces' => $annonces,
                'totalCount' => $totalCount
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Error: " . $e->getMessage());
        }
    }
}

