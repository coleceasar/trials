<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="auth-page">
  <form method="post" action="#">
                <div class="container">
            <h2>Tenant Login</h2>
            <p>Enter the Login Credentials:</p>
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
               <button type="submit">Login</button>
                </div>
 </form>
</body>
</html>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        die("Please enter both username and password.");
    }
     try {

        // Get tenant details
        $query = "SELECT * FROM tenants WHERE username = :username LIMIT 1";
        // $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            die("Invalid username or password.");
        }
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        $passwordValid = false;

        if (!empty($tenant['password'])) {

            if (password_verify($password, $tenant['password'])) {
                $passwordValid = true;
            } elseif ($password === $tenant['password']) {
                // Remove this after all passwords are hashed
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
            die("Invalid username or password.");
        }

        $assignmentQuery = "
            SELECT property_id
            FROM manager_properties
            WHERE manager_id = :manager_id
            LIMIT 1
        ";
        
        $assignmentStmt = $db->prepare($assignmentQuery);
        $assignmentStmt->bindParam(':manager_id', $tenant['tenant_id']);
        $assignmentStmt->execute();

        if ($assignmentStmt->rowCount() == 0) {
            die("No property has been assigned to this tenant.");
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

        $_SESSION['tenant_id'] = $tenant['tenant_id'];
        $_SESSION['tenant_username'] = $tenant['username'];
        $_SESSION['property_id'] = $property_id;

        header("Location: tdashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="auth-page">
  <form method="post" action="#">
                <div class="container">
            <h2>Tenant Login</h2>
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    exit;
}
// query the database for the tenant with the provided username
try {
    $query = "SELECT 
                tp.password, 
                tp.tenant_id, 
                tp.username, 
                t.name, 
                t.phone_number
                -- t.`idnumber/passport`
              FROM tenant_profile tp
              JOIN tenants t ON t.tenant_id = tp.tenant_id
              WHERE tp.username = :username
              LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo 'Invalid username';
        exit;
    }

    $storedPassword = $row['password'] ?? null;
    $passwordIsValid = false;

    if ($storedPassword !== null) {
        if (password_verify($password, $storedPassword)) {
            $passwordIsValid = true;
        } elseif ((string)$storedPassword === (string)$password) {
            $passwordIsValid = true;
        }
    }

    if (!$passwordIsValid) {
        echo 'Invalid username or password.';
        exit;
    }

    $updateLoginStmt = $db->prepare("UPDATE tenant_profile SET lastlogin = NOW(), ip_address = INET_ATON(:ip_address) WHERE username = :username");
    $updateLoginStmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
    $updateLoginStmt->bindParam(':username', $username);
    $updateLoginStmt->execute();

    $_SESSION['tenant_username'] = $row['username'];
    header('Location:tdashboard.php');
    exit;
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

