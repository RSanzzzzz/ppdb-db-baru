<?php
/**
* Email Helper Functions
* Fungsi-fungsi untuk mengirim email notifikasi
*/

// Tambahkan PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
* Fungsi untuk mengirim email notifikasi
* 
* @param string $to Email penerima
* @param string $subject Subjek email
* @param string $message Isi email (HTML)
* @return bool Status pengiriman email
*/
function sendEmail($to, $subject, $message) {
    // Cek apakah kita menggunakan PHPMailer atau fungsi mail() bawaan
    $usePhpMailer = true; // Set ke false jika ingin menggunakan fungsi mail() bawaan PHP
    
    // Log pengiriman email
    $logFile = __DIR__ . '/../logs/email.log';
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logMessage = date('Y-m-d H:i:s') . " - Mencoba mengirim email ke: $to, Subjek: $subject\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    if ($usePhpMailer) {
        return sendWithPhpMailer($to, $subject, $message, $logFile);
    } else {
        return sendWithMailFunction($to, $subject, $message, $logFile);
    }
}

/**
* Fungsi untuk mengirim email menggunakan PHPMailer
*/
function sendWithPhpMailer($to, $subject, $message, $logFile) {
    // Cek apakah PHPMailer sudah diinstal
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        // Jika belum, gunakan fungsi mail() bawaan
        file_put_contents($logFile, "PHPMailer tidak ditemukan, menggunakan fungsi mail() bawaan\n", FILE_APPEND);
        return sendWithMailFunction($to, $subject, $message, $logFile);
    }
    
    // Load PHPMailer
    require __DIR__ . '/../vendor/autoload.php';
    
    try {
        $mail = new PHPMailer(true);
        
        // Konfigurasi server
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Ganti dengan SMTP server Anda
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saranclip@gmail.com'; // Ganti dengan email Anda
        $mail->Password   = 'scrt qtaf mcwk bucd'; // Ganti dengan password atau app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Penerima
        $mail->setFrom('noreply@ppdbonline.com', 'PPDB Online');
        $mail->addAddress($to);
        
        // Konten
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->CharSet = 'UTF-8';
        
        // Kirim email
        $mail->send();
        file_put_contents($logFile, "Email berhasil dikirim menggunakan PHPMailer\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        file_put_contents($logFile, "Gagal mengirim email dengan PHPMailer: {$mail->ErrorInfo}\n", FILE_APPEND);
        // Jika gagal dengan PHPMailer, coba dengan fungsi mail() bawaan
        return sendWithMailFunction($to, $subject, $message, $logFile);
    }
}

/**
* Fungsi untuk mengirim email menggunakan fungsi mail() bawaan PHP
*/
function sendWithMailFunction($to, $subject, $message, $logFile) {
    // Headers email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: PPDB Online <noreply@ppdbonline.com>" . "\r\n";
    
    // Kirim email
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        file_put_contents($logFile, "Email berhasil dikirim menggunakan fungsi mail() bawaan\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "Gagal mengirim email dengan fungsi mail() bawaan\n", FILE_APPEND);
    }
    
    return $result;
}

/**
* Fungsi untuk membuat template email status pendaftaran
* 
* @param string $name Nama peserta
* @param string $regNumber Nomor pendaftaran
* @param string $status Status pendaftaran baru
* @param string $notes Catatan admin (opsional)
* @return string Template email dalam format HTML
*/
function getStatusUpdateEmailTemplate($name, $regNumber, $status, $notes = '') {
    // Terjemahkan status ke Bahasa Indonesia
    $statusText = '';
    $statusColor = '';
    switch ($status) {
        case 'pending':
            $statusText = 'Menunggu Verifikasi';
            $statusColor = '#f0ad4e'; // Warna kuning
            break;
        case 'verified':
            $statusText = 'Terverifikasi';
            $statusColor = '#5bc0de'; // Warna biru
            break;
        case 'accepted':
            $statusText = 'Diterima';
            $statusColor = '#5cb85c'; // Warna hijau
            break;
        case 'rejected':
            $statusText = 'Ditolak';
            $statusColor = '#d9534f'; // Warna merah
            break;
        default:
            $statusText = 'Menunggu Verifikasi';
            $statusColor = '#f0ad4e';
    }
    
    // Template email
    $template = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Update Status Pendaftaran PPDB</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
            }
            .container {
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .header {
                background-color: #3d84e1;
                color: white;
                padding: 10px 20px;
                border-radius: 5px 5px 0 0;
                margin-bottom: 20px;
            }
            .status {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 3px;
                font-weight: bold;
                color: white;
                background-color: ' . $statusColor . ';
            }
            .notes {
                background-color: #f9f9f9;
                border-left: 3px solid #3d84e1;
                padding: 10px 15px;
                margin: 20px 0;
            }
            .footer {
                margin-top: 30px;
                font-size: 12px;
                color: #777;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Update Status Pendaftaran PPDB</h2>
            </div>
            
            <p>Yth. <strong>' . htmlspecialchars($name) . '</strong>,</p>
            
            <p>Status pendaftaran PPDB Anda dengan nomor pendaftaran <strong>' . htmlspecialchars($regNumber) . '</strong> telah diperbarui.</p>
            
            <p>Status pendaftaran Anda saat ini: <span class="status">' . $statusText . '</span></p>';
    
    // Tambahkan catatan admin jika ada
    if (!empty($notes)) {
        $template .= '
            <div class="notes">
                <h3>Catatan dari Admin:</h3>
                <p>' . nl2br(htmlspecialchars($notes)) . '</p>
            </div>';
    }
    
    // Tambahkan instruksi berdasarkan status
    switch ($status) {
        case 'verified':
            $template .= '<p>Pendaftaran Anda telah diverifikasi. Silakan menunggu pengumuman hasil seleksi.</p>';
            break;
        case 'accepted':
            $template .= '<p>Selamat! Anda telah diterima. Silakan melakukan daftar ulang sesuai jadwal yang telah ditentukan.</p>';
            break;
        case 'rejected':
            $template .= '<p>Mohon maaf, pendaftaran Anda tidak dapat diterima. Silakan hubungi panitia PPDB untuk informasi lebih lanjut.</p>';
            break;
        default:
            $template .= '<p>Pendaftaran Anda sedang dalam proses verifikasi. Silakan cek status pendaftaran secara berkala.</p>';
    }
    
    $template .= '
            <p>Untuk informasi lebih lanjut, silakan login ke akun PPDB Anda atau hubungi panitia PPDB.</p>
            
            <div class="footer">
                <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                <p>&copy; ' . date('Y') . ' PPDB Online. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $template;
}

