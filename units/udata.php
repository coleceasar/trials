<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_username'])) {
    header('Location: managerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$units = [];
$errorMessage = '';

// If manager has a property assigned, restrict units to that property
$managerProperty = $_SESSION['property_id'] ?? '';

try {
    if ($managerProperty !== '') {
        $query = "SELECT 
                    u.unit_id,
                    u.unit_name, 
                    p.property_name, 
                    b.block_name, 
                    u.unit_type, 
                    u.residential_size, 
                    u.number_of_rooms,
                    u.office_size,
                    u.commercial_size,
                    u.monthly_rent,
                    u.status 
                  FROM units u
                  LEFT JOIN blocks ON u.block_id = blocks.id
                  LEFT JOIN property p ON blocks.property_id = p.property_id
                  WHERE blocks.property_id = :property_id
                  ORDER BY u.unit_name, u.unit_type, u.unit_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':property_id', $managerProperty);
        $stmt->execute();
    } else {
        $query = "SELECT 
                    u.unit_id,
                    u.unit_name, 
                    p.property_name, 
                    b.block_name, 
                    u.unit_type, 
                    u.residential_size, 
                    u.number_of_rooms,
                    u.office_size,
                    u.commercial_size,
                    u.monthly_rent,
                    u.status 
                  FROM units u
                  LEFT JOIN blocks  ON u.block_id = blocks.id
                  LEFT JOIN property p ON blocks.property_id = p.property_id
                  ORDER BY u.unit_name, u.unit_type, u.unit_id";
        
        $stmt = $db->query($query);
    }
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Unable to load units: ' . $e->getMessage();
}

$columns = ['unit_id', 'unit_name', 'property_name', 'block_name', 'unit_type', 'residential_size', 'number_of_rooms', 'office_size', 'commercial_size', 'monthly_rent', 'status'];
$columnLabels = [
    'unit_id' => 'Unit ID',
    'unit_name' => 'Unit Name',
    'property_name' => 'Property Name',
    'block_name' => 'Block Name',
    'unit_type' => 'Unit Type',
    'residential_size' => 'Residential Size',
    'number_of_rooms' => 'Number of Rooms',
    'office_size' => 'Office Size',
    'commercial_size' => 'Commercial Size',
    'monthly_rent' => 'Monthly Rent',
    'status' => 'Status'
];
?>