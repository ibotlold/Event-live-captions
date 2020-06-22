<?php
$servername = 'localhost';
$username = 'reader';
$password = '';
$dbname = 'texttranslation';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
	http_response_code(404);
	exit;
}
?>