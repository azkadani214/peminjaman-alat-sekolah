<?php
require '../../config/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["login"])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id'] ?? '');

if (empty($id)) {
    echo json_encode(['exists' => false]);
    exit;
}

$query = "SELECT id_alat_olahraga FROM alat_olahraga WHERE id_alat_olahraga = '$id'";
$res = mysqli_query($connect, $query);

if (mysqli_num_rows($res) > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}
?>
