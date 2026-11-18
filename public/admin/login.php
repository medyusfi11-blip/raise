<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
startSession();

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $admin = attemptLogin($username, $password);
            if ($admin) {
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login â€” Ibrahim Azzouz Cars</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/site-enhancements.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-input {
            background: rgba(30, 58, 138, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        .glass-input:focus {
            background: rgba(30, 58, 138, 0.3);
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.4);
        }
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .bg-mesh {
            background-color: #0a1628;
            background-image:
                radial-gradient(at 20% 80%, rgba(30, 58, 138, 0.4) 0, transparent 50%),
                radial-gradient(at 80% 20%, rgba(6, 182, 212, 0.2) 0, transparent 50%),
                radial-gradient(at 50% 50%, rgba(245, 158, 11, 0.05) 0, transparent 50%);
        }
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
    </style>
</head>
<body class="bg-mesh min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md fade-in">
        <div class="glass rounded-2xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-900/50 border border-cyan-500/30 mb-4">
                    <i class="fas fa-car-side text-2xl text-cyan-400"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Ibrahim Azzouz Cars</h1>
                <p class="text-slate-400 text-sm mt-1">Admin Dashboard</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            required
                            autocomplete="username"
                            value="<?= sanitize($_POST['username'] ?? '') ?>"
                            placeholder="Enter your username"
                            class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-white placeholder-slate-500"
                        >
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="glass-input w-full pl-11 pr-12 py-3 rounded-xl text-white placeholder-slate-500"
                        >
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-cyan-400 transition-colors"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-600 focus:ring-cyan-500 focus:ring-offset-0">
                        <span class="text-sm text-slate-400">Remember me</span>
                    </label>
                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="btn-primary w-full py-3 rounded-xl text-white font-semibold text-sm tracking-wide"
                >
                    <span id="btnText">Sign In</span>
                    <span id="btnLoader" class="hidden">
                        <i class="fas fa-spinner fa-spin"></i> Signing in...
                    </span>
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="../index.php" class="text-sm text-slate-500 hover:text-cyan-400 transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Back to Website
                </a>
            </div>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            &copy; <?= date('Y') ?> Ibrahim Azzouz Cars. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const loader = document.getElementById('btnLoader');
            btn.disabled = true;
            text.classList.add('hidden');
            loader.classList.remove('hidden');
        });
    </script>
</body>
</html>

