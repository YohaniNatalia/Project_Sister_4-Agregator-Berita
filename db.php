<?php
$servername = "localhost";
$username = "root";
$password = ""; // Kosong jika menggunakan XAMPP
$dbname = "Berita";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
