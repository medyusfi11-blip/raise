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
    <title>Car Management â€” Ibrahim Azzouz Cars</title>
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
        .badge-available { background: rgba(34,197,94,0.15); color: #22c55e; }
        .badge-reserved { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-maintenance { background: rgba(239,68,68,0.15); color: #ef4444; }
        .badge-type { background: rgba(6,182,212,0.15); color: #06b6d4; font-size: 0.65rem; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 60; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #0f1d36; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; }
        .btn-gold { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .btn-gold:hover { box-shadow: 0 0 20px rgba(245,158,11,0.3); }
        .btn-primary { background: linear-gradient(135deg, #1e3a8a, #2563eb); }
        .btn-primary:hover { box-shadow: 0 0 20px rgba(37,99,235,0.3); }
        .btn-danger { background: linear-gradient(135deg, #991b1b, #dc2626); }
        .btn-danger:hover { box-shadow: 0 0 20px rgba(220,38,38,0.3); }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast { padding: 12px 20px; border-radius: 12px; color: #fff; font-size: 0.875rem; font-weight: 500; animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 3.7s forwards; display: flex; align-items: center; gap: 10px; min-width: 280px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .toast-success { background: linear-gradient(135deg, #059669, #10b981); }
        .toast-error { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .toast-info { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s; position: fixed; z-index: 50; height: 100vh; width: 260px; } .sidebar.open { transform: translateX(0); } .sidebar-overlay.open { display: block; } }
        .feature-tag { display: inline-flex; align-items: center; gap: 4px; background: rgba(6,182,212,0.1); border: 1px solid rgba(6,182,212,0.2); padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; color: #06b6d4; }
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
        <a href="index.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1"><i class="fas fa-chart-pie w-5 text-center"></i><span data-i18n="dashboard">Dashboard</span></a>
        <a href="cars.php" class="nav-link active flex items-center gap-3 px-4 py-3 rounded-lg text-sm mb-1"><i class="fas fa-car w-5 text-center"></i><span data-i18n="cars">Cars</span></a>
        <a href="bookings.php" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 text-sm mb-1"><i class="fas fa-calendar-check w-5 text-center"></i><span data-i18n="bookings">Bookings</span></a>
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
                    <h1 class="text-lg font-bold text-white" data-i18n="car_management">Car Management</h1>
                    <p class="text-xs text-slate-500" data-i18n="manage_fleet">Manage your fleet</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="openAddModal()" class="btn-gold px-4 py-2 rounded-lg text-white text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-plus"></i> <span data-i18n="add_new_car">Add New Car</span>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm"><?= sanitize($adminInitial) ?></div>
                    <span class="text-sm text-slate-300 hidden sm:block"><?= sanitize($adminName) ?></span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 p-6">
        <div class="glass-table rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500"><i class="fas fa-search text-sm"></i></span>
                    <input type="text" id="searchInput" placeholder="Search cars..." data-i18n-placeholder="search_cars" class="glass-input w-full pl-9 pr-4 py-2.5 rounded-lg text-white text-sm placeholder-slate-500" oninput="debounceSearch()">
                </div>
                <select id="filterStatus" class="glass-input px-3 py-2.5 rounded-lg text-white text-sm" onchange="loadCars()">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Reserved">Reserved</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
                <select id="filterType" class="glass-input px-3 py-2.5 rounded-lg text-white text-sm" onchange="loadCars()">
                    <option value="">All Types</option>
                    <option value="Hatchback">Hatchback</option>
                    <option value="SUV">SUV</option>
                    <option value="Sedan">Sedan</option>
                    <option value="Van">Van</option>
                    <option value="Truck">Truck</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-3 font-semibold">Car</th>
                            <th class="px-6 py-3 font-semibold">Type</th>
                            <th class="px-6 py-3 font-semibold">Year</th>
                            <th class="px-6 py-3 font-semibold">Price/Day</th>
                            <th class="px-6 py-3 font-semibold">Units</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="carsTable">
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p class="text-xs">Loading cars...</p></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <p class="text-xs text-slate-500" id="paginationInfo">Showing 0 cars</p>
                <div class="flex items-center gap-2">
                    <button onclick="prevPage()" id="prevBtn" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 text-xs hover:bg-slate-700 transition-colors disabled:opacity-40" disabled>Previous</button>
                    <span class="text-xs text-slate-500 px-2" id="pageInfo">Page 1</span>
                    <button onclick="nextPage()" id="nextBtn" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 text-xs hover:bg-slate-700 transition-colors disabled:opacity-40" disabled>Next</button>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal-overlay" id="carModal">
    <div class="modal-box">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <h3 class="text-base font-bold text-white" id="modalTitle">Add New Car</h3>
            <button onclick="closeModal()" class="text-slate-500 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <form id="carForm" class="p-6 space-y-4" onsubmit="submitCar(event)">
            <input type="hidden" id="carId">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Name *</label>
                    <input type="text" id="carName" required class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Model *</label>
                    <input type="text" id="carModel" required class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Type *</label>
                    <select id="carType" required class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                        <option value="Sedan">Sedan</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="SUV">SUV</option>
                        <option value="Van">Van</option>
                        <option value="Truck">Truck</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Year *</label>
                    <input type="number" id="carYear" required min="2000" max="2030" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Price/Day (DH) *</label>
                    <input type="number" id="carPrice" required min="1" step="0.01" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Transmission</label>
                    <select id="carTransmission" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Fuel Type</label>
                    <select id="carFuel" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Seats</label>
                    <input type="number" id="carSeats" min="1" max="15" value="5" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Units Available</label>
                    <input type="number" id="carUnits" min="0" value="1" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Status</label>
                    <select id="carStatus" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm">
                        <option value="Available">Available</option>
                        <option value="Reserved">Reserved</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Description</label>
                <textarea id="carDescription" rows="3" class="glass-input w-full px-3 py-2.5 rounded-lg text-white text-sm resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Image</label>
                <input type="file" id="carImage" accept="image/*" class="glass-input w-full px-3 py-2 rounded-lg text-white text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-blue-900/50 file:text-cyan-400 file:text-sm file:cursor-pointer">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-2">Features</label>
                <div id="featuresList" class="flex flex-wrap gap-2 mb-2"></div>
                <div class="flex gap-2">
                    <input type="text" id="featureInput" placeholder="Add feature..." class="glass-input flex-1 px-3 py-2 rounded-lg text-white text-sm" onkeydown="if(event.key==='Enter'){event.preventDefault();addFeature();}">
                    <button type="button" onclick="addFeature()" class="btn-primary px-4 py-2 rounded-lg text-white text-sm font-medium"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg bg-slate-800 text-slate-400 text-sm hover:bg-slate-700 transition-colors">Cancel</button>
                <button type="submit" id="submitCarBtn" class="btn-primary px-6 py-2.5 rounded-lg text-white text-sm font-semibold flex items-center gap-2">
                    <span id="submitCarText">Save Car</span>
                    <span id="submitCarLoader" class="hidden"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:400px">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash-alt text-red-400 text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-white mb-2">Delete Car</h3>
            <p class="text-sm text-slate-400 mb-6">Are you sure you want to delete this car? This action cannot be undone.</p>
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
    const PAGE_LIMIT = 15;
    let totalCars = 0;
    let carFeatures = [];
    let deleteCarId = null;
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
        searchTimeout = setTimeout(() => { currentPage = 0; loadCars(); }, 300);
    }

    function badge(status) {
        const map = { Available: 'badge-available', Reserved: 'badge-reserved', Maintenance: 'badge-maintenance' };
        return '<span class="badge ' + (map[status] || '') + '">' + status + '</span>';
    }

    function loadCars() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('filterStatus').value;
        const type = document.getElementById('filterType').value;
        let url = '../api/cars.php?limit=' + PAGE_LIMIT + '&offset=' + (currentPage * PAGE_LIMIT);
        if (search) url += '&search=' + encodeURIComponent(search);
        if (status) url += '&status=' + encodeURIComponent(status);
        if (type) url += '&type=' + encodeURIComponent(type);

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { showToast(res.message || 'Failed to load', 'error'); return; }
                totalCars = res.meta.total;
                renderCars(res.data);
                updatePagination();
            })
            .catch(() => showToast('Failed to load cars', 'error'));
    }

    function renderCars(cars) {
        const tbody = document.getElementById('carsTable');
        if (cars.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm">No cars found.</td></tr>';
            return;
        }
        let html = '';
        cars.forEach(c => {
            const img = c.image ? (c.image.startsWith('http') ? c.image : '../' + c.image) : 'https://via.placeholder.com/60x60/1e3a8a/06b6d4?text=Car';
            html += '<tr class="border-t border-white/5 hover:bg-white/[0.02] transition-colors">' +
                '<td class="px-6 py-4"><div class="flex items-center gap-3">' +
                    '<img src="' + img + '" class="w-12 h-12 rounded-lg object-cover bg-slate-800" onerror="this.src=\'https://via.placeholder.com/60x60/1e3a8a/06b6d4?text=Car\'">' +
                    '<div><p class="text-white font-medium text-sm">' + c.name + '</p><p class="text-slate-500 text-xs">' + c.model + '</p></div>' +
                '</div></td>' +
                '<td class="px-6 py-4"><span class="badge badge-type">' + c.type + '</span></td>' +
                '<td class="px-6 py-4 text-slate-300">' + c.year + '</td>' +
                '<td class="px-6 py-4 text-white font-medium"><span id="price-val-' + c.id + '">' + Number(c.price_per_day).toLocaleString() + '</span> DH ' +
                    '<button onclick="quickEditPrice(' + c.id + ',' + Number(c.price_per_day) + ')" class="ml-2 text-xs text-cyan-400 hover:text-cyan-300" title="Edit price"><i class="fas fa-pen"></i></button></td>' +
                '<td class="px-6 py-4 text-slate-300">' + c.units_available + '</td>' +
                '<td class="px-6 py-4">' + badge(c.status) + '</td>' +
                '<td class="px-6 py-4 text-right">' +
                    '<button onclick="openEditModal(' + c.id + ')" class="text-slate-400 hover:text-cyan-400 transition-colors mr-3" title="Edit"><i class="fas fa-edit"></i></button>' +
                    '<button onclick="openDeleteModal(' + c.id + ')" class="text-slate-400 hover:text-red-400 transition-colors" title="Delete"><i class="fas fa-trash-alt"></i></button>' +
                '</td></tr>';
        });
        tbody.innerHTML = html;
    }

    function updatePagination() {
        const totalPages = Math.ceil(totalCars / PAGE_LIMIT);
        const showing = Math.min((currentPage + 1) * PAGE_LIMIT, totalCars);
        document.getElementById('paginationInfo').textContent = 'Showing ' + showing + ' of ' + totalCars + ' cars';
        document.getElementById('pageInfo').textContent = 'Page ' + (currentPage + 1);
        document.getElementById('prevBtn').disabled = currentPage === 0;
        document.getElementById('nextBtn').disabled = (currentPage + 1) >= totalPages;
    }

    function prevPage() { if (currentPage > 0) { currentPage--; loadCars(); } }
    function nextPage() { if ((currentPage + 1) * PAGE_LIMIT < totalCars) { currentPage++; loadCars(); } }

    async function quickEditPrice(id, currentPrice) {
        const label = (window.__adminI18n && window.__adminI18n.t) ? window.__adminI18n.t('edit_price') : 'Edit price';
        const val = prompt(label + ' (DH/day):', currentPrice);
        if (val === null) return;
        const price = parseFloat(String(val).replace(',', '.'));
        if (isNaN(price) || price <= 0) { showToast('Invalid price', 'error'); return; }
        try {
            const res = await fetch('../api/cars.php?id=' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ price_per_day: price })
            });
            const json = await res.json();
            if (json.success) {
                showToast((window.__adminI18n && window.__adminI18n.t) ? window.__adminI18n.t('price_updated') : 'Price updated', 'success');
                loadCars();
            } else {
                showToast(json.message || 'Failed to update price', 'error');
            }
        } catch (e) {
            showToast('Request failed', 'error');
        }
    }

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Car';
        document.getElementById('carId').value = '';
        document.getElementById('carForm').reset();
        document.getElementById('carYear').value = new Date().getFullYear();
        carFeatures = [];
        renderFeatures();
        document.getElementById('carModal').classList.add('open');
    }

    function openEditModal(id) {
        fetch('../api/cars.php?id=' + id)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { showToast(res.message, 'error'); return; }
                const c = res.data;
                document.getElementById('modalTitle').textContent = 'Edit Car';
                document.getElementById('carId').value = c.id;
                document.getElementById('carName').value = c.name;
                document.getElementById('carModel').value = c.model;
                document.getElementById('carType').value = c.type;
                document.getElementById('carYear').value = c.year;
                document.getElementById('carPrice').value = c.price_per_day;
                document.getElementById('carTransmission').value = c.transmission;
                document.getElementById('carFuel').value = c.fuel_type;
                document.getElementById('carSeats').value = c.seats;
                document.getElementById('carUnits').value = c.units_available;
                document.getElementById('carStatus').value = c.status;
                document.getElementById('carDescription').value = c.description || '';
                carFeatures = (c.feature_items || []).map(f => ({ name: f.feature_name, icon: f.feature_icon }));
                renderFeatures();
                document.getElementById('carModal').classList.add('open');
            })
            .catch(() => showToast('Failed to load car details', 'error'));
    }

    function closeModal() { document.getElementById('carModal').classList.remove('open'); }

    function addFeature() {
        const input = document.getElementById('featureInput');
        const name = input.value.trim();
        if (!name) return;
        carFeatures.push({ name: name, icon: 'fas fa-check' });
        input.value = '';
        renderFeatures();
    }

    function removeFeature(idx) {
        carFeatures.splice(idx, 1);
        renderFeatures();
    }

    function renderFeatures() {
        const container = document.getElementById('featuresList');
        if (carFeatures.length === 0) { container.innerHTML = ''; return; }
        let html = '';
        carFeatures.forEach((f, i) => {
            html += '<span class="feature-tag">' + f.name + ' <button type="button" onclick="removeFeature(' + i + ')" class="text-red-400 hover:text-red-300 ml-1">&times;</button></span>';
        });
        container.innerHTML = html;
    }

    async function submitCar(e) {
        e.preventDefault();
        const btn = document.getElementById('submitCarBtn');
        const text = document.getElementById('submitCarText');
        const loader = document.getElementById('submitCarLoader');
        btn.disabled = true;
        text.classList.add('hidden');
        loader.classList.remove('hidden');

        let imageUrl = '';
        const fileInput = document.getElementById('carImage');
        if (fileInput.files.length > 0) {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            try {
                const uploadRes = await fetch('../api/upload.php', { method: 'POST', body: formData });
                const uploadData = await uploadRes.json();
                if (uploadData.success) {
                    imageUrl = uploadData.data.url;
                } else {
                    showToast(uploadData.message || 'Image upload failed', 'error');
                    btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
                    return;
                }
            } catch (err) {
                showToast('Image upload failed', 'error');
                btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
                return;
            }
        }

        const carId = document.getElementById('carId').value;
        const data = {
            name: document.getElementById('carName').value,
            model: document.getElementById('carModel').value,
            type: document.getElementById('carType').value,
            year: parseInt(document.getElementById('carYear').value),
            price_per_day: parseFloat(document.getElementById('carPrice').value),
            transmission: document.getElementById('carTransmission').value,
            fuel_type: document.getElementById('carFuel').value,
            seats: parseInt(document.getElementById('carSeats').value),
            units_available: parseInt(document.getElementById('carUnits').value),
            status: document.getElementById('carStatus').value,
            description: document.getElementById('carDescription').value,
            features: carFeatures
        };
        if (imageUrl) data.image = imageUrl;

        const method = carId ? 'PUT' : 'POST';
        const url = carId ? '../api/cars.php?id=' + carId : '../api/cars.php';

        try {
            const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Car saved successfully', 'success');
                closeModal();
                loadCars();
            } else {
                showToast(json.message || 'Failed to save car', 'error');
            }
        } catch (err) {
            showToast('Request failed', 'error');
        }
        btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
    }

    function openDeleteModal(id) { deleteCarId = id; document.getElementById('deleteModal').classList.add('open'); }
    function closeDeleteModal() { deleteCarId = null; document.getElementById('deleteModal').classList.remove('open'); }

    async function confirmDelete() {
        if (!deleteCarId) return;
        const btn = document.getElementById('confirmDeleteBtn');
        const text = document.getElementById('deleteBtnText');
        const loader = document.getElementById('deleteBtnLoader');
        btn.disabled = true; text.classList.add('hidden'); loader.classList.remove('hidden');

        try {
            const res = await fetch('../api/cars.php?id=' + deleteCarId, { method: 'DELETE' });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Car deleted', 'success');
                closeDeleteModal();
                loadCars();
            } else {
                showToast(json.message || 'Delete failed', 'error');
            }
        } catch (err) {
            showToast('Delete request failed', 'error');
        }
        btn.disabled = false; text.classList.remove('hidden'); loader.classList.add('hidden');
    }

    loadCars();
</script>
    <script src="../assets/js/admin-i18n-theme.js"></script>
</body>
</html>



