<?php
// Nyalakan laporan error untuk memudahkan pengecekan
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Koneksi ke database
require_once 'config/database.php';

// 2. Ambil ID mobil dari URL (default ke ID 1 jika tidak ada)
$car_id = isset($_GET['id']) ? $_GET['id'] : 1;

// 3. Ambil data mobil dari database
$sql = "SELECT * FROM cars WHERE id = $car_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $car = $result->fetch_assoc();
} else {
    die("Mobil dengan ID $car_id tidak ditemukan. Silakan isi data mobil di phpMyAdmin terlebih dahulu.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa <?php echo $car['name']; ?> - Nusantara Rental</title>
    <style>
        /* STYLE HALAMAN & FORM */
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 550px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin: auto; }
        h2 { margin-top: 0; color: #007bff; }
        label { display: block; margin-top: 15px; font-weight: 600; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        
        /* Tombol Form */
        .btn-group { margin-top: 25px; }
        button { cursor: pointer; border: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-web { background-color: #28a745; color: white; width: 100%; padding: 15px; margin-bottom: 10px; font-size: 16px; }
        .btn-web:hover { background-color: #218838; }
        .btn-wa { background-color: #25d366; color: white; width: 100%; padding: 15px; font-size: 16px; }
        .btn-wa:hover { background-color: #1ebd5b; }
        
        .total-box { background: #e9ecef; padding: 15px; border-radius: 8px; margin-top: 20px; font-size: 1.2em; font-weight: bold; text-align: center; }
        .total-price { color: #007bff; }

        /* STYLE CHATBOX AI (PERBAIKAN INPUT HORIZONTAL) */
        #chat-wrapper { position: fixed; bottom: 20px; right: 20px; z-index: 9999; }
        #chat-toggle { background: #007bff; color: white; border: none; padding: 15px 25px; border-radius: 50px; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-weight: bold; }
        
        #chat-window { display: none; width: 350px; height: 480px; background: white; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; margin-bottom: 15px; }
        #chat-header { background: #007bff; color: white; padding: 15px; font-weight: bold; text-align: center; }
        #chat-content { flex: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; display: flex; flex-direction: column; gap: 10px; }
        
        /* Balon Chat */
        .msg { padding: 10px 14px; border-radius: 15px; font-size: 14px; line-height: 1.4; max-width: 80%; }
        .msg-ai { background: #ffffff; color: #333; align-self: flex-start; border: 1px solid #ddd; border-bottom-left-radius: 2px; }
        .msg-user { background: #007bff; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }

        /* FOOTER CHAT (BAGIAN YANG DIPERBAIKI) */
        #chat-footer { padding: 10px; border-top: 1px solid #eee; display: flex; background: white; align-items: center; }
        #chat-input { flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 20px; outline: none; font-size: 14px; width: auto; margin-top: 0; }
        #btn-send { background: #007bff; color: white; border: none; padding: 10px 18px; margin-left: 8px; border-radius: 20px; cursor: pointer; width: auto; margin-top: 0; }
    </style>
</head>
<body>

<div class="container">
    <h2>Konfirmasi Sewa</h2>
    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 5px solid #007bff;">
        <strong>Mobil:</strong> <?php echo $car['name']; ?> (<?php echo $car['transmission']; ?>)<br>
        <strong>Harga:</strong> Rp <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?> / hari
    </div>

    <form action="api/orders.php" method="POST">
        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
        <input type="hidden" id="price_per_day" value="<?php echo $car['price_per_day']; ?>">

        <label>Pilih Tanggal Mulai:</label>
        <input type="date" name="rental_start_date" required>

        <label>Durasi Sewa (Hari):</label>
        <input type="number" name="duration_days" id="duration" min="1" placeholder="Masukkan jumlah hari..." required>

        <label>Opsi Pengambilan:</label>
        <select name="delivery_option">
            <option value="pickup">Ambil Sendiri di Showroom</option>
            <option value="delivery">Antar ke Alamat Saya</option>
        </select>

        <label>Catatan atau Alamat Lengkap:</label>
        <textarea name="notes" rows="3" placeholder="Contoh: Tolong antar mobil jam 8 pagi ke Alamat..."></textarea>

        <div class="total-box">
            Total Estimasi: <span class="total-price" id="total_display">Rp 0</span>
        </div>

        <div class="btn-group">
            <button type="submit" name="order_type" value="website" class="btn-web">Konfirmasi Pesanan</button>
            <button type="submit" name="order_type" value="whatsapp" class="btn-wa">Pesan via WhatsApp</button>
        </div>
    </form>
    
    <p style="text-align: center;"><a href="cars.php" style="color: #888; text-decoration: none; font-size: 14px;">Kembali pilih mobil</a></p>
</div>

<div id="chat-wrapper">
    <div id="chat-window">
        <div id="chat-header">Asisten Nusantara AI</div>
        <div id="chat-content">
            <div class="msg msg-ai">Halo! Ada yang bisa saya bantu? Coba tanya "mobil keluarga" atau "harga murah".</div>
        </div>
        <div id="chat-footer">
            <input type="text" id="chat-input" placeholder="Ketik pesan..." autocomplete="off">
            <button id="btn-send" onclick="handleSend()">Kirim</button>
        </div>
    </div>
    <button id="chat-toggle" onclick="toggleChat()">💬 Tanya AI</button>
</div>



<script>
    /* 1. HITUNG HARGA OTOMATIS */
    const durationInput = document.getElementById('duration');
    const pricePerDay = document.getElementById('price_per_day').value;
    const totalDisplay = document.getElementById('total_display');

    durationInput.addEventListener('input', function() {
        const days = durationInput.value;
        if (days > 0) {
            const total = days * pricePerDay;
            totalDisplay.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        } else {
            totalDisplay.innerText = 'Rp 0';
        }
    });

    /* 2. LOGIKA CHATBOX */
    function toggleChat() {
        const win = document.getElementById('chat-window');
        win.style.display = (win.style.display === 'none' || win.style.display === '') ? 'flex' : 'none';
    }

    async function handleSend() {
        const input = document.getElementById('chat-input');
        const content = document.getElementById('chat-content');
        const msgText = input.value.trim();

        if (!msgText) return;

        // Tampilkan pesan User
        content.innerHTML += `<div class="msg msg-user">${msgText}</div>`;
        input.value = '';
        content.scrollTop = content.scrollHeight;

        try {
            const response = await fetch('api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msgText })
            });
            const data = await response.json();

            // Tampilkan Balasan AI
            content.innerHTML += `<div class="msg msg-ai">${data.response}</div>`;
            content.scrollTop = content.scrollHeight;
        } catch (e) {
            content.innerHTML += `<div class="msg msg-ai">Maaf, koneksi ke server AI terputus.</div>`;
        }
    }

    // Tekan Enter untuk kirim chat
    document.getElementById('chat-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') handleSend();
    });
</script>

</body>
</html>