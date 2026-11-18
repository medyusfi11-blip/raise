<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
startSession();
requireLogin();

$admin = getCurrentAdmin();
$adminName = $admin['full_name'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings â€” Ibrahim Azzouz Cars</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/site-enhancements.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a1628; }
        .sidebar { background: linear-gradient(180deg, #0f1d36 0%, #0a1628 100%); border-right: 1px solid rgba(255,255,255,0.06); }
        .nav-link { transition: all 0.2s; border-left: 3px solid transparent; }
        .nav-link:hover { background: rgba(6,182,212,0.08); border-left-color: #06b6d4; color: #06b6d4; }
        .nav-link.active { background: rgba(30,58,138,0.3); border-left-color: #06b6d4; color: #06b6d4; }
        .settings-card { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; }
        .glass-input { background: rgba(30, 58, 138, 0.2); border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; }
        .glass-input:focus { background: rgba(30, 58, 138, 0.3); border-color: #06b6d4; box-shadow: 0 0 0 3px rgba(6,182,212,0.15); outline: none; }
        .btn-primary { background: linear-gradient(135deg, #1e3a8a, #2563eb); }
        .btn-primary:hover { box-shadow: 0 0 20px rgba(37,99,235,0.3); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-gold { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .btn-gold:hover { box-shadow: 0 0 20px rgba(245,158,11,0.3); }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 12px 20px; border-radius: 12px; color: #fff; font-size: 0.875rem; font-weight: 500; animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 3.7s forwards; display: flex; align-items: center; gap: 10px; min-width: 280px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .toast-success { background: linear-gradient(135deg, #059669, #10b981); }
        .toast-error { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .toast-info { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s; position: fixed; z-index: 50; height: 100vh; width: 260px; } .sidebar.open { transform: translateX(0); } .sidebar-overlay.open { display: block; } }
    </style>
</head>
<body class="min-h-screen text-white">

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar fixed top-0 left-0 h-full w-64 flex flex-col z-50" id="sidebar">
    <div class="p-6 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-900/50 border border-cyan-500/30 flex items-center justify-center"><i class="fas fa-car-side text-cyan-400"></i></div>
            <div><h2 class="text-sm font-bold text-white leading-tight">Ibrahim Azzouz</h2><p class="text-xs text-slate-500">Cars</p></div>
        </div>
    </div>
    <nav class="flex-1 py-4 px-3 overflow-y-auto">
        <p class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-3 px-3">Navigation</p>
        <a href="index.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1"><i class="fas fa-chart-pie w-5 text-center"></i><span data-i18n="dashboard">Dashboard</span></a>
        <a href="cars.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1"><i class="fas fa-car w-5 text-center"></i><span data-i18n="cars">Cars</span></a>
        <a href="bookings.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1"><i class="fas fa-calendar-check w-5 text-center"></i><span data-i18n="bookings">Bookings</span></a>
        <a href="settings.php" class="nav-link active flex items-center gap-3 px-4 py-3 rounded-lg text-sm mb-1"><i class="fas fa-cog w-5 text-center"></i><span data-i18n="settings">Settings</span></a>
    </nav>
    <div class="p-4 border-t border-white/5">
        <a href="#" onclick="doLogout()" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-all text-sm"><i class="fas fa-sign-out-alt w-5 text-center"></i><span data-i18n="logout">Logout</span></a>
    </div>
</aside>

<div class="md:ml-64 min-h-screen flex flex-col">
    <header class="sticky top-0 z-30 bg-[#0a1628]/80 backdrop-blur-xl border-b border-white/5">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white"><i class="fas fa-bars text-xl"></i></button>
                <div>
                    <h1 class="text-lg font-bold text-white" data-i18n="settings">Settings</h1>
                    <p class="text-xs text-slate-500">Manage your configuration</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm"><?= sanitize($adminInitial) ?></div>
                <span class="text-sm text-slate-300 hidden sm:block"><?= sanitize($adminName) ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 p-6 max-w-3xl w-full mx-auto space-y-6">

        <div class="settings-card p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-900/40 flex items-center justify-center">
                    <i class="fas fa-globe text-cyan-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white" data-i18n="site_settings">Site Settings</h2>
                    <p class="text-xs text-slate-500">General site configuration</p>
                </div>
            </div>
            <form id="siteForm" onsubmit="saveSiteSettings(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Site Name</label>
                        <input type="text" id="siteName" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Phone</label>
                        <input type="text" id="sitePhone" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">WhatsApp</label>
                        <input type="text" id="siteWhatsapp" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Instagram URL</label>
                        <input type="text" id="siteInstagram" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                        <input type="email" id="siteEmail" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" id="siteSaveBtn" class="btn-primary px-6 py-2.5 rounded-lg text-white text-sm font-semibold flex items-center gap-2">
                        <span id="siteSaveText" data-i18n="save_settings">Save Settings</span>
                        <span id="siteSaveLoader" class="hidden"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>

        <div class="settings-card p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-900/40 flex items-center justify-center">
                    <i class="fas fa-lock text-amber-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white" data-i18n="change_password">Change Password</h2>
                    <p class="text-xs text-slate-500">Update your admin password</p>
                </div>
            </div>
            <form id="passwordForm" onsubmit="changePassword(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Current Password</label>
                    <input type="password" id="currentPassword" required class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">New Password</label>
                        <input type="password" id="newPassword" required minlength="6" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Confirm Password</label>
                        <input type="password" id="confirmPassword" required minlength="6" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" id="passSaveBtn" class="btn-primary px-6 py-2.5 rounded-lg text-white text-sm font-semibold flex items-center gap-2">
                        <span id="passSaveText" data-i18n="change_password">Change Password</span>
                        <span id="passSaveLoader" class="hidden"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>

        <div class="settings-card p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-emerald-900/40 flex items-center justify-center">
                    <i class="fas fa-user-edit text-emerald-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white" data-i18n="admin_profile">Admin Profile</h2>
                    <p class="text-xs text-slate-500">Update your personal information</p>
                </div>
            </div>
            <form id="profileForm" onsubmit="updateProfile(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Full Name</label>
                        <input type="text" id="profileName" required class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                        <input type="email" id="profileEmail" required class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" id="profileSaveBtn" class="btn-primary px-6 py-2.5 rounded-lg text-white text-sm font-semibold flex items-center gap-2">
                        <span id="profileSaveText" data-i18n="update_profile">Update Profile</span>
                        <span id="profileSaveLoader" class="hidden"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>

    </main>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
    function showToast(msg, type = 'info') {
        const c = document.getElementById('toastContainer');
        const t = document.createElement('div');
        t.className = 'toast toast-' + type;
        const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
        t.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + msg + '</span>';
        c.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('open');
    }

    function doLogout() {
        fetch('../api/auth.php?action=logout', { method: 'POST' }).then(() => window.location.href = 'login.php');
    }

    function loadSettings() {
        fetch('../api/content.php?action=settings')
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                document.getElementById('siteName').value = d.site_name || '';
                document.getElementById('sitePhone').value = d.site_phone || '';
                document.getElementById('siteWhatsapp').value = d.site_whatsapp || '';
                document.getElementById('siteInstagram').value = d.site_instagram || '';
                document.getElementById('siteEmail').value = d.site_email || '';
            })
            .catch(() => showToast('Failed to load settings', 'error'));
    }

    function loadProfile() {
        fetch('../api/auth.php?action=check')
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                document.getElementById('profileName').value = res.data.full_name || '';
                document.getElementById('profileEmail').value = res.data.email || '';
            })
            .catch(() => {});
    }

    async function saveSiteSettings(e) {
        e.preventDefault();
        const btn = document.getElementById('siteSaveBtn');
        const text = document.getElementById('siteSaveText');
        const loader = document.getElementById('siteSaveLoader');
        btn.disabled = true; text.classList.add('hidden'); loader.classList.remove('hidden');

        const data = {
            site_name: document.getElementById('siteName').value,
            site_phone: document.getElementById('sitePhone').value,
            site_whatsapp: document.getElementById('siteWhatsapp').value,
            site_instagram: document.getElementById('siteInstagram').value,
            site_email: document.getElementById('siteEmail').value
        };

        try {
            const res = await fetch('../api/content.php?action=settings', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Settings saved', 'success');
            } else {
                showToast(json.message || 'Failed to save settings', 'error');
            }
        } catch (err) {
            showToast('Request failed', 'error');
        }
        btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
    }

    async function changePassword(e) {
        e.preventDefault();
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        if (newPass !== confirmPass) {
            showToast('Passwords do not match', 'error');
            return;
        }
        if (newPass.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }

        const btn = document.getElementById('passSaveBtn');
        const text = document.getElementById('passSaveText');
        const loader = document.getElementById('passSaveLoader');
        btn.disabled = true; text.classList.add('hidden'); loader.classList.remove('hidden');

        try {
            const res = await fetch('../api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'change_password',
                    current_password: document.getElementById('currentPassword').value,
                    new_password: newPass,
                    confirm_password: confirmPass
                })
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Password changed', 'success');
                document.getElementById('passwordForm').reset();
            } else {
                showToast(json.message || 'Failed to change password', 'error');
            }
        } catch (err) {
            showToast('Request failed', 'error');
        }
        btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
    }

    async function updateProfile(e) {
        e.preventDefault();
        const btn = document.getElementById('profileSaveBtn');
        const text = document.getElementById('profileSaveText');
        const loader = document.getElementById('profileSaveLoader');
        btn.disabled = true; text.classList.add('hidden'); loader.classList.remove('hidden');

        try {
            const res = await fetch('../api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_profile',
                    full_name: document.getElementById('profileName').value,
                    email: document.getElementById('profileEmail').value
                })
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Profile updated', 'success');
            } else {
                showToast(json.message || 'Failed to update profile', 'error');
            }
        } catch (err) {
            showToast('Request failed', 'error');
        }
        btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
    }

    loadSettings();
    loadProfile();
</script>
    <script src="../assets/js/admin-i18n-theme.js"></script>
</body>
</html>



