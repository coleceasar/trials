<?php
header('Content-Type: application/json');
include_once '../config.php';

$database = new Database();
$db = $database->getConnection();

$unitId = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 0;

if ($unitId <= 0) {
    echo json_encode(['error' => 'Invalid unit ID']);
    exit;
}

try {
    $query = "SELECT 
                u.unit_id,
                u.unit_name,
                u.unit_type,
                u.residential_size,
                u.number_of_rooms,
                u.office_size,
                u.commercial_size,
                u.monthly_rent
              FROM units u
              WHERE u.unit_id = :unit_id
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
    $stmt->execute();
    
    $unit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$unit) {
        echo json_encode(['error' => 'Unit not found']);
        exit;
    }
    
    // Calculate charge and rent based on unit type
    $chargePerSqft = 0;
    $calculatedRent = 0;
    $unitDetails = '';
    
    $unitType = strtolower($unit['unit_type'] ?? '');
    
    if ($unitType === 'residential') {
        $chargePerSqft = 70;
        $numRooms = (int)($unit['number_of_rooms'] ?? 0);
        
        // Determine rent based on number of rooms
        switch ($numRooms) {
            case 1:
                $calculatedRent = 14000;
                $unitDetails = "Residential - 1 Bedroom";
                break;
            case 2:
                $calculatedRent = 19000;
                $unitDetails = "Residential - 2 Bedrooms";
                break;
            case 3:
                $calculatedRent = 25000;
                $unitDetails = "Residential - 3 Bedrooms";
                break;
            default:
                // Check if it's labeled as bedsitter
                if (stripos($unit['unit_name'] ?? '', 'bedsitter') !== false || 
                    stripos($unit['residential_size'] ?? '', 'bedsitter') !== false) {
                    $calculatedRent = 90000;
                    $unitDetails = "Residential - Bedsitter";
                } else {
                    $calculatedRent = $unit['monthly_rent'] ?? 0;
                    $unitDetails = "Residential - {$numRooms} Rooms";
                }
        }
    } elseif ($unitType === 'office') {
        $chargePerSqft = 80;
        $officeSize = (float)($unit['office_size'] ?? 0);
        $calculatedRent = $officeSize * $chargePerSqft;
        $unitDetails = "Office - {$officeSize} sqft";
    } elseif ($unitType === 'commercial') {
        $chargePerSqft = 90;
        $commercialSize = (float)($unit['commercial_size'] ?? 0);
        $calculatedRent = $commercialSize * $chargePerSqft;
        $unitDetails = "Commercial - {$commercialSize} sqft";
    }
    
    echo json_encode([
        'success' => true,
        'unit_type' => $unitType,
        'charge_per_sqft' => $chargePerSqft,
        'calculated_rent' => (int)$calculatedRent,
        'unit_details' => $unitDetails,
        'size_value' => ($unitType === 'residential' ? 
                        (int)($unit['number_of_rooms'] ?? 0) : 
                        (float)($unit['office_size'] ?? $unit['commercial_size'] ?? 0))
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
