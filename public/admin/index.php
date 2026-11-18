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
    <title>Dashboard â€” Ibrahim Azzouz Cars</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/site-enhancements.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a1628; }
        .sidebar {
            background: linear-gradient(180deg, #0f1d36 0%, #0a1628 100%);
            border-right: 1px solid rgba(255,255,255,0.06);
        }
        .nav-link {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .nav-link:hover { background: rgba(6,182,212,0.08); border-left-color: #06b6d4; color: #06b6d4; }
        .nav-link.active { background: rgba(30,58,138,0.3); border-left-color: #06b6d4; color: #06b6d4; }
        .stat-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s;
        }
        .stat-card:hover { border-color: rgba(255,255,255,0.12); transform: translateY(-2px); }
        .glass-table {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .badge { padding: 4px 10px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-accepted { background: rgba(34,197,94,0.15); color: #22c55e; }
        .badge-rejected { background: rgba(239,68,68,0.15); color: #ef4444; }
        .badge-completed { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast {
            padding: 12px 20px; border-radius: 12px; color: #fff; font-size: 0.875rem; font-weight: 500;
            animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 3.7s forwards;
            display: flex; align-items: center; gap: 10px; min-width: 280px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .toast-success { background: linear-gradient(135deg, #059669, #10b981); }
        .toast-error { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .toast-info { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; position: fixed; z-index: 50; height: 100vh; width: 260px; }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
        }
        @keyframes countUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .count-animate { animation: countUp 0.4s ease-out; }
    </style>
</head>
<body class="min-h-screen text-white">

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar fixed top-0 left-0 h-full w-64 flex flex-col z-50" id="sidebar">
    <div class="p-6 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-900/50 border border-cyan-500/30 flex items-center justify-center">
                <i class="fas fa-car-side text-cyan-400"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-white leading-tight">Ibrahim Azzouz</h2>
                <p class="text-xs text-slate-500">Cars</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 py-4 px-3 overflow-y-auto">
        <p class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-3 px-3">Navigation</p>
        <a href="index.php" class="nav-link active flex items-center gap-3 px-4 py-3 rounded-lg text-sm mb-1">
            <i class="fas fa-chart-pie w-5 text-center"></i>
            <span data-i18n="dashboard">Dashboard</span>
        </a>
        <a href="cars.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1">
            <i class="fas fa-car w-5 text-center"></i>
            <span data-i18n="cars">Cars</span>
        </a>
        <a href="bookings.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1">
            <i class="fas fa-calendar-check w-5 text-center"></i>
            <span data-i18n="bookings">Bookings</span>
        </a>
        <a href="settings.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1">
            <i class="fas fa-cog w-5 text-center"></i>
            <span data-i18n="settings">Settings</span>
        </a>
    </nav>

    <div class="p-4 border-t border-white/5">
        <a href="#" onclick="doLogout()" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-all text-sm">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span data-i18n="logout">Logout</span>
        </a>
    </div>
</aside>

<div class="md:ml-64 min-h-screen flex flex-col">
    <header class="sticky top-0 z-30 bg-[#0a1628]/80 backdrop-blur-xl border-b border-white/5">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-white">Dashboard</h1>
                    <p class="text-xs text-slate-500">Welcome back, <?= sanitize($adminName) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                        <?= sanitize($adminInitial) ?>
                    </div>
                    <span class="text-sm text-slate-300 hidden sm:block"><?= sanitize($adminName) ?></span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="stat-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-900/40 flex items-center justify-center">
                        <i class="fas fa-car text-blue-400"></i>
                    </div>
                    <span class="text-xs text-slate-500 font-medium" data-i18n="total_cars">Total Cars</span>
                </div>
                <p class="text-2xl font-bold text-white count-animate" id="statCars">â€”</p>
                <p class="text-xs text-slate-500 mt-1" id="statCarsSub">Loading...</p>
            </div>
            <div class="stat-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-900/40 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-cyan-400"></i>
                    </div>
                    <span class="text-xs text-slate-500 font-medium" data-i18n="total_bookings">Total Bookings</span>
                </div>
                <p class="text-2xl font-bold text-white count-animate" id="statBookings">â€”</p>
                <p class="text-xs text-slate-500 mt-1" id="statBookingsSub">Loading...</p>
            </div>
            <div class="stat-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-900/40 flex items-center justify-center">
                        <i class="fas fa-clock text-amber-400"></i>
                    </div>
                    <span class="text-xs text-slate-500 font-medium" data-i18n="pending">Pending</span>
                </div>
                <p class="text-2xl font-bold text-white count-animate" id="statPending">â€”</p>
                <p class="text-xs text-slate-500 mt-1" id="statPendingSub">Awaiting review</p>
            </div>
            <div class="stat-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-900/40 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-emerald-400"></i>
                    </div>
                    <span class="text-xs text-slate-500 font-medium" data-i18n="revenue">Revenue</span>
                </div>
                <p class="text-2xl font-bold text-white count-animate" id="statRevenue">â€”</p>
                <p class="text-xs text-slate-500 mt-1" id="statRevenueSub">Total earned</p>
            </div>
        </div>

        <div class="glass-table rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white" data-i18n="recent_bookings">Recent Bookings</h2>
                <a href="bookings.php" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors" data-i18n="view_all">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-3 font-semibold">ID</th>
                            <th class="px-6 py-3 font-semibold">Customer</th>
                            <th class="px-6 py-3 font-semibold">Car</th>
                            <th class="px-6 py-3 font-semibold">Amount</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody id="recentBookings">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p class="text-xs">Loading bookings...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
        toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + message + '</span>';
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('open');
    }

    function doLogout() {
        fetch('../api/auth.php?action=logout', { method: 'POST' })
            .then(() => { window.location.href = 'login.php'; })
            .catch(() => { window.location.href = 'login.php'; });
    }

    const statusBadge = (status) => {
        const map = {
            Pending: 'badge-pending',
            Accepted: 'badge-accepted',
            Rejected: 'badge-rejected',
            Completed: 'badge-completed'
        };
        return '<span class="badge ' + (map[status] || '') + '">' + status + '</span>';
    };

    function loadStats() {
        fetch('../api/content.php?action=stats')
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                document.getElementById('statCars').textContent = d.total_cars;
                document.getElementById('statCarsSub').textContent = d.available_cars + ' available';
                document.getElementById('statBookings').textContent = d.total_bookings;
                document.getElementById('statBookingsSub').textContent = d.pending_bookings + ' pending';
                document.getElementById('statPending').textContent = d.pending_bookings;
                document.getElementById('statRevenue').textContent = d.revenue.toLocaleString() + ' ' + d.currency;

                const tbody = document.getElementById('recentBookings');
                if (d.recent_bookings.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">No bookings yet.</td></tr>';
                    return;
                }
                let html = '';
                d.recent_bookings.forEach(b => {
                    const date = new Date(b.created_at).toLocaleDateString('en-GB');
                    html += '<tr class="border-t border-white/5 hover:bg-white/[0.02] transition-colors">' +
                        '<td class="px-6 py-4 text-slate-400">#' + b.id + '</td>' +
                        '<td class="px-6 py-4 text-white font-medium">' + b.customer_name + '</td>' +
                        '<td class="px-6 py-4 text-slate-300">' + b.car_name + ' ' + b.car_model + '</td>' +
                        '<td class="px-6 py-4 text-white font-medium">' + Number(b.total_price).toLocaleString() + ' DH</td>' +
                        '<td class="px-6 py-4">' + statusBadge(b.status) + '</td>' +
                        '<td class="px-6 py-4 text-slate-500 text-xs">' + date + '</td>' +
                        '</tr>';
                });
                tbody.innerHTML = html;
            })
            .catch(() => showToast('Failed to load dashboard data', 'error'));
    }

    loadStats();
</script>
    <script src="../assets/js/admin-i18n-theme.js"></script>
</body>
</html>



