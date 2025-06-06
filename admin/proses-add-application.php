<?php
// Pastikan file ini hanya diakses dari add-application.php
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

if (!isset($_SESSION['admin_id'])) {
    exit('Akses tidak diizinkan');
}

// Modifikasi validasi user_id dan email
$user_id = $_POST['user_id'] ?? '';
$registration_number = $_POST['registration_number'] ?? '';

if (empty($registration_number)) {
    $error = 'Nomor pendaftaran harus diisi';
    return;
}

// Validasi nomor pendaftaran unik - Updated for new schema
$checkRegStmt = $pdo->prepare("SELECT id FROM applicants WHERE nomor_pendaftaran = ?");
$checkRegStmt->execute([$registration_number]);
if ($checkRegStmt->rowCount() > 0) {
    $error = 'Nomor pendaftaran sudah digunakan. Silakan gunakan nomor lain.';
    return;
}

// Jika user_id tidak diisi, buat user baru
if (empty($user_id)) {
    // Generate username unik berdasarkan nama
    $fullName = $_POST['fullName'] ?? '';
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));
    if (empty($baseUsername)) {
        $baseUsername = 'user';
    }

    $username = $baseUsername;
    $counter = 1;

    // Cek apakah username sudah ada - Updated for new schema
    $checkUsernameStmt = $pdo->prepare("SELECT id FROM users WHERE nama_pengguna = ?");
    $checkUsernameStmt->execute([$username]);

    // Jika username sudah ada, tambahkan angka di belakangnya
    while ($checkUsernameStmt->rowCount() > 0) {
        $username = $baseUsername . $counter;
        $counter++;
        $checkUsernameStmt->execute([$username]);
    }

    // Generate password random
    $password = substr(md5(rand()), 0, 8);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Ambil email jika diisi
    $email = $_POST['email'] ?? '';

    // PERBAIKAN: Jika email kosong, buat email dummy yang unik
    if (empty($email)) {
        $email = $username . '_' . time() . '@example.com';
    }

    // Buat user baru - Updated for new schema
    $insertUserStmt = $pdo->prepare("INSERT INTO users (nama_pengguna, kata_sandi, email, nama) VALUES (?, ?, ?, ?)");
    $insertUserResult = $insertUserStmt->execute([$username, $hashedPassword, $email, $fullName]);

    if ($insertUserResult) {
        $user_id = $pdo->lastInsertId();

        // Tambahkan informasi user baru ke pesan sukses
        $userInfo = "Username: $username, Password: $password";
    } else {
        $error = 'Gagal membuat user baru. Silakan coba lagi.';
        return;
    }
} else {
    // Validasi apakah user sudah memiliki pendaftaran - Updated for new schema
    $checkStmt = $pdo->prepare("SELECT id FROM applicants WHERE user_id = ?");
    $checkStmt->execute([$user_id]);
    if ($checkStmt->rowCount() > 0) {
        $error = 'User ini sudah memiliki pendaftaran. Silakan pilih user lain.';
        return;
    }

    // Get user email if not provided - Updated for new schema
    if (empty($_POST['email'])) {
        $userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user = $userStmt->fetch();
        $_POST['email'] = $user['email'] ?? '';
    }
}

// Validasi ukuran file
$maxFileSize = 2 * 1024 * 1024; // 2MB dalam bytes
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
$fileErrors = [];

// Fungsi untuk validasi file
function validateFile($file, $maxSize, $allowedTypes)
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // File tidak diupload, itu OK untuk admin
    }

    if ($file['size'] > $maxSize) {
        return "File " . basename($file['name']) . " terlalu besar. Maksimal 2MB.";
    }

    if (!in_array($file['type'], $allowedTypes) && $file['size'] > 0) {
        return "File " . basename($file['name']) . " harus ber format JPG, PNG, atau PDF.";
    }

    return null;
}

// Validasi semua file yang diupload
$fileFields = [
    'certificate',
    'birthCertificate',
    'familyCard',
    'photo',
    'elementaryCertificate',
    'mdaCertificate',
    'skhun',
    'nisnFile',
    'parentIdCard',
    'socialCard',
    'graduationLetter'
];

foreach ($fileFields as $fileKey) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
        $fileError = validateFile($_FILES[$fileKey], $maxFileSize, $allowedTypes);
        if ($fileError) {
            $fileErrors[] = $fileError;
        }
    }
}

// Jika ada error file, tampilkan pesan error
if (!empty($fileErrors)) {
    $error = implode("<br>", $fileErrors);
    return;
}

// Upload directory - PERBAIKAN PATH UPLOAD
// Pastikan path relatif terhadap root project
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Function to handle file upload - PERBAIKAN PATH YANG DISIMPAN KE DATABASE
function handleFileUpload($fileKey, $uploadDir)
{
    if ($_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    $fileName = time() . '_' . $_FILES[$fileKey]['name'];
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $filePath)) {
        // Simpan path relatif ke database (tanpa '../')
        return 'uploads/' . $fileName;
    }

    return '';
}

// Upload files
$certificateFile = handleFileUpload('certificate', $uploadDir);
$birthCertificateFile = handleFileUpload('birthCertificate', $uploadDir);
$familyCardFile = handleFileUpload('familyCard', $uploadDir);
$photoFile = handleFileUpload('photo', $uploadDir);
$elementaryCertificateFile = handleFileUpload('elementaryCertificate', $uploadDir);
$mdaCertificateFile = handleFileUpload('mdaCertificate', $uploadDir);
$skhunFile = handleFileUpload('skhun', $uploadDir);
$nisnFile = handleFileUpload('nisnFile', $uploadDir);
$parentIdCardFile = handleFileUpload('parentIdCard', $uploadDir);
$socialCardFile = handleFileUpload('socialCard', $uploadDir);
$graduationLetterFile = handleFileUpload('graduationLetter', $uploadDir);

// Map gender value from English to Indonesian
$genderValue = '';
if ($_POST['gender'] === 'male') {
    $genderValue = 'laki-laki';
} else if ($_POST['gender'] === 'female') {
    $genderValue = 'perempuan';
}

// Modifikasi bagian insert data - Updated for new schema
$stmt = $pdo->prepare("INSERT INTO applicants (
    user_id, nomor_pendaftaran, nama_lengkap, nisn, tempat_lahir, tanggal_lahir, 
    jenis_kelamin, agama, alamat, telepon, email, nama_ayah, pekerjaan_ayah, 
    nama_ibu, pekerjaan_ibu, telepon_orangtua, nama_sekolah, alamat_sekolah, 
    tahun_lulus, file_ijazah, file_akta_kelahiran, file_kartu_keluarga, file_foto,
    file_ijazah_sd, file_ijazah_mda, file_skhun, file_nisn,
    file_ktp_orangtua, file_kartu_sosial, file_surat_lulus, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'diterima')");

$result = $stmt->execute([
    $user_id,
    $registration_number,
    $_POST['fullName'],
    $_POST['nisn'],
    $_POST['birthPlace'],
    $_POST['birthDate'],
    $genderValue,
    $_POST['religion'],
    $_POST['address'],
    $_POST['phone'],
    $_POST['email'],
    $_POST['fatherName'],
    $_POST['fatherJob'],
    $_POST['motherName'],
    $_POST['motherJob'],
    $_POST['parentPhone'],
    $_POST['schoolName'],
    $_POST['schoolAddress'],
    $_POST['graduationYear'],
    $certificateFile,
    $birthCertificateFile,
    $familyCardFile,
    $photoFile,
    $elementaryCertificateFile,
    $mdaCertificateFile,
    $skhunFile,
    $nisnFile,
    $parentIdCardFile,
    $socialCardFile,
    $graduationLetterFile
]);

if ($result) {
    // Kirim email notifikasi jika ada email
    $emailSent = false;
    if (!empty($_POST['email'])) {
        require_once '../includes/emailHelper.php';

        $subject = "Pendaftaran PPDB Anda Telah Diterima - " . $registration_number;
        $emailContent = getStatusUpdateEmailTemplate($_POST['fullName'], $registration_number, 'Diterima', '');

        // Send email
        $emailSent = sendEmail($_POST['email'], $subject, $emailContent);
    }

    // Set success message
    $success = 'Pendaftaran berhasil ditambahkan dengan status DITERIMA.';

    // Tambahkan info user jika user baru dibuat
    if (isset($userInfo)) {
        $success .= '<br><strong>User baru telah dibuat:</strong> ' . $userInfo;
    }

    if ($emailSent) {
        $success .= '<br>Email notifikasi telah dikirim ke peserta.';
    } else if (!empty($_POST['email'])) {
        $success .= '<br>Namun email notifikasi gagal dikirim.';
    }

    // Log email status jika ada email
    if (!empty($_POST['email'])) {
        $logFile = __DIR__ . '/../logs/email_status.log';
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logMessage = date('Y-m-d H:i:s') . " - Email ke {$_POST['email']}: " .
            ($emailSent ? "BERHASIL" : "GAGAL") .
            ", Status: diterima, Nomor: $registration_number\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
} else {
    $error = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
}