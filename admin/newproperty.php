<?php
session_start();
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';

unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Property & Branches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Create Property & Branches</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="processproperty.php" method="POST">
                        
                        <!-- Property Details -->
                        <h5 class="text-secondary border-bottom pb-2">Property Details</h5>
                        
                        <div class="mb-3">
                            <label for="property_name" class="form-label">Property Name</label>
                            <input type="text" maxlength="255" class="form-control" id="property_name" name="property_name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_type" class="form-label">Property Type</label>
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value="" disabled selected>Select Type</option>
                                    <option value="Residential">Residential</option>
                                    <option value="Apartments">Apartments</option>
                                    <option value="BNB">BNB</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="numberofblocks" class="form-label">Number of Blocks</label>
                                <input type="number" class="form-control" id="numberofblocks" name="numberofblocks" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="location" class="form-label">Property Location</label>
                            <input type="text" maxlength="20" class="form-control" id="location" name="location" required>
                        </div>

                        <!-- Branch Details Section -->
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="text-secondary mb-0">Branch Details</h5>
                            <button type="button" id="add-branch-btn" class="btn btn-sm btn-outline-success">+ Add Another Branch</button>
                        </div>

                        <div id="branch-container">
                            <div class="row g-2 mb-3 align-items-end branch-row">
                                <div class="col-md-6">
                                    <label class="form-label">Branch Name</label>
                                    <input type="text" maxlength="20" name="branch_name[]" class="form-control" placeholder="Branch Name" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Branch Location</label>
                                    <input type="text" maxlength="20" name="branch_location[]" class="form-control" placeholder="Location">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger remove-branch-btn w-100" disabled>&times;</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Save Property & Branches</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="property.js"></script>
</body>
</html>