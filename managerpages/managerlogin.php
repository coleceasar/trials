<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="auth-page">
  <form method="post" action="#">
                <div class="container">
            <h2>Manager Login</h2>
            <p>Enter the Login Credentials:</p>
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
               <button type="submit">Login</button>
                </div>
 </form>
</body>
</html>
<?php
session_start();
include_once '../config.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        die("Please enter both username and password.");
    }

    try {

        // Get manager details
        $query = "SELECT * FROM manager WHERE username = :username AND LOWER(status) = 'active' LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$manager) {
            echo 'Invalid username, password, or account is inactive.';
            exit;
        }
        $passwordValid = false;

        if (!empty($manager['password'])) {

            if (password_verify($password, $manager['password'])) {
                $passwordValid = true;
            } elseif ($password === $manager['password']) {
                // Remove this after all passwords are hashed
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
            die("Invalid username or password.");
        }

        $assignmentQuery = "SELECT property_id
            FROM manager_properties
            WHERE manager_id = :manager_id
            LIMIT 1
        ";
        
        $assignmentStmt = $db->prepare($assignmentQuery);
        $assignmentStmt->bindParam(':manager_id', $manager['manager_id']);
        $assignmentStmt->execute();

        if ($assignmentStmt->rowCount() == 0) {
            die("No property has been assigned to this manager.");
        }

        $assignment = $assignmentStmt->fetch(PDO::FETCH_ASSOC);
        $property_id = $assignment['property_id'];
        $propertyQuery = "
            SELECT property_id
            FROM property
            WHERE property_id = :property_id
            LIMIT 1
        ";

        $propertyStmt = $db->prepare($propertyQuery);
        $propertyStmt->bindParam(':property_id', $property_id);
        $propertyStmt->execute();

        if ($propertyStmt->rowCount() == 0) {
            die("Assigned property does not exist.");
        }

        $_SESSION['manager_id'] = $manager['manager_id'];
        $_SESSION['manager_username'] = $manager['username'];
        $_SESSION['property_id'] = $property_id;

        header("Location: managersdashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>