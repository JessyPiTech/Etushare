<?php
require_once __DIR__ . "/Objet.php";

class User extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($input) {
        try {
            $query = "INSERT INTO user (user_name, user_etucoin, user_image_profil, user_mail, user_password, user_description_profil)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);

            $user_name = $input['user_name'];
            $user_etucoin = 500;
            $user_image_profil = $input['user_image_profil'];
            $user_mail = $input['user_mail'];
            $user_password = $input['user_password'];
            $user_description_profil = $input['user_description_profil'];

            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

            $stmt->bind_param('sissss', 
                $user_name, 
                $user_etucoin, 
                $user_image_profil, 
                $user_mail, 
                $hashed_password,
                $user_description_profil
            );

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Utilisateur créé avec succès",
                    'user_id' => $this->conn->insert_id
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de la création de l'utilisateur: " . $stmt->error
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
            $query = "UPDATE user SET user_name = ?,  
                    user_image_profil = ?, user_mail = ?, 
                    user_password = ?, user_description_profil = ?
                    WHERE user_id = ?";

            $stmt = $this->conn->prepare($query);

            $user_name = $input['user_name'];
            
            $user_image_profil = $input['user_image_profil'];
            $user_mail = $input['user_mail'];
            $user_password = $input['user_password'];
            $user_description_profil = $input['user_description_profil'];
            $user_id = $input['user_id'];

            $stmt->bind_param('sssssi', 
                $user_name, 
                $user_image_profil, 
                $user_mail, 
                $user_password, 
                $user_description_profil, 
                $user_id
            );

            $result = $stmt->execute();

            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Utilisateur mis à jour avec succès"
                ]);
                return true;
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => "Échec de la mise à jour de l'utilisateur: " . $stmt->error
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
    public function getUserDetails($user_id) {
        $query = "SELECT * FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>



<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . "/config/coDB.php";

$conn = coDB();

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si les données ont été envoyées
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les valeurs du formulaire avec assainissement
    $user_name = $conn->real_escape_string($_POST['username']);
    $user_mail = $conn->real_escape_string($_POST['email']);
    $user_password = password_hash($_POST['password'], PASSWORD_BCRYPT); 
    $user_etucoin = isset($_POST['etucoin']) ? (int)$_POST['etucoin'] : 500;
    $user_image_profil = $conn->real_escape_string($_POST['image']);
    $user_description_profil = isset($_POST['description']) && !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "I am anonymous";
    
    // Préparer la requête SQL avec des paramètres
    $stmt = $conn->prepare("INSERT INTO user (user_name, user_etucoin, user_image_profil, user_mail, user_password, user_description_profil)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $user_name, $user_etucoin, $user_image_profil, $user_mail, $user_password, $user_description_profil);

    if ($stmt->execute()) {
        echo "New user created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
    */
?>