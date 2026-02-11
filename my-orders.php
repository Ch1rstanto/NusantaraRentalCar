<?php
// Nyalakan laporan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Sementara menggunakan user_id = 1 (Nanti diganti Member 1 dengan session)
$user_id = 1; 

// Query untuk mengambil data pesanan beserta nama mobilnya
$sql = "SELECT orders.*, cars.name AS car_name, cars.price_per_day 
        FROM orders 
        JOIN cars ON orders.car_id = cars.id 
        WHERE orders.user_id = $user_id 
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Nusantara Rental</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: auto; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-home { background: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; }
        
        /* Style Tabel */
        .table-container { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8f9fa; padding: 15px; text-align: left; font-size: 14px; color: #666; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 15px; }
        
        /* Status Badges */
        .badge { padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .pending { background: #fff3cd; color: #856404; }
        .approved { background: #d4edda; color: #155724; }
        .cancelled { background: #f8d7da; color: #721c24; }
        .completed { background: #d1ecf1; color: #0c5460; }
        
        .car-name { font-weight: bold; color: #007bff; }
        .total-price { font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h1>Riwayat Pesanan</h1>
        <a href="cars.php" class="btn-home">Sewa Mobil Lagi</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Mobil</th>
                    <th>Tanggal Sewa</th>
                    <th>Durasi</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="car-name"><?php echo $row['car_name']; ?></div>
                            <small style="color: #999;">Tipe: <?php echo ucfirst($row['order_type']); ?></small>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['rental_start_date'])); ?></td>
                        <td><?php echo $row['duration_days']; ?> Hari</td>
                        <td class="total-price">Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge <?php echo $row['status']; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                            Belum ada pesanan yang dibuat.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>