<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';

// Get users for dropdown
$stmt = $pdo->query("SELECT id, username, name, email FROM users");
$users = $stmt->fetchAll();

// Initialize error and success messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    // Process form data
    require_once 'proses-add-application.php';
}

$pageTitle = "Tambah Pendaftaran Baru - Admin PPDB";
include 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Pendaftaran Baru</h2>
            <p class="text-gray-600">Form untuk menambahkan pendaftaran baru dengan status diterima</p>
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
        <form method="POST" action="add-application.php" enctype="multipart/form-data" class="space-y-6">
            <!-- Pilih User -->
            <div class="space-y-4">
                <h4 class="font-medium text-dark">Pilih User</h4>
                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Ubah bagian "Pilih User" agar tidak required -->
                    <div class="space-y-2">
                        <label for="user_id" class="text-sm font-medium text-dark">
                            User Terdaftar (Opsional)
                        </label>
                        <select 
                            id="user_id" 
                            name="user_id" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            <option value="">-- Tidak memilih user --</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name'] ? $user['name'] . ' (' . $user['username'] . ')' : $user['username']); ?> - <?php echo htmlspecialchars($user['email']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500">Jika tidak dipilih, sistem akan membuat user baru secara otomatis.</p>
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
                            value="<?php echo 'PPDB' . date('Y') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT); ?>"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required
                        >
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required
                        >
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
                            required
                        >
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
                            required
                        >
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
                            required
                        >
                    </div>
                    <div class="space-y-2">
                        <label for="gender" class="text-sm font-medium text-dark">
                            Jenis Kelamin
                        </label>
                        <select 
                            id="gender" 
                            name="gender" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required
                        >
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
                            required
                        >
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
                            required
                        ></textarea>
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
                            required
                        >
                    </div>
                    <!-- Ubah field email agar tidak required -->
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-dark">
                            Email (Opsional)
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Masukkan email" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
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
                            required
                        >
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
                            required
                        >
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
                            required
                        >
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required
                        >
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
                            required
                        >
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
                            required
                        >
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
                            required
                        >
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
                            required
                        >
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
                        <li>Semua dokumen bersifat opsional untuk admin</li>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
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
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="text-xs text-gray-500">Maks. 2MB (JPG, PNG, PDF)</p>
                    </div>
                </div>
            </div>
            
            <div>
                <button type="submit" name="submit_form" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active hover-transition shadow-sm">
                    Simpan Pendaftaran
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
