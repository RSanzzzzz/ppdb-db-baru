<?php
session_start();
// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#3d84e1', // Normal
                            hover: '#377ccb',   // Normal:hover
                            active: '#316ab4',  // Normal:active
                            foreground: '#ffffff',
                        },
                        light: {
                            DEFAULT: '#ecf3fc', // Light
                            hover: '#e2edfb',   // Light:hover
                            active: '#c3d9f6',  // Light:active
                        },
                        dark: {
                            DEFAULT: '#2e63a9', // Dark
                            hover: '#254f87',   // Dark:hover
                            active: '#1b3b65',  // Dark:active
                            foreground: '#ffffff',
                        },
                        darker: '#152e4f',      // Darker
                        muted: {
                            DEFAULT: '#ecf3fc', // Using Light as muted
                            foreground: '#2e63a9', // Using Dark as muted foreground
                        },
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom styles to ensure color consistency */
        body {
            color: #2e63a9; /* dark */
        }
        .text-default {
            color: #2e63a9; /* dark */
        }
        .hover-transition {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-white text-default">
    <header class="bg-primary px-4 py-3 text-primary-foreground shadow-md">
        <div class="container mx-auto flex items-center justify-between">
            <h1 class="text-xl font-bold">PPDB Online</h1>
            <a href="login.php" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-primary shadow-sm hover:bg-light hover-transition">
                Login
            </a>
        </div>
    </header>
    
    <main class="flex-1">
        <section class="bg-light py-12 md:py-20">
            <div class="container mx-auto px-4 text-center">
                <h2 class="mb-4 text-3xl font-bold md:text-4xl text-dark">Penerimaan Peserta Didik Baru</h2>
                <p class="mx-auto mb-8 max-w-2xl text-muted-foreground">
                    Selamat datang di sistem informasi penerimaan peserta didik baru. Silahkan login untuk melanjutkan proses pendaftaran.
                </p>
                <a href="login.php" class="inline-flex rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground shadow-sm hover:bg-primary-hover hover-transition">
                    Mulai Pendaftaran
                </a>
            </div>
        </section>
        
        <section class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="mb-8 text-center text-2xl font-bold text-dark">Alur Pendaftaran</h2>
                <div class="mx-auto grid max-w-4xl gap-8 md:grid-cols-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground shadow-md">
                            1
                        </div>
                        <h3 class="mb-2 font-semibold text-dark">Login</h3>
                        <p class="text-sm text-muted-foreground">Masuk ke sistem dengan akun yang telah terdaftar</p>
                    </div>
                    
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground shadow-md">
                            2
                        </div>
                        <h3 class="mb-2 font-semibold text-dark">Informasi PPDB</h3>
                        <p class="text-sm text-muted-foreground">Baca informasi tentang penerimaan peserta didik baru</p>
                    </div>
                    
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground shadow-md">
                            3
                        </div>
                        <h3 class="mb-2 font-semibold text-dark">Pendaftaran</h3>
                        <p class="text-sm text-muted-foreground">Isi formulir pendaftaran dengan data yang benar</p>
                    </div>
                    
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground shadow-md">
                            4
                        </div>
                        <h3 class="mb-2 font-semibold text-dark">Selesai</h3>
                        <p class="text-sm text-muted-foreground">Pendaftaran selesai dan menunggu hasil pemeriksaan</p>
                    </div>
                </div>
                <h2 class="mt-10 font-bold text-4x1 text-dark text-center">Gratis Baju Batik & Kaos Bagi 100 Pendaftar Pertama</h2>
            </div>
        </section>
        
        <section class="bg-light py-12">
            <div class="container mx-auto px-4">
                <h2 class="mb-8 text-center text-2xl font-bold text-dark">Jadwal Penting</h2>
                <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border bg-white shadow-md">
                    <div class="grid grid-cols-2 border-b p-4 font-medium text-dark">
                        <div>Kegiatan</div>
                        <div>Tanggal</div>
                    </div>
                    <div class="grid grid-cols-2 border-b p-4 text-dark">
                        <div>Pendaftaran Gelombang 1</div>
                        <div>1 Maret - 31 Mei 2025</div>
                    </div>
                    <div class="grid grid-cols-2 border-b p-4 text-dark">
                        <div>Pendaftaran Gelombang 2</div>
                        <div>1 Juni - 15 Juli 2025</div>
                    </div>
                </div>
            </div>
        </section>
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
</body>
</html>

