<?php
// Database configuration
$host = 'localhost';
$dbname = 'newdb-ppdb';
$dbusername = 'root';
$password = '';

// Create connection
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $password);
  // Set the PDO error mode to exception
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}
?>

