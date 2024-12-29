
<?php
require_once __DIR__ . "/Objet.php";

class Category extends Objet {
    public function __construct($conn, $id = null) {
        parent::__construct($conn, $id);
    }

    public function get() {
        try {
            $query = "SELECT category_id, category_name FROM category ORDER BY category_name";
            $stmt = $this->conn->prepare($query);

            if (!$stmt->execute()) {
                throw new Exception("Query execution failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $categories = [];

            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Error: " . $e->getMessage());
        }
    }
}

