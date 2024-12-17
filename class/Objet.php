<?php

class Objet {
    protected $conn;
    protected $id;

    public function __construct($conn, $id = null) {
        $this->conn = $conn;
        $this->id = $id;
    }

    public function countItems($table, $user_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM $table WHERE user_id = $user_id";
            $stmt = $this->conn->prepare($query);

            // Exécuter la requête
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                // Retourner le compte des éléments
                return (int)$row['count'];
            } else {
                throw new Exception("Erreur lors de l'exécution de la requête : " . $stmt->error);
            }
        } catch (Exception $e) {
            // Gérer les exceptions
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => "Erreur : " . $e->getMessage()
            ]);
            return false;
        }
    }

    public function delete($table, $column, $input, $double = false) {
        if ($double == false) {
            
        
            try {
                // Directly use the column name in the query
                $query = "DELETE FROM $table WHERE $column = ?";
                $stmt = $this->conn->prepare($query);
                
                if (!isset($input[$column])) {
                    throw new Exception("Column value not found in input");
                }
        
                $value = $input[$column];
                
                // Use mysqli parameter binding
                $stmt->bind_param('i', $value);  // Use 'i' for integer, change if needed
                
                $result = $stmt->execute();
                
                if ($result) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => "Suppression réussie"
                    ]);
                    return true;
                } else {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false, 
                        'message' => "Échec de la suppression: " . $stmt->error
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
        }else{
            
            try {
                // Directly use the column name in the query
                $query = "DELETE FROM $table WHERE user_id = ? && annonce_id = ?";
                $stmt = $this->conn->prepare($query);
        
                $userId = $input['user_id'];
                $annonceId = $input['annonce_id'];
                
                // Use mysqli parameter binding
                $stmt->bind_param('ii', $userId,$annonceId);  // Use 'i' for integer, change if needed
                
                $result = $stmt->execute();
                
                if ($result) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => "Suppression réussie"
                    ]);
                    return true;
                } else {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false, 
                        'message' => "Échec de la suppression: " . $stmt->error
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
    }
}

?>
