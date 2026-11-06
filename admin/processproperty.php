<?php
session_start();
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    if ($conn) {
        try {
            $conn->beginTransaction();

            // 1. Insert Property
            $propertyName = trim($_POST['property_name'] ?? '');
            $propertyType = trim($_POST['property_type'] ?? '');
            $numberOfBlocks = (int)($_POST['numberofblocks'] ?? 0);
            $propertyLocation = trim($_POST['location'] ?? '');
            $propertyStatus = trim($_POST['status'] ?? 'Unassigned');

            $stmtProp = $conn->prepare("INSERT INTO property (property_name, property_type, numberofblocks, location, status) VALUES (:name, :type, :blocks, :location, :status)");
            $stmtProp->bindParam(':name', $propertyName);
            $stmtProp->bindParam(':type', $propertyType);
            $stmtProp->bindParam(':blocks', $numberOfBlocks, PDO::PARAM_INT);
            $stmtProp->bindParam(':location', $propertyLocation);
            $stmtProp->bindParam(':status', $propertyStatus);
            $stmtProp->execute();

            $propertyId = $conn->lastInsertId();

            // 2. Insert Branches
            if (!empty($_POST['branch_name']) && is_array($_POST['branch_name'])) {
                $stmtBranch = $conn->prepare("INSERT INTO branch (property_id, branch_name, location) VALUES (:property_id, :branch_name, :location)");
                
                $branchNames = $_POST['branch_name'];
                $branchLocations = $_POST['branch_location'] ?? [];

                for ($i = 0; $i < count($branchNames); $i++) {
                    $bName = trim($branchNames[$i]);
                    $bLoc = trim($branchLocations[$i] ?? '');

                    if (!empty($bName)) {
                        $stmtBranch->bindParam(':property_id', $propertyId, PDO::PARAM_INT);
                        $stmtBranch->bindParam(':branch_name', $bName);
                        $stmtBranch->bindParam(':location', $bLoc);
                        $stmtBranch->execute();
                    }
                }
            }

            $conn->commit();
            $_SESSION['message'] = "Property and branch(es) added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error saving record: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Database connection failed.";
        $_SESSION['message_type'] = "danger";
    }
}

header('Location: newproperty.php');
exit;