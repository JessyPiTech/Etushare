<?php
class Image extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function create($annonce_id, $image_path, $image_name) {
        $query = "INSERT INTO annonce_image (annonce_id, image_path, image_name) 
                  VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iss', $annonce_id, $image_path, $image_name);

        if (!$stmt->execute()) {
            throw new Exception("Échec de l'ajout de l'image: " . $stmt->error);
        }
        
        return $stmt->insert_id;
    }

    public function update($annonce_id, $image_path) {
        $query = "UPDATE annonce_image 
                  SET image_path = ?
                  WHERE annonce_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $image_path, $annonce_id);

        if (!$stmt->execute()) {
            throw new Exception("Échec de la mise à jour de l'image: " . $stmt->error);
        }

        return true;
    }
}
?>