<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';

// Pesan notifikasi
$notification = '';
$notificationType = '';

if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $notification = 'Data pendaftaran berhasil dihapus.';
    $notificationType = 'success';
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_id':
            $notification = 'ID pendaftaran tidak ditemukan.';
            break;
        case 'not_found':
            $notification = 'Data pendaftaran tidak ditemukan.';
            break;
        case 'delete_failed':
            $notification = 'Gagal menghapus data pendaftaran.';
            break;
        default:
            $notification = 'Terjadi kesalahan.';
            break;
    }
    $notificationType = 'error';
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filters
$query = "SELECT a.*, u.username, u.name as user_name FROM applicants a 
         JOIN users u ON a.user_id = u.id 
         WHERE 1=1";

$params = [];

if (!empty($status)) {
    $query .= " AND a.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $query .= " AND (a.full_name LIKE ? OR a.registration_number LIKE ? OR a.nisn LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY a.created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get counts for dashboard
$stmtTotal = $pdo->query("SELECT COUNT(*) FROM applicants");
$totalApplications = $stmtTotal->fetchColumn();

$stmtPending = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status = 'pending'");
$pendingApplications = $stmtPending->fetchColumn();

$stmtVerified = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status = 'verified'");
$verifiedApplications = $stmtVerified->fetchColumn();

$stmtAccepted = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status = 'accepted'");
$acceptedApplications = $stmtAccepted->fetchColumn();

$stmtRejected = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status = 'rejected'");
$rejectedApplications = $stmtRejected->fetchColumn();

function tanggal_indo($tanggal) {
    $bulan_indo = array(
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    );
    
    $d = date('d', strtotime($tanggal));
    $m = $bulan_indo[date('n', strtotime($tanggal))];
    $y = date('Y', strtotime($tanggal));
    
    return $d . ' ' . $m . ' ' . $y;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PPDB Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
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

        /* Custom SweetAlert Styles */
        .swal2-popup {
            border-radius: 1rem;
            font-family: 'Inter', sans-serif;
        }

        .swal2-title {
            color: #2e63a9;
        }

        .swal2-confirm {
            background-color: #3d84e1 !important;
        }

        .swal2-confirm:hover {
            background-color: #377ccb !important;
        }

        .swal2-cancel {
            background-color: #64748b !important;
        }

        .swal2-cancel:hover {
            background-color: #475569 !important;
        }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-gray-50 text-default">
    <header class="sticky top-0 z-10 border-b bg-white shadow-sm">
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
            <h1 class="text-xl font-bold text-dark">PPDB Online - Admin Panel</h1>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm font-medium md:inline-block text-dark">
                    Admin: <?php echo htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['username']); ?>
                </span>
                <a href="?logout=1" class="text-dark hover:text-dark-hover hover-transition" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sr-only">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto flex-1 px-4 py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-dark">Dashboard Admin</h2>
            <p class="text-muted-foreground">Kelola dan verifikasi pendaftaran peserta didik baru</p>
        </div>

        <!-- Stats Cards -->
        <div class="mb-8 grid gap-4 md:grid-cols-5">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Pendaftar</h3>
                    <span class="text-2xl font-bold text-dark"><?php echo $totalApplications; ?></span>
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-muted-foreground">Menunggu Verifikasi</h3>
                    <span class="text-2xl font-bold text-yellow-600"><?php echo $pendingApplications; ?></span>
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-muted-foreground">Terverifikasi</h3>
                    <span class="text-2xl font-bold text-blue-600"><?php echo $verifiedApplications; ?></span>
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-muted-foreground">Diterima</h3>
                    <span class="text-2xl font-bold text-green-600"><?php echo $acceptedApplications; ?></span>
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-muted-foreground">Ditolak</h3>
                    <span class="text-2xl font-bold text-red-600"><?php echo $rejectedApplications; ?></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-6">
            <a href="add-application.php" class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active hover-transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Tambah Pendaftaran Baru
            </a>
        </div>

        <!-- Filters and Search -->
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <a href="dashboard.php" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-dark shadow-sm hover:bg-light hover-transition <?php echo empty($status) ? 'bg-light' : ''; ?>">
                    Semua
                </a>
                <a href="?status=pending" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-dark shadow-sm hover:bg-light hover-transition <?php echo $status === 'pending' ? 'bg-light' : ''; ?>">
                    Menunggu Verifikasi
                </a>
                <a href="?status=verified" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-dark shadow-sm hover:bg-light hover-transition <?php echo $status === 'verified' ? 'bg-light' : ''; ?>">
                    Terverifikasi
                </a>
                <a href="?status=accepted" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-dark shadow-sm hover:bg-light hover-transition <?php echo $status === 'accepted' ? 'bg-light' : ''; ?>">
                    Diterima
                </a>
                <a href="?status=rejected" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-dark shadow-sm hover:bg-light hover-transition <?php echo $status === 'rejected' ? 'bg-light' : ''; ?>">
                    Ditolak
                </a>
            </div>

            <form action="dashboard.php" method="GET" class="flex w-full max-w-md gap-2 md:w-auto">
                <?php if (!empty($status)): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <?php endif; ?>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama, nomor pendaftaran, atau NISN"
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="w-full flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 hover-transition">
                    <i class="fas fa-search"></i>
                    <span class="sr-only">Cari</span>
                </button>
            </form>
        </div>

        <!-- Applications Table -->
        <div class="overflow-hidden rounded-lg border bg-white shadow">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead class="border-b bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-sm font-medium text-dark">No. Pendaftaran</th>
                            <th class="px-4 py-3 text-sm font-medium text-dark">Nama Lengkap</th>
                            <th class="px-4 py-3 text-sm font-medium text-dark">NISN</th>
                            <th class="px-4 py-3 text-sm font-medium text-dark">Tanggal Daftar</th>
                            <th class="px-4 py-3 text-sm font-medium text-dark">Status</th>
                            <th class="px-4 py-3 text-sm font-medium text-dark">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (count($applications) > 0): ?>
                            <?php foreach ($applications as $app): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($app['registration_number']); ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($app['full_name']); ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($app['nisn']); ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo tanggal_indo($app['created_at']); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php
                                        $statusClass = 'status-' . $app['status'];
                                        $statusText = '';
                                        switch ($app['status']) {
                                            case 'pending':
                                                $statusText = 'Menunggu Verifikasi';
                                                break;
                                            case 'verified':
                                                $statusText = 'Terverifikasi';
                                                break;
                                            case 'accepted':
                                                $statusText = 'Diterima';
                                                break;
                                            case 'rejected':
                                                $statusText = 'Ditolak';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?> rounded-full px-2 py-1 text-xs font-medium">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            <a href="view-application.php?id=<?php echo $app['id']; ?>" class="text-primary hover:text-primary-hover" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-application.php?id=<?php echo $app['id']; ?>" class="text-blue-600 hover:text-blue-800 ml-2" title="Edit Data">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($app['status'] === 'pending'): ?>
                                                <a href="update-status.php?id=<?php echo $app['id']; ?>&status=verified" class="text-blue-600 hover:text-blue-800" title="Verifikasi">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($app['status'] === 'verified'): ?>
                                                <a href="update-status.php?id=<?php echo $app['id']; ?>&status=accepted" class="text-green-600 hover:text-green-800" title="Terima">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                                <a href="update-status.php?id=<?php echo $app['id']; ?>&status=rejected" class="text-red-600 hover:text-red-800" title="Tolak">
                                                    <i class="fas fa-times-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <!-- Tombol hapus dengan SweetAlert -->
                                            <button
                                                type="button"
                                                class="delete-btn text-red-600 hover:text-red-800 ml-2"
                                                title="Hapus Data"
                                                data-id="<?php echo $app['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($app['full_name']); ?>"
                                                data-reg="<?php echo htmlspecialchars($app['registration_number']); ?>"
                                                data-status="<?php echo $statusText; ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Tidak ada data pendaftaran yang ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-dark py-6 text-dark-foreground">
        <div class="container mx-auto px-4 text-center text-sm">
            <p>© <?php echo date('Y'); ?> Sistem Informasi Penerimaan Peserta Didik Baru. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Script untuk SweetAlert -->
    <script>
        // Tampilkan notifikasi jika ada
        <?php if (!empty($notification)): ?>
            Swal.fire({
                icon: '<?php echo $notificationType === 'success' ? 'success' : 'error'; ?>',
                title: '<?php echo $notificationType === 'success' ? 'Berhasil!' : 'Gagal!'; ?>',
                text: '<?php echo addslashes($notification); ?>',
                confirmButtonColor: '#3d84e1',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>

        // Event listener untuk tombol hapus
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const regNumber = this.getAttribute('data-reg');
                const status = this.getAttribute('data-status');

                Swal.fire({
                    title: 'Konfirmasi Hapus Data',
                    html: `
                       <div class="text-left mb-4">
                           <p class="mb-2">Anda akan menghapus data pendaftaran berikut:</p>
                           <div class="bg-gray-50 p-3 rounded-lg">
                               <p><span class="font-semibold">Nama:</span> ${name}</p>
                               <p><span class="font-semibold">No. Pendaftaran:</span> ${regNumber}</p>
                               <p><span class="font-semibold">Status:</span> ${status}</p>
                           </div>
                       </div>
                       <p class="text-red-600 font-semibold">Data yang dihapus tidak dapat dikembalikan!</p>
                   `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Data',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    customClass: {
                        popup: 'swal-wide',
                        title: 'text-red-600',
                        confirmButton: 'bg-red-600 hover:bg-red-700'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loading
                        Swal.fire({
                            title: 'Menghapus Data...',
                            html: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Redirect ke halaman delete dengan parameter confirm=yes
                        window.location.href = `delete-application.php?id=${id}&confirm=yes`;
                    }
                });
            });
        });
    </script>
</body>

</html>