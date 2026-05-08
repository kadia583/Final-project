<?php
require_once __DIR__ . '/Database.php';
class RealEstateDatabase {

    private PDO $conn;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function addUser(string $userName, string $contactInfo, string $passwordHash, string $userType): bool {
        // TODO:
        // 1. Insert a new user into the Users table using a prepared statement.
        // 2. Return true if successful, false otherwise.
        $check = $this->conn->prepare("SELECT userId FROM Users WHERE userName = ?");
    $check->execute([$userName]);

    if ($check->fetch()) {
        return false; // already exists
    }
        $sql = "INSERT INTO Users (userName, contactInfo, passwordHash, userType)
                VALUES (:userName, :contactInfo, :passwordHash, :userType)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':userName' => $userName,
            ':contactInfo' => $contactInfo,
            ':passwordHash' => $passwordHash,
            ':userType' => $userType
        ]);
    }

    public function getUserByUsername(string $userName) {
        // Retrieve one user by username.
        $sql = "SELECT * FROM Users WHERE userName = :userName LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userName' => $userName]);
        return $stmt->fetch();
    }

    public function addProperty(string $title, string $propertyType, string $address, string $city, float $price, string $status, int $agentId): bool {
        // TODO: Insert a new property into the Properties table.
        $sql = "INSERT INTO Properties (title, propertyType, address, city, price, status, agentId)
            VALUES (:title, :propertyType, :address, :city, :price, :status, :agentId)";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
        ':title' => $title,
        ':propertyType' => $propertyType,
        ':address' => $address,
        ':city' => $city,
        ':price' => $price,
        ':status' => $status,
        ':agentId' => $agentId
       
    ]);
        
    }

    public function getAllProperties(): array {
        // TODO: Optionally replace this with the PropertyListingView.
        $sql = "SELECT p.*, u.userName AS agentName
                FROM Properties p
                JOIN Users u ON p.agentId = u.userId
                ORDER BY p.propertyId DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function getPropertyById(int $propertyId) {
        $sql = "SELECT p.*, u.userName AS agentName
                FROM Properties p
                JOIN Users u ON p.agentId = u.userId
                WHERE p.propertyId = :propertyId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':propertyId' => $propertyId]);
        return $stmt->fetch();
    }

    public function addInquiry(int $userId, int $propertyId, string $message): bool {
        // TODO: Insert a new inquiry with the current date and time.
        $sql = "INSERT INTO Inquiries (userId, propertyId, message, inquiryDate)
            VALUES (:userId, :propertyId, :message, NOW())";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
        ':userId' => $userId,
        ':propertyId' => $propertyId,
        ':message' => $message
        
    ]);
        
    }

    public function getUserDetails(int $userId) {
        // TODO:
        // Expand this function so it returns the user plus their related
        // inquiries, favorites, or transactions.
        
        $sql = "SELECT * FROM Users WHERE userId = :userId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch();

        $sqlInquiries = "SELECT i.*, p.title AS propertyTitle
                     FROM Inquiries i
                     JOIN Properties p ON i.propertyId = p.propertyId
                     WHERE i.userId = :userId
                     ORDER BY i.createdAt DESC";

    $stmtInquiries = $this->conn->prepare($sqlInquiries);
    $stmtInquiries->execute([':userId' => $userId]);
    $inquiries = $stmtInquiries->fetchAll();

    // Attach related data
    $user['inquiries'] = $inquiries;

     $sqlFavorites = "SELECT f.*, p.title
                     FROM Favorites f
                     JOIN Properties p ON f.propertyId = p.propertyId
                     WHERE f.userId = :userId";

    $stmtFav = $this->conn->prepare($sqlFavorites);
    $stmtFav->execute([':userId' => $userId]);
    $user['favorites'] = $stmtFav->fetchAll(PDO::FETCH_ASSOC);

    // Get transactions
    $sqlTransactions = "SELECT * FROM Transactions WHERE userId = :userId";
    $stmtTrans = $this->conn->prepare($sqlTransactions);
    $stmtTrans->execute([':userId' => $userId]);
    $user['transactions'] = $stmtTrans->fetchAll(PDO::FETCH_ASSOC);

    return $user;

    }

    public function getPropertiesByCity(string $city): array {
        // TODO: Finish this function
        $sql = "SELECT p.*, u.userName AS agentName
            FROM Properties p
            JOIN Users u ON p.agentId = u.userId
            WHERE p.city = :city
            ORDER BY p.propertyId DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':city' => $city]);

    return $stmt->fetchAll();
        
    }
    public function addFavorite(int $userId, int $propertyId): bool {
// Check if already favorited
$sql = "SELECT * FROM favorites
WHERE userId = :userId
AND propertyId = :propertyId";
$stmt = $this->conn->prepare($sql);
$stmt->execute([
':userId' => $userId,
':propertyId' => $propertyId
]);

if ($stmt->fetch()) {
return false; // Already favorited
}
// Add to favorites
$sql2 = "INSERT INTO Favorites (userId, propertyId, savedDate)
VALUES (:userId, :propertyId, NOW())";
$stmt2 = $this->conn->prepare($sql2);
return $stmt2->execute([
':userId' => $userId,
':propertyId' => $propertyId
]);
}

}
?>
