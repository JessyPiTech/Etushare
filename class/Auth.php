<?php

class Auth {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login($input) {
        try {
            if (!isset($input['authLogin']) || !isset($input['authPassword'])) {
                throw new Exception("Les paramètres 'authLogin' et 'authPassword' sont requis.");
            }
            $authLogin = $input['authLogin'];
            $authPassword = $input['authPassword'];
    
            if (filter_var($authLogin, FILTER_VALIDATE_EMAIL)) {
                $query = "SELECT user_password, user_id, user_name, user_mail FROM user WHERE user_mail = ?";
            } else {
                $query = "SELECT user_password, user_id, user_name, user_mail FROM user WHERE user_name = ?";
            }
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erreur de préparation de la requête : " . $this->conn->error);
            }

            $stmt->bind_param('s', $authLogin);
    
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de l'exécution de la requête : " . $stmt->error);
            }
    
            $result = $stmt->get_result();

            $response = [
                'success' => false,
                'message' => "Identifiants incorrects."
            ];

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashedPassword = $row['user_password'];

                if (password_verify($authPassword, $hashedPassword)) {
                    
                    $user_id = $row['user_id'];
                    $user_name = $row['user_name'];
                    $user_mail = $row['user_mail'];

                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['user_mail'] = $user_mail;
                    
                    $response = [
                        'success' => true,
                        'message' => "Connexion réussie.",
                        'user_id' => $user_id,
                        'user_name' => $user_name,
                        'user_mail' => $user_mail,
                        'redirect' => './'
                        
                    ];
                }
            }
            header('Content-Type: application/json');
            http_response_code($response['success'] ? 200 : 401);
            echo json_encode($response);
            return $response['success'];

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
    

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: auth.php');
        exit();
    }
}
?>