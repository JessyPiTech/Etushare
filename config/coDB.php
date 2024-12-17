<?php
function coDB() {  
    require_once __DIR__ . "/credentials.php";
    
    $conn = new mysqli($serveur, $utilisateur, $mot_de_passe, $base_de_donnees);
    
    if ($conn->connect_error) {
        echo json_encode(["error" =>'Erreur de connexion à la base de données : ' . $conn->connect_error]);
        return null;
    }
    
    return $conn;
}
?>