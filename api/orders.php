<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Hubungkan ke database
require_once '../config/database.php';

// 2. Cek apakah tombol pesan diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dari form
    $user_id = 1; // Sementara diset 1 (Login akan diurus Member 1 nanti)
    $car_id = $_POST['car_id'];
    $rental_start_date = $_POST['rental_start_date'];
    $duration_days = $_POST['duration_days'];
    $delivery_option = $_POST['delivery_option'];
    $notes = $_POST['notes'];
    $order_type = $_POST['order_type']; // 'website' atau 'whatsapp'

    // Hitung tanggal selesai (rental_end_date)
    $rental_end_date = date('Y-m-d', strtotime($rental_start_date . " + $duration_days days"));

    // Ambil harga mobil untuk hitung total_price
    $res_car = $conn->query("SELECT price_per_day FROM cars WHERE id = $car_id");
    $car_data = $res_car->fetch_assoc();
    $total_price = $car_data['price_per_day'] * $duration_days;

    // 3. Logika Jika Pilih WhatsApp
    if ($order_type === 'whatsapp') {
        // Ambil nomor WA dari tabel site_settings (jika ada)
        $res_wa = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'whatsapp_number'");
        $wa_data = $res_wa->fetch_assoc();
        $wa_number = $wa_data['setting_value'] ?? '6281234567890'; // Default jika kosong

        $pesan_wa = "Halo Admin, saya ingin menyewa mobil ID $car_id mulai tanggal $rental_start_date selama $duration_days hari.";
        $link_wa = "https://wa.me/$wa_number?text=" . urlencode($pesan_wa);
        
        // Simpan ke database dulu sebagai catatan, lalu redirect ke WA
        $sql = "INSERT INTO orders (user_id, car_id, order_type, rental_start_date, rental_end_date, duration_days, delivery_option, total_price, status) 
                VALUES ('$user_id', '$car_id', 'whatsapp', '$rental_start_date', '$rental_end_date', '$duration_days', '$delivery_option', '$total_price', 'pending')";
        
        if ($conn->query($sql)) {
            header("Location: $link_wa");
            exit;
        }
    } 
    // 4. Logika Jika Pilih Website
    else {
        $sql = "INSERT INTO orders (user_id, car_id, order_type, rental_start_date, rental_end_date, duration_days, delivery_option, total_price, status, notes) 
                VALUES ('$user_id', '$car_id', 'website', '$rental_start_date', '$rental_end_date', '$duration_days', '$delivery_option', '$total_price', 'pending', '$notes')";

        if ($conn->query($sql)) {
            echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='../my-orders.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>