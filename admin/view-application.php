<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$applicationId = $_GET['id'];

require_once '../config/database.php';

// Get application details - Updated for new schema
$stmt = $pdo->prepare("SELECT a.*, u.email, u.nama as user_name FROM applicants a 
                      LEFT JOIN users u ON a.user_id = u.id 
                      WHERE a.id = ?");
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php");
    exit();
}

// Status options for dropdown - Updated for new schema
$statusOptions = [
    'menunggu' => 'Menunggu Verifikasi',
    'terverifikasi' => 'Terverifikasi',
    'diterima' => 'Diterima',
    'ditolak' => 'Ditolak'
];

// Check if status was updated
$statusUpdated = isset($_GET['status_updated']) && $_GET['status_updated'] == 1;
$emailStatus = isset($_SESSION['email_status']) ? $_SESSION['email_status'] : '';

// Clear email status from session after displaying
if (isset($_SESSION['email_status'])) {
    unset($_SESSION['email_status']);
}

// Check if email logs exist
$emailLogFile = __DIR__ . '/../logs/email.log';
$emailLogExists = file_exists($emailLogFile);
$emailLogs = '';
if ($emailLogExists) {
    $emailLogs = file_get_contents($emailLogFile);
    $emailLogs = array_slice(explode("\n", $emailLogs), -10); // Get last 10 lines
    $emailLogs = implode("\n", $emailLogs);
}

// Function to map gender value
function mapGender($gender) {
    return $gender === 'laki-laki' ? 'Laki-laki' : 'Perempuan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftaran - Admin PPDB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles */
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
        .log-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.75rem;
            font-family: monospace;
            font-size: 0.875rem;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Pendaftaran</h2>
                <p class="text-gray-600">Nomor Pendaftaran: <?php echo htmlspecialchars($application['nomor_pendaftaran']); ?></p>
            </div>
            <a href="dashboard.php" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
        
        <?php if ($statusUpdated): ?>
        <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">
            Status pendaftaran berhasil diperbarui.
            <?php if ($emailStatus === 'success'): ?>
                <span class="ml-2 font-medium">Email notifikasi telah dikirim ke peserta.</span>
            <?php elseif ($emailStatus === 'failed'): ?>
                <span class="ml-2 font-medium text-yellow-700">Email notifikasi gagal dikirim ke peserta. Silakan periksa log email.</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Status Update Form -->
        <div class="mb-8 rounded-lg border bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-medium text-gray-800">Update Status Pendaftaran</h3>
            
            <form action="update-status.php?id=<?php echo $applicationId; ?>&return=view" method="post" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <?php foreach ($statusOptions as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $application['status'] === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="admin_notes" class="mb-1 block text-sm font-medium text-gray-700">Catatan Admin</label>
                        <textarea id="admin_notes" name="admin_notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"><?php echo htmlspecialchars($application['catatan_admin'] ?? ''); ?></textarea>
                        <p class="mt-1 text-xs text-red-500">* Catatan ini akan ditampilkan kepada peserta dan dikirim melalui email.</p>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Applicant Information -->
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-medium text-gray-800">Informasi Pendaftar</h3>
            
            <div class="mb-6 grid gap-6 md:grid-cols-2">
                <div>
                    <h4 class="mb-2 font-medium text-gray-700">Data Pribadi</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Nama Lengkap</td>
                            <td><?php echo htmlspecialchars($application['nama_lengkap']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Email</td>
                            <td><?php echo htmlspecialchars($application['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">NISN</td>
                            <td><?php echo htmlspecialchars($application['nisn']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Tempat, Tanggal Lahir</td>
                            <td><?php echo htmlspecialchars($application['tempat_lahir']) . ', ' . date('d F Y', strtotime($application['tanggal_lahir'])); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Jenis Kelamin</td>
                            <td><?php echo mapGender($application['jenis_kelamin']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Agama</td>
                            <td><?php echo htmlspecialchars(ucfirst($application['agama'])); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Alamat</td>
                            <td><?php echo htmlspecialchars($application['alamat']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Telepon</td>
                            <td><?php echo htmlspecialchars($application['telepon']); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div>
                    <h4 class="mb-2 font-medium text-gray-700">Data Orang Tua</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Nama Ayah</td>
                            <td><?php echo htmlspecialchars($application['nama_ayah']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Pekerjaan Ayah</td>
                            <td><?php echo htmlspecialchars($application['pekerjaan_ayah']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Nama Ibu</td>
                            <td><?php echo htmlspecialchars($application['nama_ibu']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Pekerjaan Ibu</td>
                            <td><?php echo htmlspecialchars($application['pekerjaan_ibu']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Telepon Orang Tua</td>
                            <td><?php echo htmlspecialchars($application['telepon_orangtua']); ?></td>
                        </tr>
                    </table>
                    
                    <h4 class="mb-2 mt-4 font-medium text-gray-700">Data Sekolah Asal</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Nama Sekolah</td>
                            <td><?php echo htmlspecialchars($application['nama_sekolah']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Alamat Sekolah</td>
                            <td><?php echo htmlspecialchars($application['alamat_sekolah']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium text-gray-600">Tahun Lulus</td>
                            <td><?php echo htmlspecialchars($application['tahun_lulus']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h4 class="mb-2 font-medium text-gray-700">Dokumen</h4>
            <div class="grid gap-4 md:grid-cols-3">
                <?php
                // Updated for new schema
                $documents = [
                    'Ijazah/Surat Keterangan Lulus' => $application['file_ijazah'],
                    'Akta Kelahiran' => $application['file_akta_kelahiran'],
                    'Kartu Keluarga' => $application['file_kartu_keluarga'],
                    'Pas Foto' => $application['file_foto'],
                    'Ijazah SD/MI' => $application['file_ijazah_sd'],
                    'Ijazah MDA' => $application['file_ijazah_mda'],
                    'SKHUN' => $application['file_skhun'],
                    'NISN' => $application['file_nisn'],
                    'KTP Wali' => $application['file_ktp_orangtua'],
                    'Kartu KIS/KIP/PKH' => $application['file_kartu_sosial'],
                    'Surat Keterangan Lulus' => $application['file_surat_lulus']
                ];
                
                foreach ($documents as $label => $file):
                    if (!empty($file)):
                ?>
                <div class="rounded-md border p-3">
                    <p class="mb-2 text-sm font-medium text-gray-700"><?php echo $label; ?></p>
                    <a href="../<?php echo htmlspecialchars($file); ?>" target="_blank" class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">
                        <i class="fas fa-file-alt mr-1"></i> Lihat Dokumen
                    </a>
                </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>