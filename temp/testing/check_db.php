<?php
require 'config.php';
$conn = getDBConnection();

if (!$conn) {
    die("DB connection failed.");
}

echo "<h3>Connected to database: " . $DB_NAME . "</h3>";

$result = $conn->query("SHOW TABLES");
echo "<h4>Tables:</h4>";
while ($row = $result->fetch_array()) {
    echo $row[0] . "<br>";
}

echo "<hr><h4>Columns in active_users:</h4>";
$result = $conn->query("SHOW COLUMNS FROM active_users");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "<br>";
}
?>
