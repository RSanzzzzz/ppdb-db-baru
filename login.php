<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple validation
    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        require_once 'config/database.php';
        
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            
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
  <title>Login - PPDB Online</title>
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
          <h1 class="text-2xl font-bold text-dark">Login PPDB MTs Al-Ishlah</h1>
          <p class="text-muted-foreground">Masukkan username dan password</p>
      </div>
      
      <?php if ($error): ?>
          <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
              <?php echo htmlspecialchars($error); ?>
          </div>
      <?php endif; ?>
      
      <form method="POST" action="login.php" class="space-y-4">
          <div class="space-y-2">
              <label for="username" class="block text-sm font-medium text-dark">Username / Email</label>
              <input 
                  type="text" 
                  id="username" 
                  name="username" 
                  placeholder="Masukkan username / email terdaftar" 
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-darker focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                  required
              >
          </div>
          
          <div class="space-y-2">
              <div class="flex items-center justify-between">
                  <label for="password" class="block text-sm font-medium text-dark">Password</label>
                  <!-- <a href="#" class="text-xs text-primary hover:text-primary-hover hover:underline hover-transition">Lupa password?</a> -->
              </div>
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
          
          <div class="text-center text-sm">
              Belum punya akun? 
              <a href="register.php" class="text-primary hover:text-primary-hover hover:underline hover-transition">Daftar disini</a>
          </div>
      </form>
  </div>
</body>
</html>

