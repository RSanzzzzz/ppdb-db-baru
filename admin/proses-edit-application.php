<?php
// File ini digunakan oleh edit-application.php
// Tidak untuk diakses langsung

// Pastikan file ini hanya diakses dari edit-application.php
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

if (!isset($_SESSION['admin_id']) || !isset($applicationId)) {
    exit('Akses tidak diizinkan');
}

// Ambil data dari form
$user_id = $_POST['user_id'] ?? '';
$registration_number = $_POST['registration_number'] ?? '';
$status = $_POST['status'] ?? '';
$admin_notes = $_POST['admin_notes'] ?? '';

// Validasi data dasar
if (empty($registration_number)) {
    $error = 'Nomor pendaftaran harus diisi';
    return;
}

// Validasi nomor pendaftaran unik (kecuali untuk pendaftaran yang sedang diedit) - Updated for new schema
$checkRegStmt = $pdo->prepare("SELECT id FROM applicants WHERE nomor_pendaftaran = ? AND id != ?");
$checkRegStmt->execute([$registration_number, $applicationId]);
if ($checkRegStmt->rowCount() > 0) {
    $error = 'Nomor pendaftaran sudah digunakan. Silakan gunakan nomor lain.';
    return;
}

// Validasi ukuran file
$maxFileSize = 2 * 1024 * 1024; // 2MB dalam bytes
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
$fileErrors = [];

// Fungsi untuk validasi file
function validateFile($file, $maxSize, $allowedTypes)
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // File tidak diupload, itu OK untuk edit
    }

    if ($file['size'] > $maxSize) {
        return "File " . basename($file['name']) . " terlalu besar. Maksimal 2MB.";
    }

    if (!in_array($file['type'], $allowedTypes) && $file['size'] > 0) {
        return "File " . basename($file['name']) . " harus berformat JPG, PNG, atau PDF.";
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

// Upload directory
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Function to handle file upload
function handleFileUpload($fileKey, $uploadDir, $oldFilePath)
{
    if ($_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFilePath; // Kembalikan path file lama jika tidak ada file baru
    }

    $fileName = time() . '_' . $_FILES[$fileKey]['name'];
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $filePath)) {
        // Hapus file lama jika ada
        if (!empty($oldFilePath) && file_exists('../' . $oldFilePath)) {
            @unlink('../' . $oldFilePath);
        }

        // Simpan path relatif ke database (tanpa '../')
        return 'uploads/' . $fileName;
    }

    return $oldFilePath; // Kembalikan path file lama jika upload gagal
}

// Upload files - Updated for new schema
$certificateFile = handleFileUpload('certificate', $uploadDir, $application['file_ijazah']);
$birthCertificateFile = handleFileUpload('birthCertificate', $uploadDir, $application['file_akta_kelahiran']);
$familyCardFile = handleFileUpload('familyCard', $uploadDir, $application['file_kartu_keluarga']);
$photoFile = handleFileUpload('photo', $uploadDir, $application['file_foto']);
$elementaryCertificateFile = handleFileUpload('elementaryCertificate', $uploadDir, $application['file_ijazah_sd']);
$mdaCertificateFile = handleFileUpload('mdaCertificate', $uploadDir, $application['file_ijazah_mda']);
$skhunFile = handleFileUpload('skhun', $uploadDir, $application['file_skhun']);
$nisnFile = handleFileUpload('nisnFile', $uploadDir, $application['file_nisn']);
$parentIdCardFile = handleFileUpload('parentIdCard', $uploadDir, $application['file_ktp_orangtua']);
$socialCardFile = handleFileUpload('socialCard', $uploadDir, $application['file_kartu_sosial']);
$graduationLetterFile = handleFileUpload('graduationLetter', $uploadDir, $application['file_surat_lulus']);

// Map gender value from English to Indonesian if needed
$genderValue = $_POST['gender'];
if ($genderValue === 'male') {
    $genderValue = 'laki-laki';
} else if ($genderValue === 'female') {
    $genderValue = 'perempuan';
}

// Update data pendaftaran - Updated for new schema
$stmt = $pdo->prepare("UPDATE applicants SET 
   user_id = ?, 
   nomor_pendaftaran = ?, 
   nama_lengkap = ?, 
   nisn = ?, 
   tempat_lahir = ?, 
   tanggal_lahir = ?, 
   jenis_kelamin = ?, 
   agama = ?, 
   alamat = ?, 
   telepon = ?, 
   email = ?, 
   nama_ayah = ?, 
   pekerjaan_ayah = ?, 
   nama_ibu = ?, 
   pekerjaan_ibu = ?, 
   telepon_orangtua = ?, 
   nama_sekolah = ?, 
   alamat_sekolah = ?, 
   tahun_lulus = ?, 
   file_ijazah = ?, 
   file_akta_kelahiran = ?, 
   file_kartu_keluarga = ?, 
   file_foto = ?,
   file_ijazah_sd = ?, 
   file_ijazah_mda = ?, 
   file_skhun = ?, 
   file_nisn = ?,
   file_ktp_orangtua = ?, 
   file_kartu_sosial = ?, 
   file_surat_lulus = ?,
   status = ?,
   catatan_admin = ?
WHERE id = ?");

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
    $graduationLetterFile,
    $status,
    $admin_notes,
    $applicationId
]);

if ($result) {
    // Kirim email notifikasi jika status berubah - Updated for new schema
    $statusChanged = $application['status'] !== $status;
    $notesChanged = $application['catatan_admin'] !== $admin_notes;

    if (($statusChanged || $notesChanged) && !empty($_POST['email'])) {
        require_once '../includes/emailHelper.php';

        $subject = "Update Status Pendaftaran PPDB - " . $registration_number;
        
        // Map status to display text for email
        $statusDisplayText = '';
        switch ($status) {
            case 'menunggu':
                $statusDisplayText = 'Menunggu Verifikasi';
                break;
            case 'terverifikasi':
                $statusDisplayText = 'Terverifikasi';
                break;
            case 'diterima':
                $statusDisplayText = 'Diterima';
                break;
            case 'ditolak':
                $statusDisplayText = 'Ditolak';
                break;
            default:
                $statusDisplayText = $status; // Use as-is if not mapped
        }
        
        $emailContent = getStatusUpdateEmailTemplate($_POST['fullName'], $registration_number, $status, $admin_notes);

        // Send email
        $emailSent = sendEmail($_POST['email'], $subject, $emailContent);

        // Log email status
        $logFile = __DIR__ . '/../logs/email_status.log';
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logMessage = date('Y-m-d H:i:s') . " - Email ke {$_POST['email']}: " .
            ($emailSent ? "BERHASIL" : "GAGAL") .
            ", Status: $statusDisplayText, ID: $applicationId\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        if ($emailSent) {
            $success = 'Data pendaftaran berhasil diperbarui dan email notifikasi telah dikirim.';
        } else {
            $success = 'Data pendaftaran berhasil diperbarui, namun email notifikasi gagal dikirim.';
        }
    } else {
        $success = 'Data pendaftaran berhasil diperbarui.';
    }

    // Log perubahan - Updated for new schema
    $logFile = __DIR__ . '/../logs/admin_actions.log';
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $logMessage = date('Y-m-d H:i:s') . " - Admin ID: {$_SESSION['admin_id']} memperbarui pendaftaran ID: {$applicationId}, Nomor: {$registration_number}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
} else {
    $error = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
}