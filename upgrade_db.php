<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "Adding blood_group_id to users table...\n";

try {
    // Check if column exists
    $col_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'blood_group_id'");
    if ($col_check->rowCount() == 0) {
        $sql = "ALTER TABLE users ADD COLUMN blood_group_id INT(11) NULL AFTER email, ADD CONSTRAINT fk_user_blood_group FOREIGN KEY (blood_group_id) REFERENCES blood_groups(id) ON DELETE SET NULL";
        $pdo->exec($sql);
        echo "Column blood_group_id added successfully.\n";
    } else {
        echo "Column blood_group_id already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>