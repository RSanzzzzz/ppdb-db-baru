<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'PPDB Online'; ?></title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

