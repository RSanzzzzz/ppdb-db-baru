<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin, redirect to admin dashboard
if (isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// Get active tab from query parameter, default to 'info'
$activeTab = $_GET['tab'] ?? 'info';

// Initialize error variable
$error = '';

// Check if user has already applied BEFORE processing form
$hasApplied = false;
$applicationStatus = '';
$registrationNumber = '';
$adminNotes = '';

if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $application = $stmt->fetch();

    if ($application) {
        $hasApplied = true;
        $applicationStatus = $application['status'];
        $registrationNumber = $application['registration_number'];
        $adminNotes = $application['admin_notes'];

        // If user has already applied, show the completion tab
        if ($activeTab === 'form') {
            $activeTab = 'complete';
        }
    }
}

// Tambahkan fungsi validasi file setelah bagian "// Handle form submission"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form']) && !$hasApplied) {
    require_once 'config/database.php';

    // Generate registration number
    $registrationNumber = 'PPDB' . date('Y') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    // Handle file uploads
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Validasi ukuran file
    $maxFileSize = 2 * 1024 * 1024; // 2MB dalam bytes
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $fileErrors = [];

    // Fungsi untuk validasi file
    function validateFile($file, $maxSize, $allowedTypes)
    {
        if ($file['size'] > $maxSize) {
            return "File " . basename($file['name']) . " terlalu besar. Maksimal 2MB.";
        }

        if (!in_array($file['type'], $allowedTypes) && $file['size'] > 0) {
            return "File " . basename($file['name']) . " harus berformat JPG, PNG, atau PDF.";
        }

        return null;
    }

    // Validasi semua file yang diupload
    $requiredFiles = [
        'certificate',
        'birthCertificate',
        'familyCard',
        'photo',
        'elementaryCertificate',
        'mdaCertificate',
        'skhun',
        'nisnFile',
        'parentIdCard'
    ];

    foreach ($requiredFiles as $fileKey) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = validateFile($_FILES[$fileKey], $maxFileSize, $allowedTypes);
            if ($error) {
                $fileErrors[] = $error;
            }
        } else if ($_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            $fileErrors[] = "File " . ucfirst($fileKey) . " wajib diupload.";
        }
    }

    // Validasi file opsional
    $optionalFiles = ['socialCard', 'graduationLetter'];
    foreach ($optionalFiles as $fileKey) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = validateFile($_FILES[$fileKey], $maxFileSize, $allowedTypes);
            if ($error) {
                $fileErrors[] = $error;
            }
        }
    }

    // Jika ada error file, tampilkan pesan error
    if (!empty($fileErrors)) {
        $error = implode("<br>", $fileErrors);
    } else {
        // Tambahkan kode untuk melanjutkan proses upload setelah validasi
        // Process file uploads jika tidak ada error
        $certificateFile = $_FILES['certificate']['name'] ? $uploadDir . time() . '_' . $_FILES['certificate']['name'] : '';
        $birthCertificateFile = $_FILES['birthCertificate']['name'] ? $uploadDir . time() . '_' . $_FILES['birthCertificate']['name'] : '';
        $familyCardFile = $_FILES['familyCard']['name'] ? $uploadDir . time() . '_' . $_FILES['familyCard']['name'] : '';
        $photoFile = $_FILES['photo']['name'] ? $uploadDir . time() . '_' . $_FILES['photo']['name'] : '';

        // New document uploads
        $elementaryCertificateFile = $_FILES['elementaryCertificate']['name'] ? $uploadDir . time() . '_' . $_FILES['elementaryCertificate']['name'] : '';
        $mdaCertificateFile = $_FILES['mdaCertificate']['name'] ? $uploadDir . time() . '_' . $_FILES['mdaCertificate']['name'] : '';
        $skhunFile = $_FILES['skhun']['name'] ? $uploadDir . time() . '_' . $_FILES['skhun']['name'] : '';
        $nisnFile = $_FILES['nisnFile']['name'] ? $uploadDir . time() . '_' . $_FILES['nisnFile']['name'] : '';
        $parentIdCardFile = $_FILES['parentIdCard']['name'] ? $uploadDir . time() . '_' . $_FILES['parentIdCard']['name'] : '';
        $socialCardFile = $_FILES['socialCard']['name'] ? $uploadDir . time() . '_' . $_FILES['socialCard']['name'] : '';
        $graduationLetterFile = $_FILES['graduationLetter']['name'] ? $uploadDir . time() . '_' . $_FILES['graduationLetter']['name'] : '';

        // Move uploaded files
        if ($_FILES['certificate']['name']) move_uploaded_file($_FILES['certificate']['tmp_name'], $certificateFile);
        if ($_FILES['birthCertificate']['name']) move_uploaded_file($_FILES['birthCertificate']['tmp_name'], $birthCertificateFile);
        if ($_FILES['familyCard']['name']) move_uploaded_file($_FILES['familyCard']['tmp_name'], $familyCardFile);
        if ($_FILES['photo']['name']) move_uploaded_file($_FILES['photo']['tmp_name'], $photoFile);

        // Move new uploaded files
        if ($_FILES['elementaryCertificate']['name']) move_uploaded_file($_FILES['elementaryCertificate']['tmp_name'], $elementaryCertificateFile);
        if ($_FILES['mdaCertificate']['name']) move_uploaded_file($_FILES['mdaCertificate']['tmp_name'], $mdaCertificateFile);
        if ($_FILES['skhun']['name']) move_uploaded_file($_FILES['skhun']['tmp_name'], $skhunFile);
        if ($_FILES['nisnFile']['name']) move_uploaded_file($_FILES['nisnFile']['tmp_name'], $nisnFile);
        if ($_FILES['parentIdCard']['name']) move_uploaded_file($_FILES['parentIdCard']['tmp_name'], $parentIdCardFile);
        if ($_FILES['socialCard']['name']) move_uploaded_file($_FILES['socialCard']['tmp_name'], $socialCardFile);
        if ($_FILES['graduationLetter']['name']) move_uploaded_file($_FILES['graduationLetter']['tmp_name'], $graduationLetterFile);

        // Insert data into database
        $stmt = $pdo->prepare("INSERT INTO applicants (
      user_id, registration_number, full_name, nisn, birth_place, birth_date, 
      gender, religion, address, phone, email, father_name, father_job, 
      mother_name, mother_job, parent_phone, school_name, school_address, 
      graduation_year, certificate_file, birth_certificate_file, family_card_file, photo_file,
      elementary_certificate_file, mda_certificate_file, skhun_file, nisn_file,
      parent_id_card_file, social_card_file, graduation_letter_file
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $result = $stmt->execute([
            $_SESSION['user_id'],
            $registrationNumber,
            $_POST['fullName'],
            $_POST['nisn'],
            $_POST['birthPlace'],
            $_POST['birthDate'],
            $_POST['gender'],
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
            // Save registration number in session for display
            $_SESSION['registration_number'] = $registrationNumber;
            $formSubmitted = true;

            // Use PRG pattern to prevent form resubmission
            header("Location: dashboard.php?tab=complete&submitted=1");
            exit();
        } else {
            $error = 'Terjadi kesalahan saat menyimpan data. Silahkan coba lagi.';
        }
    }
}

// Get user email from database
$userEmail = '';
if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $userEmail = $user['email'];
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// After form submission, refresh application data
if (isset($_GET['submitted']) && $_GET['submitted'] == '1') {
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $application = $stmt->fetch();

    if ($application) {
        $hasApplied = true;
        $applicationStatus = $application['status'];
        $registrationNumber = $application['registration_number'];
        $adminNotes = $application['admin_notes'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PPDB Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#3d84e1', // Normal
                            hover: '#377ccb', // Normal:hover
                            active: '#316ab4', // Normal:active
                            foreground: '#ffffff',
                        },
                        light: {
                            DEFAULT: '#ecf3fc', // Light
                            hover: '#e2edfb', // Light:hover
                            active: '#c3d9f6', // Light:active
                        },
                        dark: {
                            DEFAULT: '#2e63a9', // Dark
                            hover: '#254f87', // Dark:hover
                            active: '#1b3b65', // Dark:active
                            foreground: '#ffffff',
                        },
                        darker: '#152e4f', // Darker
                        muted: {
                            DEFAULT: '#ecf3fc', // Using Light as muted
                            foreground: '#2e63a9', // Using Dark as muted foreground
                        },
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles to ensure color consistency */
        body {
            color: #2e63a9;
            /* dark */
        }

        .text-default {
            color: #2e63a9;
            /* dark */
        }

        .hover-transition {
            transition: all 0.2s ease-in-out;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #3d84e1 !important;
            /* primary */
            box-shadow: 0 0 0 1px #3d84e1 !important;
            /* primary */
        }

        .tab-active {
            border-bottom: 2px solid #3d84e1;
            /* primary */
            color: #3d84e1;
            /* primary */
        }

        .tab-inactive {
            color: #2e63a9;
            /* dark */
        }

        .tab-inactive:hover {
            color: #254f87;
            /* dark:hover */
        }

        .status-pending {
            background-color: #fff4db;
            color: #b27b00;
        }

        .status-verified {
            background-color: #e0f5ff;
            color: #0077b6;
        }

        .status-accepted {
            background-color: #e3f8e9;
            color: #15803d;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .admin-notes {
            background-color: #f8f9fa;
            border-left: 3px solid #3d84e1;
            padding: 10px;
            margin-top: 10px;
        }

        .contact-card {
            transition: transform 0.2s ease-in-out;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-white text-default">
    <header class="sticky top-0 z-10 border-b bg-white shadow-sm">
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
            <h1 class="text-xl font-bold text-dark">PPDB Online</h1>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm font-medium md:inline-block text-dark">Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username']); ?></span>
                <a href="?logout=1" class="text-dark hover:text-dark-hover hover-transition">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sr-only">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto flex-1 px-4 py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-dark">Dashboard Pendaftaran</h2>
            <p class="text-muted-foreground">Silahkan ikuti langkah-langkah pendaftaran berikut</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-800">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="mb-6 border-b">
            <div class="flex flex-wrap -mb-px">
                <a href="?tab=info" class="inline-block p-4 <?php echo $activeTab === 'info' ? 'tab-active' : 'tab-inactive'; ?> hover-transition">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi PPDB
                </a>
                <a href="?tab=form" class="inline-block p-4 <?php echo $activeTab === 'form' ? 'tab-active' : 'tab-inactive'; ?> hover-transition <?php echo $hasApplied ? 'pointer-events-none opacity-50' : ''; ?>">
                    <i class="fas fa-file-alt mr-2"></i>
                    Formulir Pendaftaran
                </a>
                <a href="?tab=complete" class="inline-block p-4 <?php echo $activeTab === 'complete' ? 'tab-active' : 'tab-inactive'; ?> hover-transition <?php echo !$hasApplied && !$formSubmitted ? 'pointer-events-none opacity-50' : ''; ?>">
                    <i class="fas fa-check-circle mr-2"></i>
                    Status Pendaftaran
                </a>
                <a href="?tab=contact" class="inline-block p-4 <?php echo $activeTab === 'contact' ? 'tab-active' : 'tab-inactive'; ?> hover-transition">
                    <i class="fas fa-phone-alt mr-2"></i>
                    Hubungi Kami
                </a>
            </div>
        </div>

        <!-- Tab Content -->
        <?php if ($activeTab === 'info'): ?>
            <!-- Information Tab -->
            <div class="rounded-lg border bg-white p-6 shadow-md">
                <h3 class="mb-4 text-xl font-bold text-dark">Informasi Penerimaan Peserta Didik Baru</h3>
                <p class="mb-6 text-muted-foreground">
                    Informasi penting tentang proses penerimaan siswa baru
                </p>

                <div class="mb-6">
                    <h4 class="mb-2 text-lg font-medium text-dark">Persyaratan Pendaftaran</h4>
                    <ul class="ml-6 list-disc space-y-2 text-dark">
                        <li>Ijazah SD/MI</li>
                        <li>Ijazah MDA</li>
                        <li>SKHUN</li>
                        <li>NISN</li>
                        <li>Akta Kelahiran </li>
                        <li>Kartu Keluarga </li>
                        <li>KTP Wali </li>
                        <li>Kartu KIS/KIP/PKH </li>
                        <li>Pas foto berwarna ukuran 3x4</li>
                        <li>Surat Keterangan Lulus</li>
                    </ul>
                </div>

                <!-- <div class="mb-6">
                    <h4 class="mb-2 text-lg font-medium text-dark">Jalur Pendaftaran</h4>
                    <div class="space-y-2">
                        <p class="text-dark"><strong>1. Jalur Prestasi Akademik</strong></p>
                        <p class="text-sm text-muted-foreground">Untuk siswa dengan nilai rapor yang memenuhi syarat minimum</p>

                        <p class="text-dark"><strong>2. Jalur Prestasi Non-Akademik</strong></p>
                        <p class="text-sm text-muted-foreground">Untuk siswa dengan prestasi di bidang olahraga, seni, atau kompetisi lainnya</p>

                        <p class="text-dark"><strong>3. Jalur Reguler</strong></p>
                        <p class="text-sm text-muted-foreground">Untuk pendaftaran umum berdasarkan hasil tes masuk</p>
                    </div>
                </div> -->

                <div class="pt-4">
                    <a href="?tab=form" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active hover-transition shadow-sm <?php echo $hasApplied ? 'opacity-50 pointer-events-none' : ''; ?>">
                        <?php echo $hasApplied ? 'Anda Sudah Mendaftar' : 'Lanjut ke Formulir Pendaftaran'; ?>
                    </a>
                </div>
            </div>
        <?php elseif ($activeTab === 'form' && !$hasApplied): ?>
            <!-- Form Tab -->
            <div class="rounded-lg border bg-white p-6 shadow-md">
                <h3 class="mb-4 text-xl font-bold text-dark">Formulir Pendaftaran</h3>
                <p class="mb-6 text-muted-foreground">
                    Isi data diri Anda dengan lengkap dan benar
                </p>

                <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="space-y-6">
                    <!-- Data Pribadi -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-dark">Data Pribadi</h4>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="fullName" class="text-sm font-medium text-dark">
                                    Nama Lengkap
                                </label>
                                <input
                                    type="text"
                                    id="fullName"
                                    name="fullName"
                                    placeholder="Masukkan nama lengkap"
                                    value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="nisn" class="text-sm font-medium text-dark">
                                    NISN
                                </label>
                                <input
                                    type="text"
                                    id="nisn"
                                    name="nisn"
                                    placeholder="Masukkan NISN"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="birthPlace" class="text-sm font-medium text-dark">
                                    Tempat Lahir
                                </label>
                                <input
                                    type="text"
                                    id="birthPlace"
                                    name="birthPlace"
                                    placeholder="Masukkan tempat lahir"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="birthDate" class="text-sm font-medium text-dark">
                                    Tanggal Lahir
                                </label>
                                <input
                                    type="date"
                                    id="birthDate"
                                    name="birthDate"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="gender" class="text-sm font-medium text-dark">
                                    Jenis Kelamin
                                </label>
                                <select
                                    id="gender"
                                    name="gender"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="religion" class="text-sm font-medium text-dark">
                                    Agama
                                </label>
                                <select
                                    id="religion"
                                    name="religion"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                    <option value="">Pilih agama</option>
                                    <option value="islam">Islam</option>
                                    <option value="kristen">Kristen</option>
                                    <option value="katolik">Katolik</option>
                                    <option value="hindu">Hindu</option>
                                    <option value="buddha">Buddha</option>
                                    <option value="konghucu">Konghucu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat dan Kontak -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-dark">Alamat dan Kontak</h4>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2 md:col-span-2">
                                <label for="address" class="text-sm font-medium text-dark">
                                    Alamat Lengkap
                                </label>
                                <textarea
                                    id="address"
                                    name="address"
                                    placeholder="Masukkan alamat lengkap"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    rows="3"
                                    required></textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-medium text-dark">
                                    Nomor Telepon
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    placeholder="Masukkan nomor telepon"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-medium text-dark">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Masukkan email"
                                    value="<?php echo htmlspecialchars($userEmail); ?>"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Data Orang Tua/Wali -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-dark">Data Orang Tua/Wali</h4>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="fatherName" class="text-sm font-medium text-dark">
                                    Nama Ayah
                                </label>
                                <input
                                    type="text"
                                    id="fatherName"
                                    name="fatherName"
                                    placeholder="Masukkan nama ayah"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="fatherJob" class="text-sm font-medium text-dark">
                                    Pekerjaan Ayah
                                </label>
                                <input
                                    type="text"
                                    id="fatherJob"
                                    name="fatherJob"
                                    placeholder="Masukkan pekerjaan ayah"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="motherName" class="text-sm font-medium text-dark">
                                    Nama Ibu
                                </label>
                                <input
                                    type="text"
                                    id="motherName"
                                    name="motherName"
                                    placeholder="Masukkan nama ibu"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="motherJob" class="text-sm font-medium text-dark">
                                    Pekerjaan Ibu
                                </label>
                                <input
                                    type="text"
                                    id="motherJob"
                                    name="motherJob"
                                    placeholder="Masukkan pekerjaan ibu"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary
                                  class=" w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="parentPhone" class="text-sm font-medium text-dark">
                                    Nomor Telepon Orang Tua
                                </label>
                                <input
                                    type="tel"
                                    id="parentPhone"
                                    name="parentPhone"
                                    placeholder="Masukkan nomor telepon orang tua"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Data Sekolah Asal -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-dark">Data Sekolah Asal</h4>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="schoolName" class="text-sm font-medium text-dark">
                                    Nama Sekolah
                                </label>
                                <input
                                    type="text"
                                    id="schoolName"
                                    name="schoolName"
                                    placeholder="Masukkan nama sekolah asal"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="schoolAddress" class="text-sm font-medium text-dark">
                                    Alamat Sekolah
                                </label>
                                <input
                                    type="text"
                                    id="schoolAddress"
                                    name="schoolAddress"
                                    placeholder="Masukkan alamat sekolah asal"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div class="space-y-2">
                                <label for="graduationYear" class="text-sm font-medium text-dark">
                                    Tahun Lulus
                                </label>
                                <input
                                    type="text"
                                    id="graduationYear"
                                    name="graduationYear"
                                    placeholder="Masukkan tahun lulus"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Unggah Dokumen -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-dark">Unggah Dokumen</h4>

                        <div class="mb-4 rounded-md bg-blue-50 p-4 text-sm text-blue-800">
                            <p class="font-medium">Ketentuan Upload File:</p>
                            <ul class="mt-2 list-disc pl-5">
                                <li>Ukuran maksimal file: 2MB</li>
                                <li>Format file yang diizinkan: JPG, PNG, PDF</li>
                            </ul>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Dokumen Lama -->
                            <div class="space-y-2">
                                <label for="certificate" class="text-sm font-medium text-dark">
                                    Ijazah/Surat Keterangan Lulus <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="certificate"
                                    name="certificate"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="birthCertificate" class="text-sm font-medium text-dark">
                                    Akta Kelahiran <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="birthCertificate"
                                    name="birthCertificate"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="familyCard" class="text-sm font-medium text-dark">
                                    Kartu Keluarga <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="familyCard"
                                    name="familyCard"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="photo" class="text-sm font-medium text-dark">
                                    Pas Foto <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="photo"
                                    name="photo"
                                    accept=".jpg,.jpeg,.png"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG)</p>
                            </div>

                            <!-- Dokumen Baru -->
                            <div class="space-y-2">
                                <label for="elementaryCertificate" class="text-sm font-medium text-dark">
                                    Ijazah SD/MI <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="elementaryCertificate"
                                    name="elementaryCertificate"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="mdaCertificate" class="text-sm font-medium text-dark">
                                    Ijazah MDA <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="mdaCertificate"
                                    name="mdaCertificate"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="skhun" class="text-sm font-medium text-dark">
                                    SKHUN <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="skhun"
                                    name="skhun"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="nisnFile" class="text-sm font-medium text-dark">
                                    NISN <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="nisnFile"
                                    name="nisnFile"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="parentIdCard" class="text-sm font-medium text-dark">
                                    KTP Orang Tua/Wali <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="parentIdCard"
                                    name="parentIdCard"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    required>
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="socialCard" class="text-sm font-medium text-dark">
                                    Kartu KIS/KIP/PKH (Jika Ada)
                                </label>
                                <input
                                    type="file"
                                    id="socialCard"
                                    name="socialCard"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                            <div class="space-y-2">
                                <label for="graduationLetter" class="text-sm font-medium text-dark">
                                    Surat Keterangan Lulus (Jika Ada)
                                </label>
                                <input
                                    type="file"
                                    id="graduationLetter"
                                    name="graduationLetter"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="submit_form" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active hover-transition shadow-sm">
                            Submit Pendaftaran
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($activeTab === 'complete') : ?>
            <!-- Completion Tab -->
            <div id="bukti-pendaftaran" class="rounded-lg border bg-white p-6 shadow-md">
                <h3 class="mb-4 text-xl font-bold text-dark">Status Pendaftaran</h3>
                <p class="mb-6 text-muted-foreground">
                    Informasi status pendaftaran Anda
                </p>

                <?php if ($hasApplied || $formSubmitted): ?>
                    <?php
                    // Get application details if not already fetched
                    if (!isset($application) && isset($_SESSION['user_id'])) {
                        require_once 'config/database.php';
                        $stmt = $pdo->prepare("SELECT * FROM applicants WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
                        $stmt->execute([$_SESSION['user_id']]);
                        $application = $stmt->fetch();

                        if ($application) {
                            $applicationStatus = $application['status'];
                            $registrationNumber = $application['registration_number'];
                            $adminNotes = $application['admin_notes']; // Make sure to get admin notes
                        } else {
                            $registrationNumber = $_SESSION['registration_number'] ?? 'PPDB' . date('Y') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                        }
                    }

                    // Status text and class
                    $statusText = '';
                    $statusClass = '';
                    switch ($applicationStatus) {
                        case 'pending':
                            $statusText = 'Menunggu Verifikasi';
                            $statusClass = 'status-pending';
                            break;
                        case 'verified':
                            $statusText = 'Terverifikasi';
                            $statusClass = 'status-verified';
                            break;
                        case 'accepted':
                            $statusText = 'Diterima';
                            $statusClass = 'status-accepted';
                            break;
                        case 'rejected':
                            $statusText = 'Ditolak';
                            $statusClass = 'status-rejected';
                            break;
                        default:
                            $statusText = 'Menunggu Verifikasi';
                            $statusClass = 'status-pending';
                    }
                    ?>
                    <div class="space-y-4 text-center">
                        <div class="mx-auto my-6 flex h-20 w-20 items-center justify-center rounded-full bg-light">
                            <i class="fas fa-check-circle text-4xl text-primary"></i>
                        </div>

                        <h4 class="text-xl font-medium text-dark">Pendaftaran Berhasil!</h4>
                        <p class="text-muted-foreground">
                            Data pendaftaran Anda telah kami terima dan sedang dalam proses verifikasi.
                        </p>

                        <div class="mx-auto mt-4 max-w-md rounded-lg border bg-light p-4 text-left">
                            <div class="mb-2 grid grid-cols-2 gap-2">
                                <span class="text-sm font-medium text-dark">Nomor Pendaftaran:</span>
                                <span class="text-sm text-darker"><?php echo htmlspecialchars($registrationNumber); ?></span>
                            </div>
                            <div class="mb-2 grid grid-cols-2 gap-2">
                                <span class="text-sm font-medium text-dark">Nama:</span>
                                <span class="text-sm text-darker"><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username']); ?></span>
                            </div>
                            <div class="mb-2 grid grid-cols-2 gap-2">
                                <span class="text-sm font-medium text-dark">Tanggal Daftar:</span>
                                <span class="text-sm text-darker"><?php echo isset($application) ? date('d F Y', strtotime($application['created_at'])) : date('d F Y'); ?></span>
                            </div>
                            <div class="mb-2 grid grid-cols-2 gap-2">
                                <span class="text-sm font-medium text-dark">Status:</span>
                                <span class="rounded-full <?php echo $statusClass; ?> px-2 py-1 text-xs font-medium">
                                    <?php echo $statusText; ?>
                                </span>
                            </div>

                            <?php if (!empty($adminNotes)): ?>
                                <div class="mt-4">
                                    <span class="text-sm font-medium text-dark">Catatan Admin:</span>
                                    <div class="admin-notes mt-2 text-sm text-darker">
                                        <?php echo nl2br(htmlspecialchars($adminNotes)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-6 space-y-2">
                            <p class="text-sm text-muted-foreground">
                                Silahkan cetak bukti pendaftaran ini sebagai bukti bahwa Anda telah mendaftar.
                            </p>
                            <button
                                onclick="window.print()"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 hover-transition shadow-sm">
                                Cetak Bukti Pendaftaran
                            </button>
                            <button
                                onclick="downloadPDF()"
                                class="rounded-md bg-green-500 px-4 py-2 text-sm font-medium text-white hover:bg-green-600 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 hover-transition shadow-sm">
                                Download Bukti Pendaftaran
                            </button>
                        </div>

                        <div class="mt-8 rounded-lg border bg-light p-4 text-left">
                            <h4 class="mb-2 font-medium text-dark">Informasi Selanjutnya:</h4>
                            <ol class="ml-6 list-decimal space-y-2 text-sm text-dark">
                                <li>Pantau status pendaftaran Anda melalui dashboard ini atau pemberitahuan pada email terdaftar</li>
                                <li>Pengumuman hasil akan diumumkan pada tanggal 1 Juli 2025</li>
                                <li>Jika diterima, lakukan daftar ulang pada tanggal 11-15 Juli 2025</li>
                                <li>Untuk informasi lebih lanjut, hubungi panitia PPDB di (021) 1234567</li>
                            </ol>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted-foreground">
                        Anda belum mendaftar. Silahkan isi formulir pendaftaran terlebih dahulu.
                    </p>
                <?php endif; ?>
            </div>
        <?php elseif ($activeTab === 'contact'): ?>
            <!-- Contact Tab -->
            <div class="rounded-lg border bg-white p-6 shadow-md">
                <h3 class="mb-4 text-xl font-bold text-dark">Hubungi Kami</h3>
                <p class="mb-6 text-muted-foreground">
                    Jika Anda memiliki pertanyaan atau kendala terkait pendaftaran, silahkan hubungi kami melalui kontak berikut:
                </p>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Kontak Panitia PPDB -->
                    <div class="contact-card rounded-lg border bg-white p-5 shadow-sm hover:border-primary">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-light text-primary">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <h4 class="mb-2 font-medium text-dark">Panitia PPDB</h4>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-phone-alt text-primary"></i>
                                <span>0858-6054-7292 (Iman S Rohman)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-phone-alt text-primary"></i>
                                <span>0852-2151-7479 (Atep Rahman, S.Pd)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-clock text-primary"></i>
                                <span>08.00 - 16.00 WIB</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Kontak Sekretariat -->
                    <div class="contact-card rounded-lg border bg-white p-5 shadow-sm hover:border-primary">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-light text-primary">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                        <h4 class="mb-2 font-medium text-dark">Sekretariat Sekolah</h4>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-phone-alt text-primary"></i>
                                <span>0812-9857-0328 (Teti Hukmiati, S.Ag)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-phone-alt text-primary"></i>
                                <span>0813-1204-1422 (Yulli Yulisman, S.Pd)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-envelope text-primary"></i>
                                <span>mtsalishlahhits@gmail.com</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 rounded-lg border bg-light p-5">
                    <h4 class="mb-3 font-medium text-dark">Lokasi Sekolah</h4>
                    <div class="aspect-video w-full overflow-hidden rounded-lg">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.5984219938628!2d107.97290621067373!3d-7.50950519247182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6606bf6c7128ed%3A0x815a9f2955afde8a!2sMTs%20Al-Islah!5e0!3m2!1sid!2sid!4v1743428911876!5m2!1sid!2sid"
                            width="100%"
                            height="100%"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy">
                            </src=>
                    </div>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Jl. Pendidikan No. 123, Kecamatan Contoh, Kota Contoh, Provinsi Contoh 12345
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-dark py-6 text-dark-foreground">
        <div class="container mx-auto px-4">
            <div class="grid gap-6 md:grid-cols-3">
                <div>
                    <h3 class="mb-3 text-lg font-medium text-white">PPDB Online</h3>
                    <p class="text-sm text-gray-300">
                        Sistem Informasi Penerimaan Peserta Didik Baru untuk memudahkan proses pendaftaran siswa baru.
                    </p>
                </div>

                <div>
                    <h3 class="mb-3 text-lg font-medium text-white">Kontak Kami</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone-alt"></i>
                            <span>0812-9857-0328 (Teti Hukmiati, S.Ag)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone-alt"></i>
                            <span>0813-1204-1422 (Yulli Yulisman, S.Pd)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone-alt"></i>
                            <span>0852-2151-7479 (Atep Rahman, S.Pd)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone-alt"></i>
                            <span>0858-6054-7292 (Iman S Rohman)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-envelope"></i>
                            <span>mtsalishlahhits@gmail.com</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Raya Ciawi Bojonggambir No. 54, Desa Bojonggambir, Kec. Bojonggambir, Kab. Tasikmalaya</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-3 text-lg font-medium text-white">Tautan</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li><a href="?tab=info" class="hover:text-white hover:underline">Informasi PPDB</a></li>
                        <li><a href="?tab=contact" class="hover:text-white hover:underline">Hubungi Kami</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 border-t border-gray-700 pt-4 text-center text-sm text-gray-400">
                <p>© <?php echo date('Y'); ?> Sistem Informasi Penerimaan Peserta Didik Baru. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</body>

<script>
    function downloadPDF() {
        const element = document.getElementById('bukti-pendaftaran');
        const opt = {
            margin: 0.5,
            filename: 'bukti-pendaftaran.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        };

        html2pdf().set(opt).from(element).save();
    }
</script>

</html>