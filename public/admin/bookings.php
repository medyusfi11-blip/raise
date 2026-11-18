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
    <title>Booking Management â€” Ibrahim Azzouz Cars</title>
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
        .glass-table { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); }
        .glass-input { background: rgba(30, 58, 138, 0.2); border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; }
        .glass-input:focus { background: rgba(30, 58, 138, 0.3); border-color: #06b6d4; box-shadow: 0 0 0 3px rgba(6,182,212,0.15); outline: none; }
        .badge { padding: 4px 10px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-accepted { background: rgba(34,197,94,0.15); color: #22c55e; }
        .badge-rejected { background: rgba(239,68,68,0.15); color: #ef4444; }
        .badge-completed { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 60; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #0f1d36; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
        .btn-action-accept { background: linear-gradient(135deg, #059669, #10b981); }
        .btn-action-reject { background: linear-gradient(135deg, #991b1b, #dc2626); }
        .btn-action-complete { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        .btn-danger { background: linear-gradient(135deg, #991b1b, #dc2626); }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 12px 20px; border-radius: 12px; color: #fff; font-size: 0.875rem; font-weight: 500; animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 3.7s forwards; display: flex; align-items: center; gap: 10px; min-width: 280px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .toast-success { background: linear-gradient(135deg, #059669, #10b981); }
        .toast-error { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .toast-info { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s; position: fixed; z-index: 50; height: 100vh; width: 260px; } .sidebar.open { transform: translateX(0); } .sidebar-overlay.open { display: block; } }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .detail-label { color: #64748b; font-size: 0.8rem; }
        .detail-value { color: #e2e8f0; font-size: 0.8rem; font-weight: 500; }
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
        <a href="bookings.php" class="nav-link active flex items-center gap-3 px-4 py-3 rounded-lg text-sm mb-1"><i class="fas fa-calendar-check w-5 text-center"></i><span data-i18n="bookings">Bookings</span></a>
        <a href="settings.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1"><i class="fas fa-cog w-5 text-center"></i><span data-i18n="settings">Settings</span></a>
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
                    <h1 class="text-lg font-bold text-white" data-i18n="booking_management">Booking Management</h1>
                    <p class="text-xs text-slate-500" data-i18n="manage_bookings">Manage all rental bookings</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm"><?= sanitize($adminInitial) ?></div>
                <span class="text-sm text-slate-300 hidden sm:block"><?= sanitize($adminName) ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 p-6">
        <div class="glass-table rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500"><i class="fas fa-search text-sm"></i></span>
                    <input type="text" id="searchInput" placeholder="Search bookings..." data-i18n-placeholder="search_bookings" class="glass-input w-full pl-9 pr-4 py-2.5 rounded-lg text-white text-sm placeholder-slate-500" oninput="debounceSearch()">
                </div>
                <select id="filterStatus" class="glass-input px-3 py-2.5 rounded-lg text-white text-sm" onchange="loadBookings()">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-3 font-semibold">ID</th>
                            <th class="px-6 py-3 font-semibold">Customer</th>
                            <th class="px-6 py-3 font-semibold">Car</th>
                            <th class="px-6 py-3 font-semibold">Dates</th>
                            <th class="px-6 py-3 font-semibold">Days</th>
                            <th class="px-6 py-3 font-semibold">Total</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTable">
                        <tr><td colspan="8" class="px-6 py-12 text-center text-slate-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p class="text-xs">Loading bookings...</p></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <p class="text-xs text-slate-500" id="paginationInfo">Showing 0 bookings</p>
                <div class="flex items-center gap-2">
                    <button onclick="prevPage()" id="prevBtn" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 text-xs hover:bg-slate-700 transition-colors disabled:opacity-40" disabled>Previous</button>
                    <span class="text-xs text-slate-500 px-2" id="pageInfo">Page 1</span>
                    <button onclick="nextPage()" id="nextBtn" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 text-xs hover:bg-slate-700 transition-colors disabled:opacity-40" disabled>Next</button>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Booking Details</h3>
            <button onclick="closeDetailModal()" class="text-slate-500 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6" id="detailContent">
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:400px">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash-alt text-red-400 text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-white mb-2">Delete Booking</h3>
            <p class="text-sm text-slate-400 mb-6">Are you sure you want to delete this booking? This action cannot be undone.</p>
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2.5 rounded-lg bg-slate-800 text-slate-400 text-sm hover:bg-slate-700 transition-colors">Cancel</button>
                <button onclick="confirmDelete()" id="confirmDeleteBtn" class="btn-danger px-6 py-2.5 rounded-lg text-white text-sm font-semibold">
                    <span id="deleteBtnText">Delete</span>
                    <span id="deleteBtnLoader" class="hidden"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
    let currentPage = 0;
    const PAGE_LIMIT = 20;
    let totalBookings = 0;
    let deleteBookingId = null;
    let searchTimeout = null;

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

    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { currentPage = 0; loadBookings(); }, 300);
    }

    function statusBadge(status) {
        const map = { Pending: 'badge-pending', Accepted: 'badge-accepted', Rejected: 'badge-rejected', Completed: 'badge-completed' };
        return '<span class="badge ' + (map[status] || '') + '">' + status + '</span>';
    }

    function loadBookings() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('filterStatus').value;
        let url = '../api/bookings.php?limit=' + PAGE_LIMIT + '&offset=' + (currentPage * PAGE_LIMIT);
        if (search) url += '&search=' + encodeURIComponent(search);
        if (status) url += '&status=' + encodeURIComponent(status);

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { showToast(res.message || 'Failed to load', 'error'); return; }
                totalBookings = res.meta.total;
                renderBookings(res.data);
                updatePagination();
            })
            .catch(() => showToast('Failed to load bookings', 'error'));
    }

    function renderBookings(bookings) {
        const tbody = document.getElementById('bookingsTable');
        if (bookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-12 text-center text-slate-500 text-sm">No bookings found.</td></tr>';
            return;
        }
        let html = '';
        bookings.forEach(b => {
            const start = new Date(b.start_date).toLocaleDateString('en-GB');
            const end = new Date(b.end_date).toLocaleDateString('en-GB');
            let actions = '';
            if (b.status === 'Pending') {
                actions = '<button onclick="updateStatus(' + b.id + ',\'Accepted\')" class="btn-action-accept px-2.5 py-1 rounded-md text-white text-xs font-medium mr-1" title="Accept"><i class="fas fa-check"></i></button>' +
                    '<button onclick="updateStatus(' + b.id + ',\'Rejected\')" class="btn-action-reject px-2.5 py-1 rounded-md text-white text-xs font-medium mr-1" title="Reject"><i class="fas fa-times"></i></button>';
            } else if (b.status === 'Accepted') {
                actions = '<button onclick="updateStatus(' + b.id + ',\'Completed\')" class="btn-action-complete px-2.5 py-1 rounded-md text-white text-xs font-medium mr-1" title="Complete"><i class="fas fa-flag-checkered"></i></button>';
            }
            actions += '<button onclick="viewDetails(' + b.id + ')" class="text-slate-400 hover:text-cyan-400 transition-colors mr-2" title="View"><i class="fas fa-eye"></i></button>';
            actions += '<button onclick="openDeleteModal(' + b.id + ')" class="text-slate-400 hover:text-red-400 transition-colors" title="Delete"><i class="fas fa-trash-alt"></i></button>';

            html += '<tr class="border-t border-white/5 hover:bg-white/[0.02] transition-colors">' +
                '<td class="px-6 py-4 text-slate-400">#' + b.id + '</td>' +
                '<td class="px-6 py-4"><p class="text-white font-medium text-sm">' + b.customer_name + '</p><p class="text-slate-500 text-xs">' + b.customer_phone + '</p></td>' +
                '<td class="px-6 py-4 text-slate-300 text-sm">' + b.car_name + ' ' + b.car_model + '</td>' +
                '<td class="px-6 py-4 text-slate-300 text-xs">' + start + ' &rarr; ' + end + '</td>' +
                '<td class="px-6 py-4 text-slate-300 text-sm">' + b.total_days + '</td>' +
                '<td class="px-6 py-4 text-white font-medium">' + Number(b.total_price).toLocaleString() + ' DH</td>' +
                '<td class="px-6 py-4">' + statusBadge(b.status) + '</td>' +
                '<td class="px-6 py-4 text-right">' + actions + '</td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
    }

    function updatePagination() {
        const totalPages = Math.ceil(totalBookings / PAGE_LIMIT);
        const showing = Math.min((currentPage + 1) * PAGE_LIMIT, totalBookings);
        document.getElementById('paginationInfo').textContent = 'Showing ' + showing + ' of ' + totalBookings + ' bookings';
        document.getElementById('pageInfo').textContent = 'Page ' + (currentPage + 1);
        document.getElementById('prevBtn').disabled = currentPage === 0;
        document.getElementById('nextBtn').disabled = (currentPage + 1) >= totalPages;
    }

    function prevPage() { if (currentPage > 0) { currentPage--; loadBookings(); } }
    function nextPage() { if ((currentPage + 1) * PAGE_LIMIT < totalBookings) { currentPage++; loadBookings(); } }

    async function updateStatus(id, status) {
        try {
            const res = await fetch('../api/bookings.php?id=' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: status })
            });
            const json = await res.json();
            if (json.success) {
                showToast('Booking ' + status.toLowerCase() + ' successfully', 'success');
                loadBookings();
            } else {
                showToast(json.message || 'Failed to update', 'error');
            }
        } catch (err) {
            showToast('Request failed', 'error');
        }
    }

    function viewDetails(id) {
        fetch('../api/bookings.php?id=' + id)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { showToast(res.message, 'error'); return; }
                const b = res.data;
                const start = new Date(b.start_date).toLocaleDateString('en-GB');
                const end = new Date(b.end_date).toLocaleDateString('en-GB');
                const created = new Date(b.created_at).toLocaleString('en-GB');
                let html = '<div class="space-y-0">' +
                    detailRow('Booking ID', '#' + b.id) +
                    detailRow('Status', statusBadge(b.status)) +
                    detailRow('Car', b.car_name + ' ' + b.car_model) +
                    detailRow('Customer', b.customer_name) +
                    detailRow('Email', b.customer_email) +
                    detailRow('Phone', b.customer_phone) +
                    detailRow('City', b.customer_city || 'â€”') +
                    detailRow('Start Date', start) +
                    detailRow('End Date', end) +
                    detailRow('Total Days', b.total_days) +
                    detailRow('Total Price', Number(b.total_price).toLocaleString() + ' DH') +
                    detailRow('Notes', b.notes || 'â€”') +
                    detailRow('Created', created) +
                    '</div>';
                document.getElementById('detailContent').innerHTML = html;
                document.getElementById('detailModal').classList.add('open');
            })
            .catch(() => showToast('Failed to load details', 'error'));
    }

    function detailRow(label, value) {
        return '<div class="detail-row"><span class="detail-label">' + label + '</span><span class="detail-value">' + value + '</span></div>';
    }

    function closeDetailModal() { document.getElementById('detailModal').classList.remove('open'); }

    function openDeleteModal(id) { deleteBookingId = id; document.getElementById('deleteModal').classList.add('open'); }
    function closeDeleteModal() { deleteBookingId = null; document.getElementById('deleteModal').classList.remove('open'); }

    async function confirmDelete() {
        if (!deleteBookingId) return;
        const btn = document.getElementById('confirmDeleteBtn');
        const text = document.getElementById('deleteBtnText');
        const loader = document.getElementById('deleteBtnLoader');
        btn.disabled = true; text.classList.add('hidden'); loader.classList.remove('hidden');

        try {
            const res = await fetch('../api/bookings.php?id=' + deleteBookingId, { method: 'DELETE' });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Booking deleted', 'success');
                closeDeleteModal();
                loadBookings();
            } else {
                showToast(json.message || 'Delete failed', 'error');
            }
        } catch (err) {
            showToast('Delete request failed', 'error');
        }
        btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
    }

    loadBookings();
</script>
    <script src="../assets/js/admin-i18n-theme.js"></script>
</body>
</html>



