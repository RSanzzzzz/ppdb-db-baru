<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php?error=no_id");
    exit();
}

$applicationId = $_GET['id'];

require_once '../config/database.php';

// Get application data - Updated for new schema
$stmt = $pdo->prepare("SELECT a.*, u.id as user_id, u.nama_pengguna, u.nama as user_name, u.email as user_email 
                      FROM applicants a 
                      LEFT JOIN users u ON a.user_id = u.id 
                      WHERE a.id = ?");
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php?error=not_found");
    exit();
}

// Get users for dropdown - Updated for new schema
$stmt = $pdo->query("SELECT id, nama_pengguna, nama, email FROM users");
$users = $stmt->fetchAll();

// Initialize error and success messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    // Process form data
    require_once 'proses-edit-application.php';
}

$pageTitle = "Edit Pendaftaran - Admin PPDB";
include 'includes/header.php';

// Function to map gender from Indonesian to English for form display
function mapGenderToEnglish($gender) {
    if ($gender === 'laki-laki') return 'male';
    if ($gender === 'perempuan') return 'female';
    return $gender;
}
?>

<main class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Pendaftaran</h2>
            <p class="text-gray-600">Form untuk mengedit data pendaftaran</p>
        </div>
        <a href="dashboard.php" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-800">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="rounded-lg border bg-white p-6 shadow-md">
        <form method="POST" action="edit-application.php?id=<?php echo $applicationId; ?>" enctype="multipart/form-data" class="space-y-6">
            <!-- Pilih User -->
            <div class="space-y-4">
                <h4 class="font-medium text-dark">Informasi User</h4>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="user_id" class="text-sm font-medium text-dark">
                            User Terdaftar
                        </label>
                        <select
                            id="user_id"
                            name="user_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">-- Tidak memilih user --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $application['user_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nama'] ? $user['nama'] . ' (' . $user['nama_pengguna'] . ')' : $user['nama_pengguna']); ?> - <?php echo htmlspecialchars($user['email']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500">User saat ini: <?php echo htmlspecialchars($application['user_name'] ?? $application['nama_pengguna'] ?? 'Tidak ada'); ?></p>
                    </div>

                    <div class="space-y-2">
                        <label for="registration_number" class="text-sm font-medium text-dark">
                            Nomor Pendaftaran
                        </label>
                        <input
                            type="text"
                            id="registration_number"
                            name="registration_number"
                            placeholder="Masukkan nomor pendaftaran"
                            value="<?php echo htmlspecialchars($application['nomor_pendaftaran']); ?>"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required>
                    </div>
                </div>
            </div>

            <!-- Status Pendaftaran -->
            <div class="space-y-4">
                <h4 class="font-medium text-dark">Status Pendaftaran</h4>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="status" class="text-sm font-medium text-dark">
                            Status
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required>
                            <option value="menunggu" <?php echo ($application['status'] == 'menunggu') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                            <option value="terverifikasi" <?php echo ($application['status'] == 'terverifikasi') ? 'selected' : ''; ?>>Terverifikasi</option>
                            <option value="diterima" <?php echo ($application['status'] == 'diterima') ? 'selected' : ''; ?>>Diterima</option>
                            <option value="ditolak" <?php echo ($application['status'] == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="admin_notes" class="text-sm font-medium text-dark">
                            Catatan Admin
                        </label>
                        <textarea
                            id="admin_notes"
                            name="admin_notes"
                            placeholder="Masukkan catatan admin"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            rows="3"><?php echo htmlspecialchars($application['catatan_admin'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

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
                            value="<?php echo htmlspecialchars($application['nama_lengkap']); ?>"
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
                            value="<?php echo htmlspecialchars($application['nisn']); ?>"
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
                            value="<?php echo htmlspecialchars($application['tempat_lahir']); ?>"
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
                            value="<?php echo htmlspecialchars($application['tanggal_lahir']); ?>"
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
                            <option value="male" <?php echo (mapGenderToEnglish($application['jenis_kelamin']) == 'male') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="female" <?php echo (mapGenderToEnglish($application['jenis_kelamin']) == 'female') ? 'selected' : ''; ?>>Perempuan</option>
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
                            <option value="islam" <?php echo ($application['agama'] == 'islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="kristen" <?php echo ($application['agama'] == 'kristen') ? 'selected' : ''; ?>>Kristen</option>
                            <option value="katolik" <?php echo ($application['agama'] == 'katolik') ? 'selected' : ''; ?>>Katolik</option>
                            <option value="hindu" <?php echo ($application['agama'] == 'hindu') ? 'selected' : ''; ?>>Hindu</option>
                            <option value="buddha" <?php echo ($application['agama'] == 'buddha') ? 'selected' : ''; ?>>Buddha</option>
                            <option value="konghucu" <?php echo ($application['agama'] == 'konghucu') ? 'selected' : ''; ?>>Konghucu</option>
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
                            required><?php echo htmlspecialchars($application['alamat']); ?></textarea>
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
                            value="<?php echo htmlspecialchars($application['telepon']); ?>"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required>
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-dark">
                            Email (Opsional)
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Masukkan email"
                            value="<?php echo htmlspecialchars($application['email']); ?>"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
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
                            value="<?php echo htmlspecialchars($application['nama_ayah']); ?>"
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
                            value="<?php echo htmlspecialchars($application['pekerjaan_ayah']); ?>"
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
                            value="<?php echo htmlspecialchars($application['nama_ibu']); ?>"
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
                            value="<?php echo htmlspecialchars($application['pekerjaan_ibu']); ?>"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
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
                            value="<?php echo htmlspecialchars($application['telepon_orangtua']); ?>"
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
                            value="<?php echo htmlspecialchars($application['nama_sekolah']); ?>"
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
                            value="<?php echo htmlspecialchars($application['alamat_sekolah']); ?>"
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
                            value="<?php echo htmlspecialchars($application['tahun_lulus']); ?>"
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
                        <li>Kosongkan field jika tidak ingin mengubah file yang sudah ada</li>
                    </ul>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Dokumen Lama -->
                    <div class="space-y-2">
                        <label for="certificate" class="text-sm font-medium text-dark">
                            Ijazah/Surat Keterangan Lulus
                        </label>
                        <input
                            type="file"
                            id="certificate"
                            name="certificate"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_ijazah'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_ijazah']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="birthCertificate" class="text-sm font-medium text-dark">
                            Akta Kelahiran
                        </label>
                        <input
                            type="file"
                            id="birthCertificate"
                            name="birthCertificate"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_akta_kelahiran'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_akta_kelahiran']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="familyCard" class="text-sm font-medium text-dark">
                            Kartu Keluarga
                        </label>
                        <input
                            type="file"
                            id="familyCard"
                            name="familyCard"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_kartu_keluarga'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_kartu_keluarga']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="photo" class="text-sm font-medium text-dark">
                            Pas Foto
                        </label>
                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept=".jpg,.jpeg,.png"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG)</p>
                        <?php if (!empty($application['file_foto'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_foto']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Dokumen Baru -->
                    <div class="space-y-2">
                        <label for="elementaryCertificate" class="text-sm font-medium text-dark">
                            Ijazah SD/MI
                        </label>
                        <input
                            type="file"
                            id="elementaryCertificate"
                            name="elementaryCertificate"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_ijazah_sd'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_ijazah_sd']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="mdaCertificate" class="text-sm font-medium text-dark">
                            Ijazah MDA
                        </label>
                        <input
                            type="file"
                            id="mdaCertificate"
                            name="mdaCertificate"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_ijazah_mda'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_ijazah_mda']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="skhun" class="text-sm font-medium text-dark">
                            SKHUN
                        </label>
                        <input
                            type="file"
                            id="skhun"
                            name="skhun"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_skhun'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_skhun']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="nisnFile" class="text-sm font-medium text-dark">
                            NISN
                        </label>
                        <input
                            type="file"
                            id="nisnFile"
                            name="nisnFile"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_nisn'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_nisn']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="parentIdCard" class="text-sm font-medium text-dark">
                            KTP Orang Tua/Wali
                        </label>
                        <input
                            type="file"
                            id="parentIdCard"
                            name="parentIdCard"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                        <?php if (!empty($application['file_ktp_orangtua'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_ktp_orangtua']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
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
                        <?php if (!empty($application['file_kartu_sosial'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_kartu_sosial']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
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
                        <?php if (!empty($application['file_surat_lulus'])): ?>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-green-600">File saat ini:</span>
                                <a href="../<?php echo htmlspecialchars($application['file_surat_lulus']); ?>" target="_blank" class="text-xs text-primary hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" name="submit_form" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active hover-transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>