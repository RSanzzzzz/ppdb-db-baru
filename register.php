<?php
session_start();
require_once "includes/function/pwValidation.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    
    // Simple validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($name)) {
        $error = 'Semua field harus diisi';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } elseif (!isStrongPassword($password)) {
        $error = 'Password tidak kuat! Minimal 6 karakter (Huruf besar, Huruf kecil, angka dan simbol)';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        require_once 'config/database.php';
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userByUsername = $stmt->fetch();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userByEmail = $stmt->fetch();

        if ($userByUsername) {
            $error = 'Username sudah terdaftar';
        } elseif ($userByEmail) {
            $error = 'Email sudah terdaftar';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, name) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$username, $hashed_password, $email, $name]);
            
            if ($result) {
                $success = 'Pendaftaran berhasil! Silahkan login.';
            } else {
                $error = 'Terjadi kesalahan, silahkan coba lagi';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - PPDB Online</title>
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
      input:focus, select:focus, textarea:focus {
          border-color: #3d84e1 !important; /* primary */
          box-shadow: 0 0 0 1px #3d84e1 !important; /* primary */
      }
  </style>
</head>
<body class="flex min-h-screen items-center justify-center bg-light p-4 text-default">
  <div class="w-full max-w-md rounded-lg border bg-white p-6 shadow-md">
      <div class="mb-6 text-center">
          <h1 class="text-2xl font-bold text-dark">Daftar Akun PPDB</h1>
          <p class="text-muted-foreground">Buat akun baru untuk mendaftar PPDB</p>
      </div>
      
      <?php if ($error): ?>
          <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
              <?php echo htmlspecialchars($error); ?>
          </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
          <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
              <?php echo htmlspecialchars($success); ?>
          </div>
      <?php endif; ?>
      
      <form method="POST" action="register.php" class="space-y-4">
          <div class="space-y-2">
              <label for="name" class="block text-sm font-medium text-dark">Nama Lengkap</label>
              <input 
                  type="text" 
                  id="name" 
                  name="name" 
                  placeholder="Masukkan nama lengkap" 
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                  required
              >
          </div>
          
          <div class="space-y-2">
              <label for="username" class="block text-sm font-medium text-dark">Username</label>
              <input 
                  type="text" 
                  id="username" 
                  name="username" 
                  placeholder="Masukkan username" 
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                  required
              >
          </div>
          
          <div class="space-y-2">
              <label for="email" class="block text-sm font-medium text-dark">Email</label>
              <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  placeholder="Masukkan email" 
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                  required
              >
          </div>
          
          <div class="space-y-2">
              <label for="password" class="block text-sm font-medium text-dark">Password</label>
              <input 
                  type="password" 
                  id="password" 
                  name="password" 
                  placeholder="Masukkan password" 
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                  required
              >
          </div>
          
          <div class="space-y-2">
              <label for="confirm_password" class="block text-sm font-medium text-dark">Konfirmasi Password</label>
              <input 
                  type="password" 
                  id="confirm_password" 
                  name="confirm_password" 
                  placeholder="Masukkan konfirmasi password" 
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                  required
              >
          </div>
          
          <button 
              type="submit" 
              class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 hover-transition shadow-sm"
          >
              Daftar
          </button>
          
          <div class="text-center text-sm">
              Sudah punya akun? 
              <a href="login.php" class="text-primary hover:text-primary-hover hover:underline hover-transition">Login disini</a>
          </div>
      </form>
  </div>
</body>
</html>

