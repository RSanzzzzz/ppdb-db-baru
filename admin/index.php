<?php
session_start();

require_once '../config/database.php';

// Redirect to admin dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
  header("Location: dashboard.php");
  exit();
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  
  // Simple validation
  if (empty($username) || empty($password)) {
      $error = 'Username dan password harus diisi';
  } else {
      // Check admin credentials
      $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
      $stmt->execute([$username]);
      $admin = $stmt->fetch();
        
        if (password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'];
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Username atau password salah';
        }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - PPDB Online</title>
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
          <h1 class="text-2xl font-bold text-dark">Admin Login</h1>
          <p class="text-muted-foreground">Masuk ke panel admin PPDB Online MTs Al-Ishlah Bojonggambir</p>
      </div>
      
      <?php if ($error): ?>
          <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
              <?php echo htmlspecialchars($error); ?>
          </div>
      <?php endif; ?>
      
      <form method="POST" action="index.php" class="space-y-4">
          <div class="space-y-2">
              <label for="username" class="block text-sm font-medium text-dark">Username Admin</label>
              <input 
                  type="text" 
                  id="username" 
                  name="username" 
                  placeholder="Masukkan username admin" 
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
          
          <button 
              type="submit" 
              class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover active:bg-primary-active focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 hover-transition"
          >
              Login
          </button>
          
          <!-- <div class="text-center text-sm">
              <a href="../index.php" class="text-primary hover:text-primary-hover hover:underline hover-transition">Kembali ke Halaman Utama</a>
          </div> -->
      </form>
  </div>
</body>
</html>

