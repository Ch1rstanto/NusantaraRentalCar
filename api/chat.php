<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = mysqli_real_escape_string($conn, strtolower($data['message'] ?? ''));

$reply = "Maaf, saya belum paham. Coba tanya tentang 'mobil keluarga' atau 'paling murah'.";

// Logika Rekomendasi
if (strpos($userMessage, 'keluarga') !== false) {
    $res = $conn->query("SELECT name FROM cars WHERE seats >= 7 LIMIT 1");
    if($car = $res->fetch_assoc()) {
        $reply = "Untuk keluarga, saya rekomendasikan " . $car['name'] . " karena kapasitasnya luas.";
    }
} elseif (strpos($userMessage, 'murah') !== false) {
    $res = $conn->query("SELECT name, price_per_day FROM cars ORDER BY price_per_day ASC LIMIT 1");
    if($car = $res->fetch_assoc()) {
        $reply = "Mobil termurah kami saat ini adalah " . $car['name'] . " hanya Rp " . number_format($car['price_per_day'], 0, ',', '.') . " per hari.";
    }
}

// SIMPAN KE DATABASE (Tabel yang sudah ada)
// Kita beri session_id buatan sendiri dulu karena Member 1 belum selesai
$session_id = "user_guest_01"; 
$sql_history = "INSERT INTO chat_history (session_id, message, response) VALUES ('$session_id', '$userMessage', '$reply')";
$conn->query($sql_history);

echo json_encode(['response' => $reply]);