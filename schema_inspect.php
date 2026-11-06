<?php
$db = new PDO('mysql:host=localhost;dbname=pms', 'root', '');
$tables = ['manager','property_manager','manager_properties','property','properties','landlord_properties'];
foreach ($tables as $t) {
    $stmt = $db->query("SHOW TABLES LIKE '$t'");
    $res = $stmt ? $stmt->fetchColumn() : false;
    echo "$t: " . ($res ? $res : 'MISSING') . "\n";
}
echo "\n";
foreach ($tables as $t) {
    $stmt = $db->query("SHOW TABLES LIKE '$t'");
    if ($stmt && $stmt->fetchColumn()) {
        echo "DESCRIBE $t\n";
        $cols = $db->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo $col['Field'] . ' ' . $col['Type'] . "\n";
        }
        echo "\n";
    }
}
