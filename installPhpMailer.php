<?php
/**
 * Script untuk menginstal PHPMailer
 * 
 * Jalankan script ini dari command line:
 * php install-phpmailer.php
 * 
 * Atau buka di browser:
 * http://localhost/ppdb/install-phpmailer.php
 */

// Cek apakah composer sudah terinstal
$composerInstalled = false;
exec('composer --version', $output, $returnVar);
$composerInstalled = $returnVar === 0;

if (!$composerInstalled) {
    echo "Composer tidak ditemukan. Silakan instal Composer terlebih dahulu: https://getcomposer.org/download/";
    exit(1);
}

// Cek apakah file composer.json sudah ada
if (!file_exists('composer.json')) {
    // Buat file composer.json
    $composerJson = [
        'require' => [
            'phpmailer/phpmailer' => '^6.8'
        ]
    ];
    
    file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
    echo "File composer.json telah dibuat.\n";
}

// Jalankan composer install
echo "Menginstal PHPMailer...\n";
exec('composer install', $output, $returnVar);

if ($returnVar !== 0) {
    echo "Gagal menginstal PHPMailer. Error:\n";
    echo implode("\n", $output);
    exit(1);
}

echo "PHPMailer berhasil diinstal!\n";
echo "Sekarang Anda dapat menggunakan PHPMailer untuk mengirim email.\n";

// Buat direktori logs jika belum ada
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
    echo "Direktori logs telah dibuat.\n";
}

echo "\nPengaturan email selesai. Silakan edit file includes/email-helper.php untuk mengkonfigurasi SMTP server Anda.\n";
