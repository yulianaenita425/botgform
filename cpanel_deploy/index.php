<?php
session_start();
$isLoggedIn = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Auto-Injector Google Form | botgfrom.manjapos.my.id</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #0f172a;
            --card-bg: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            padding: 20px;
            line-height: 1.5;
        }

        .container {
            max-width: 1220px;
            margin: 0 auto;
        }

        /* Login Screen */
        .login-wrap {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 32px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
        }

        .login-card h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .login-card p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        /* Header */
        header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border);
            padding-bottom: 14px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .header-title h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title p {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 3px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e2e8f0;
            color: #475569;
        }

        .badge-status.active {
            background: #dbeafe;
            color: #1e40af;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .badge-ua {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .stat-card h3 {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-card .val {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-top: 4px;
        }

        .stat-card.primary .val { color: var(--primary); }
        .stat-card.success .val { color: var(--success); }
        .stat-card.warning .val { color: var(--warning); }
        .stat-card.danger .val { color: var(--danger); }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 480px 1fr;
            gap: 20px;
        }

        @media (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 20px;
        }

        .panel h2 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 14px;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .form-group small {
            display: block;
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .input-group-btn {
            display: flex;
            gap: 6px;
        }

        input[type="text"], input[type="number"], input[type="password"], select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-main);
            background: #fff;
            transition: border-color 0.15s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .btn-action-sm {
            padding: 8px 12px;
            background: #0284c7;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.15s;
        }

        .btn-action-sm:hover {
            background: #0369a1;
        }

        .btn-purple {
            background: #7c3aed;
        }
        .btn-purple:hover {
            background: #6d28d9;
        }

        .mode-selector {
            display: flex;
            background: #f1f5f9;
            border-radius: 8px;
            padding: 4px;
            margin-bottom: 12px;
            gap: 4px;
        }

        .mode-tab {
            flex: 1;
            text-align: center;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            color: #475569;
            transition: all 0.15s;
            border: none;
            background: transparent;
        }

        .mode-tab.active {
            background: #ffffff;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .quick-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .quick-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }

        .quick-btn:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }

        .quick-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .upload-box {
            background: #f0fdf4;
            border: 1px dashed #86efac;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .upload-box h4 {
            font-size: 13px;
            color: #166534;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .upload-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .upload-controls input[type="file"] {
            font-size: 12px;
            color: #334155;
            flex: 1;
        }

        .btn-upload {
            background: #16a34a;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-upload:hover {
            background: #15803d;
        }

        .selective-box {
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px;
            background: #fff;
            margin-top: 6px;
        }

        .selective-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 8px;
            font-size: 12px;
            border-bottom: 1px solid #f8fafc;
            border-radius: 4px;
        }

        .selective-item:hover {
            background: #f1f5f9;
        }

        .delays-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            margin-top: 12px;
        }

        .delays-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .delays-header h4 {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
        }

        .auto-generator-bar {
            background: #ffffff;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .auto-gen-inputs {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        .auto-gen-inputs input {
            width: 48px;
            padding: 3px 5px;
            font-size: 11px;
            text-align: center;
        }

        .btn-gen {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .delays-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }

        .delay-row-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }

        .delay-row-item:last-child {
            border-bottom: none;
        }

        .delay-row-info {
            flex: 1;
            padding-right: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .delay-row-inputs {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .input-time-group {
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .input-time-group input {
            width: 44px;
            padding: 3px 5px;
            font-size: 11px;
            text-align: center;
            font-weight: 600;
        }

        .total-time-estimate {
            margin-top: 8px;
            font-size: 11px;
            color: #0f766e;
            background: #ccfbf1;
            padding: 5px 8px;
            border-radius: 6px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }

        .btn {
            flex: 1;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .btn-danger {
            background: #fee2e2;
            color: var(--danger);
            border: 1px solid #fca5a5;
        }

        .btn-danger:hover:not(:disabled) {
            background: #fecaca;
        }

        .btn-retry {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fcd34d;
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 7px 11px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .progress-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .progress-bar-bg {
            height: 10px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin: 8px 0;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: var(--dark);
        }

        .live-log-status {
            font-size: 12px;
            color: #0369a1;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 8px;
            display: none;
            line-height: 1.5;
        }

        .countdown-badge {
            display: inline-block;
            background: #0284c7;
            color: #fff;
            padding: 2px 7px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 700;
            font-size: 12px;
            margin-left: 4px;
        }

        .table-container {
            overflow-x: auto;
            max-height: 480px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 8px 12px;
            position: sticky;
            top: 0;
            border-bottom: 1px solid var(--border);
            z-index: 2;
        }

        td {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .tag-success {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
        }

        .tag-failed {
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
        }

        .device-pill {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 24px;
            color: var(--text-muted);
            font-size: 13px;
        }

        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }

        .modal-card {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-card.large {
            max-width: 900px;
        }

        .mapping-table {
            width: 100%;
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .mapping-table th, .mapping-table td {
            padding: 7px 10px;
            font-size: 11px;
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <!-- MANDATORY LOGIN SCREEN -->
    <div class="login-wrap">
        <div class="login-card">
            <h2>🔒 Login Admin</h2>
            <p>Silakan masuk untuk mengakses panel injeksi Google Form.</p>
            <form onsubmit="handleLoginSubmit(event)">
                <div class="form-group" style="text-align: left;">
                    <label>Username:</label>
                    <input type="text" id="loginUsername" value="admin" required autofocus>
                </div>
                <div class="form-group" style="text-align: left;">
                    <label>Password:</label>
                    <input type="password" id="loginPassword" value="admin123" required>
                </div>
                <div id="loginError" style="color: #dc2626; font-size: 12px; margin-bottom: 12px; display: none;"></div>
                <button type="submit" id="btnLoginSubmit" class="btn btn-primary" style="width: 100%; padding: 12px;">Masuk ke Dashboard</button>
            </form>
        </div>
    </div>
    <script>
        async function handleLoginSubmit(e) {
            e.preventDefault();
            const u = document.getElementById('loginUsername').value.trim();
            const p = document.getElementById('loginPassword').value.trim();
            const btn = document.getElementById('btnLoginSubmit');
            const errDiv = document.getElementById('loginError');

            btn.disabled = true;
            btn.innerText = 'Memverifikasi...';
            errDiv.style.display = 'none';

            try {
                const res = await fetch('api.php?action=login&_t=' + Date.now(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: u, password: p })
                });
                const d = await res.json();
                if (d.success) {
                    location.reload();
                } else {
                    errDiv.innerText = d.error || 'Login gagal';
                    errDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.innerText = 'Masuk ke Dashboard';
                }
            } catch (err) {
                errDiv.innerText = 'Error koneksi: ' + err.message;
                errDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerText = 'Masuk ke Dashboard';
            }
        }
    </script>
<?php else: ?>
    <!-- AUTHENTICATED DASHBOARD -->
    <div class="container">
        <header>
            <div class="header-title">
                <h1>⚡ Google Form Auto-Injector</h1>
                <p>Online Edition &bull; botgfrom.manjapos.my.id &bull; Sinkronisasi Real-Time</p>
            </div>
            <div class="header-actions">
                <button class="btn-action-sm btn-purple" onclick="openMappingModal()" title="Lihat dan sesuaikan pemetaan pertanyaan Google Form">
                    🔗 Pemetaan Kolom
                </button>
                <button class="btn-secondary" onclick="openDataViewer()" title="Buka data viewer untuk melihat seluruh isi responden">
                    📋 Data Viewer
                </button>
                <a href="api.php?action=export_report" class="btn-action-sm" style="text-decoration: none; background: #059669;" title="Download laporan bukti submission">
                    📥 Ekspor Bukti (CSV)
                </a>
                <div class="badge-ua" title="User-Agent & Header perangkat diacak tiap submit">
                    🛡️ Rotasi UA: Aktif
                </div>
                <div id="badgeStatus" class="badge-status">
                    <span id="badgeDot">●</span> <span id="badgeText">Siap</span>
                </div>
                <button class="btn-secondary" onclick="doLogout()" style="color: #b91c1c;" title="Keluar dari akun admin">
                    🚪 Keluar
                </button>
            </div>
        </header>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Data Responden</h3>
                <div class="val" id="statTotal">0</div>
            </div>
            <div class="stat-card success">
                <h3>Sudah Terinjeksi</h3>
                <div class="val" id="statSubmitted">0</div>
            </div>
            <div class="stat-card warning">
                <h3>Sisa Belum Terisi</h3>
                <div class="val" id="statPending">0</div>
            </div>
            <div class="stat-card danger" id="cardFailedBox" style="display: none;">
                <h3>Gagal / Terkendala</h3>
                <div class="val" id="statFailed">0</div>
            </div>
            <div class="stat-card primary">
                <h3>Persentase Selesai</h3>
                <div class="val" id="statPercent">0%</div>
            </div>
        </div>

        <div class="main-grid">
            <!-- Left Panel -->
            <div>
                <!-- Upload Box -->
                <div class="panel" style="padding: 14px 18px; margin-bottom: 14px;">
                    <div class="upload-box">
                        <h4>📁 Unggah Data Baru (.xlsx)</h4>
                        <div class="upload-controls">
                            <input type="file" id="fileUploader" accept=".xlsx">
                            <button type="button" class="btn-upload" onclick="uploadDataFile()">Unggah & Muat</button>
                        </div>
                        <small style="color: #166534; margin-top: 5px; display: block;" id="uploadStatus">Gunakan untuk memuat file Excel responden baru.</small>

                        <!-- Info File Excel Aktif & Terakhir Diubah -->
                        <div style="margin-top: 10px; padding: 10px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 13px; color: #166534; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-size: 15px;">📊</span>
                                <span><strong>File Excel Aktif:</strong> <span id="activeExcelName" style="font-weight: 700; color: #15803d;">Data_Kuesioner_Penelitian_Lengkap.xlsx</span></span>
                            </div>
                            <div style="font-size: 12px; color: #4b5563; display: flex; align-items: center; gap: 4px;">
                                <span>🕒 <strong>Terakhir Diubah:</strong></span>
                                <span id="activeExcelDate" style="font-weight: 600; color: #1f2937;">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Box -->
                <div class="panel">
                    <h2>⚙️ Pengaturan Injeksi</h2>

                    <div class="form-group">
                        <label for="formUrl">Link Google Form Target:</label>
                        <div class="input-group-btn">
                            <input type="text" id="formUrl" value="https://forms.gle/d9Z6dsyNxaLQgYvb8">
                            <button type="button" class="btn-action-sm" onclick="saveTargetUrl()" title="Simpan Link Permanen">
                                💾 Simpan
                            </button>
                            <button type="button" class="btn-action-sm btn-purple" onclick="scanTargetUrl()" title="Pindai form dan petakan kolom secara otomatis">
                                🔍 Scan Form
                            </button>
                        </div>
                        <small id="urlSavedNotice">Mendukung link Google Form apa pun. Klik <strong>Scan Form</strong> untuk mencocokkan otomatis pertanyaan form baru.</small>

                        <!-- Info Link Google Form Aktif & Status -->
                        <div style="margin-top: 10px; padding: 10px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 13px; color: #1e40af; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                            <div style="word-break: break-all; max-width: 100%; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <span style="font-size: 15px;">🔗</span>
                                <span><strong>Link Form Aktif:</strong></span>
                                <a id="activeFormLinkDisplay" href="https://forms.gle/d9Z6dsyNxaLQgYvb8" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 700; word-break: break-all;">https://forms.gle/d9Z6dsyNxaLQgYvb8</a>
                            </div>
                            <div style="font-size: 12px; color: #4b5563; display: flex; align-items: center; gap: 4px;">
                                <span>🕒 <strong>Status:</strong></span>
                                <span id="activeFormDate" style="font-weight: 600; color: #16a34a;">Aktif Tersimpan</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label style="margin: 0;">Metode Pemilihan Data:</label>
                            <button type="button" id="btnRetryFailed" class="btn-retry" style="display: none;" onclick="startRetryFailed()">
                                🔁 Coba Ulang yang Gagal (<span id="failedBadgeCount">0</span>)
                            </button>
                        </div>
                        <div class="mode-selector">
                            <button type="button" class="mode-tab active" id="tabBatch" onclick="switchMode('batch')">Batch Otomatis</button>
                            <button type="button" class="mode-tab" id="tabRange" onclick="switchMode('range')">Rentang Baris</button>
                            <button type="button" class="mode-tab" id="tabSelective" onclick="switchMode('selective')">Pilih Centang</button>
                        </div>

                        <div id="viewModeBatch">
                            <label for="batchSize" style="font-size: 12px;">Jumlah Data yang Disuntik:</label>
                            <input type="number" id="batchSize" min="1" max="500" value="10" oninput="onConfigChange()">
                            <div class="quick-badges">
                                <span class="quick-btn" onclick="setBatch(5)">5 Data</span>
                                <span class="quick-btn active" onclick="setBatch(10)">10 Data</span>
                                <span class="quick-btn" onclick="setBatch(25)">25 Data</span>
                                <span class="quick-btn" onclick="setBatch(50)">50 Data</span>
                                <span class="quick-btn" onclick="setBatch(0)">Semua Sisa</span>
                            </div>
                        </div>

                        <div id="viewModeRange" style="display: none;">
                            <label style="font-size: 12px;">Rentang Nomor Baris Excel:</label>
                            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center;">
                                <input type="number" id="rangeStart" placeholder="Dari Baris #" min="2" max="500" value="6" oninput="onConfigChange()">
                                <span style="font-weight: 600; color: #64748b;">s/d</span>
                                <input type="number" id="rangeEnd" placeholder="Sampai Baris #" min="2" max="500" value="15" oninput="onConfigChange()">
                            </div>
                            <small>Berdasarkan nomor baris di Excel (misal baris #6 sampai #15).</small>
                        </div>

                        <div id="viewModeSelective" style="display: none;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label style="font-size: 12px; margin: 0;">Centang Responden yang Mau Disuntik:</label>
                                <span style="font-size: 11px; color: var(--primary); cursor: pointer;" onclick="toggleSelectAll()">Pilih Semua</span>
                            </div>
                            <div class="selective-box" id="selectiveList">
                            </div>
                        </div>
                    </div>

                    <!-- Per-Data Delays Box -->
                    <div class="delays-box">
                        <div class="delays-header">
                            <h4>⏱️ Pengaturan Jeda Tiap Data (Menit & Detik)</h4>
                        </div>

                        <div class="auto-generator-bar">
                            <div><strong>⚡ Acak Cepat Semua Baris:</strong></div>
                            <div class="auto-gen-inputs">
                                <span>Min:</span>
                                <input type="number" id="genMinM" value="3" min="0" max="60" title="Menit Minimal"> m
                                <input type="number" id="genMinS" value="10" min="0" max="59" title="Detik Minimal"> s
                                <span style="margin-left: 4px;">s/d Max:</span>
                                <input type="number" id="genMaxM" value="5" min="0" max="60" title="Menit Maksimal"> m
                                <input type="number" id="genMaxS" value="30" min="0" max="59" title="Detik Maksimal"> s
                                <button type="button" class="btn-gen" onclick="generateRandomDelays()">🎲 Terapkan Acak</button>
                            </div>
                        </div>

                        <div class="delays-list" id="delaysList">
                        </div>

                        <div class="total-time-estimate">
                            <span>Total Perkiraan Waktu Batch:</span>
                            <span id="totalTimeEstimate">0 menit 0 detik</span>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button id="btnStart" class="btn btn-primary" onclick="startInjectionLoop()">
                            <span>▶</span> Mulai Inject Data
                        </button>
                        <button id="btnStop" class="btn btn-danger" onclick="stopInjectionLoop()" disabled>
                            <span>⏹</span> Berhenti
                        </button>
                    </div>

                    <div style="margin-top: 14px; text-align: center;">
                        <button class="btn-secondary" onclick="resetProgress()">⚠️ Reset Riwayat Progress</button>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Live Progress & History -->
            <div class="panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h2 style="margin: 0; border: none; padding: 0;">📊 Baris Progres & Log Injeksi</h2>
                    <span id="logCounter" style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Memuat...</span>
                </div>

                <div class="progress-box">
                    <div class="progress-text">
                        <span id="progressLabel">Progres Batch: Siap</span>
                        <span id="progressNumber">0 / 0</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div id="progressBar" class="progress-bar-fill"></div>
                    </div>
                    <div id="liveStatus" class="live-log-status">
                        Sedang menunggu perintah...
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th style="width: 75px;">Baris</th>
                                <th>Nama Usaha & Perangkat</th>
                                <th style="width: 120px;">Waktu</th>
                                <th style="width: 70px;">Durasi</th>
                                <th style="width: 75px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="6" class="empty-state">Belum ada data injeksi pada riwayat.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Viewer Modal -->
    <div id="dataViewerModal" class="modal-overlay">
        <div class="modal-card large">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px;">📋 Pratinjau Seluruh Data Responden (Data Viewer)</h3>
                <button type="button" class="btn-secondary" onclick="closeDataViewer()">Tutup ✕</button>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                <input type="text" id="viewerSearch" placeholder="Cari berdasarkan nama usaha..." oninput="filterViewerData()" style="flex: 1;">
                <select id="viewerFilterStatus" onchange="filterViewerData()" style="width: 170px;">
                    <option value="ALL">Semua Status</option>
                    <option value="PENDING">Belum Terisi (Pending)</option>
                    <option value="SUCCESS">Sudah Terisi (Sukses)</option>
                    <option value="FAILED">Gagal</option>
                </select>
            </div>
            <div class="table-container" style="max-height: 480px;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 45px;">Index</th>
                            <th style="width: 75px;">Baris Excel</th>
                            <th>Nama Usaha</th>
                            <th style="width: 90px;">Status</th>
                            <th style="width: 110px;">Waktu Terakhir</th>
                            <th style="width: 85px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="viewerTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mapping Modal -->
    <div id="mappingModal" class="modal-overlay">
        <div class="modal-card large">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h3 style="font-size: 16px;">🔗 Pemetaan Kolom Google Form $\leftrightarrow$ Excel</h3>
                <button type="button" class="btn-secondary" onclick="closeMappingModal()">Tutup ✕</button>
            </div>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                Sesuaikan pertanyaan di Google Form dengan kolom data Excel yang sesuai.
            </p>
            <div class="table-container" style="max-height: 440px;">
                <table class="mapping-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Pertanyaan Google Form</th>
                            <th style="width: 15%;">Tipe Field</th>
                            <th style="width: 40%;">Kolom Data di Excel</th>
                        </tr>
                    </thead>
                    <tbody id="mappingTableBody">
                    </tbody>
                </table>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px;">
                <button type="button" class="btn-secondary" onclick="closeMappingModal()">Batal</button>
                <button type="button" class="btn-action-sm btn-purple" style="padding: 9px 16px;" onclick="saveMappingFromUI()">💾 Simpan Pemetaan Kolom</button>
            </div>
        </div>
    </div>

    <script>
        let isLocalRunning = false;
        let stopRequested = false;
        let allRowsData = [];
        let excelHeadersGlobal = [];
        let currentMappingObj = null;
        let countdownTimer = null;
        let currentMode = 'batch';

        function switchMode(mode) {
            currentMode = mode;
            document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
            document.getElementById('viewModeBatch').style.display = 'none';
            document.getElementById('viewModeRange').style.display = 'none';
            document.getElementById('viewModeSelective').style.display = 'none';

            if (mode === 'batch') {
                document.getElementById('tabBatch').classList.add('active');
                document.getElementById('viewModeBatch').style.display = 'block';
            } else if (mode === 'range') {
                document.getElementById('tabRange').classList.add('active');
                document.getElementById('viewModeRange').style.display = 'block';
            } else if (mode === 'selective') {
                document.getElementById('tabSelective').classList.add('active');
                document.getElementById('viewModeSelective').style.display = 'block';
            }
            onConfigChange();
        }

        function setBatch(num) {
            document.getElementById('batchSize').value = num;
            document.querySelectorAll('.quick-btn').forEach(btn => btn.classList.remove('active'));
            if (event && event.target) {
                event.target.classList.add('active');
            }
            onConfigChange();
        }

        function onConfigChange() {
            const targets = getTargetRows();
            renderDelayRows(targets);
        }

        function getTargetRows() {
            if (currentMode === 'batch') {
                let count = parseInt(document.getElementById('batchSize').value, 10);
                const pending = allRowsData.filter(r => !r.isSubmitted);
                if (isNaN(count) || count <= 0) count = pending.length;
                return pending.slice(0, count);
            } else if (currentMode === 'range') {
                const start = parseInt(document.getElementById('rangeStart').value, 10) || 2;
                const end = parseInt(document.getElementById('rangeEnd').value, 10) || 500;
                return allRowsData.filter(r => r.rowNum >= start && r.rowNum <= end);
            } else if (currentMode === 'selective') {
                const checkboxes = document.querySelectorAll('.sel-row-cb:checked');
                const selectedIdx = Array.from(checkboxes).map(cb => parseInt(cb.value, 10));
                return allRowsData.filter(r => selectedIdx.includes(r.index));
            }
            return [];
        }

        function renderDelayRows(targets) {
            const container = document.getElementById('delaysList');
            container.innerHTML = '';
            const itemsCount = targets.length;

            for (let i = 0; i < itemsCount - 1; i++) {
                const nextTarget = targets[i + 1];
                const upcomingNama = nextTarget ? nextTarget.namaUsaha : `Data #${i + 2}`;
                
                const defMin = 3 + Math.floor(Math.random() * 2);
                const defSec = Math.floor(Math.random() * 59);

                const rowDiv = document.createElement('div');
                rowDiv.className = 'delay-row-item';
                rowDiv.innerHTML = `
                    <div class="delay-row-info" title="Jeda setelah #${targets[i].rowNum} sebelum #${nextTarget ? nextTarget.rowNum : ''} ${upcomingNama}">
                        <strong>Jeda ${i+1} ➔ ${i+2}:</strong> <span style="color:#64748b;">${upcomingNama}</span>
                    </div>
                    <div class="delay-row-inputs">
                        <div class="input-time-group">
                            <input type="number" class="delay-m" id="delay_m_${i}" value="${defMin}" min="0" max="120" onchange="calcTotalEstimate()">
                            <span>m</span>
                        </div>
                        <div class="input-time-group">
                            <input type="number" class="delay-s" id="delay_s_${i}" value="${defSec}" min="0" max="59" onchange="calcTotalEstimate()">
                            <span>s</span>
                        </div>
                    </div>
                `;
                container.appendChild(rowDiv);
            }

            if (itemsCount <= 1) {
                container.innerHTML = `<div style="padding:12px; text-align:center; color:#64748b; font-size:12px;">${itemsCount === 1 ? 'Hanya 1 data (tidak memerlukan jeda).' : 'Pilih data terlebih dahulu.'}</div>`;
            }

            calcTotalEstimate();
        }

        function renderSelectiveList(rows) {
            const container = document.getElementById('selectiveList');
            if (!rows || rows.length === 0) {
                container.innerHTML = '<div style="padding:10px; color:#64748b; font-size:12px;">Memuat data...</div>';
                return;
            }

            container.innerHTML = rows.map(r => {
                const statusTag = r.isSubmitted ? '<span style="color:#16a34a; font-weight:600; font-size:10px; margin-left:auto;">[Terisi]</span>' : '';
                const checked = !r.isSubmitted && r.index < 10 ? 'checked' : '';
                return `
                    <label class="selective-item">
                        <input type="checkbox" class="sel-row-cb" value="${r.index}" ${checked} onchange="onConfigChange()">
                        <span>#${r.rowNum} &bull; <strong>${r.namaUsaha}</strong></span>
                        ${statusTag}
                    </label>
                `;
            }).join('');
        }

        let selectAllState = false;
        function toggleSelectAll() {
            selectAllState = !selectAllState;
            document.querySelectorAll('.sel-row-cb').forEach(cb => cb.checked = selectAllState);
            onConfigChange();
        }

        async function saveTargetUrl() {
            const formUrl = document.getElementById('formUrl').value.trim();
            if (!formUrl) {
                alert('Silakan masukkan link Google Form!');
                return;
            }
            try {
                const res = await fetch('api.php?action=settings&_t=' + Date.now(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ formUrl })
                });
                const data = await res.json();
                if (data.success) {
                    const notice = document.getElementById('urlSavedNotice');
                    notice.innerHTML = '<span style="color: #16a34a; font-weight: 600;">✅ Link Google Form berhasil disimpan secara permanen!</span>';
                    
                    const linkEl = document.getElementById('activeFormLinkDisplay');
                    if (linkEl) {
                        linkEl.textContent = formUrl;
                        linkEl.href = formUrl;
                    }
                    const dateEl = document.getElementById('activeFormDate');
                    if (dateEl) {
                        dateEl.textContent = 'Baru Disimpan (' + (data.formLastUpdated || new Date().toLocaleTimeString('id-ID')) + ')';
                    }

                    setTimeout(() => {
                        notice.innerHTML = 'Mendukung link Google Form apa pun. Klik <strong>Scan Form</strong> untuk mencocokkan otomatis.';
                    }, 4000);
                }
            } catch (err) {
                alert('Gagal menyimpan link: ' + err.message);
            }
        }

        async function scanTargetUrl() {
            const formUrl = document.getElementById('formUrl').value.trim();
            if (!formUrl) {
                alert('Masukkan link Google Form terlebih dahulu!');
                return;
            }
            const notice = document.getElementById('urlSavedNotice');
            notice.innerHTML = '⏳ <strong>Sedang memindai struktur pertanyaan Google Form...</strong>';

            try {
                const res = await fetch('api.php?action=scan&_t=' + Date.now(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ formUrl })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    notice.innerHTML = `<span style="color: #7c3aed; font-weight: 600;">✨ Form terdeteksi: "${data.title}" (${data.totalQuestions} pertanyaan). Pemetaan kolom dibuka.</span>`;
                    openMappingModal();
                } else {
                    notice.innerHTML = `<span style="color: #dc2626;">❌ Error scan: ${data.error}</span>`;
                }
            } catch (e) {
                notice.innerHTML = `<span style="color: #dc2626;">❌ Gagal memindai: ${e.message}</span>`;
            }
        }

        async function openMappingModal() {
            document.getElementById('mappingModal').style.display = 'flex';
            const tbody = document.getElementById('mappingTableBody');
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px;">Memuat pemetaan kolom...</td></tr>';

            try {
                const res = await fetch('current_mapping.json?_t=' + Date.now());
                const data = await res.json();
                currentMappingObj = data;

                if (!data.mapping || Object.keys(data.mapping).length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px; color:#64748b;">Belum ada pemetaan form. Silakan klik tombol "Scan Form" terlebih dahulu.</td></tr>';
                    return;
                }

                tbody.innerHTML = Object.entries(data.mapping).map(([id, q]) => {
                    const typeLabel = q.type === 9 ? 'Date (Tanggal)' : q.type === 4 ? 'Checkboxes' : q.type === 2 ? 'Pilihan Ganda' : 'Teks/Lainnya';
                    return `
                        <tr>
                            <td><strong>${q.questionTitle || `Entry #${id}`}</strong></td>
                            <td><span style="color:#64748b; font-size:11px;">${typeLabel}</span></td>
                            <td>
                                <select class="map-select" data-entry-id="${id}">
                                    <option value="">-- Abaikan (Kosongkan) --</option>
                                    ${excelHeadersGlobal.map(h => `<option value="${h}" ${h === q.excelColumn ? 'selected' : ''}>${h}</option>`).join('')}
                                </select>
                            </td>
                        </tr>
                    `;
                }).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="3" style="color:red; padding:20px;">Error: ${e.message}</td></tr>`;
            }
        }

        function closeMappingModal() {
            document.getElementById('mappingModal').style.display = 'none';
        }

        async function saveMappingFromUI() {
            if (!currentMappingObj) return;
            const selects = document.querySelectorAll('.map-select');
            selects.forEach(sel => {
                const entryId = sel.dataset.entryId;
                if (currentMappingObj.mapping[entryId]) {
                    currentMappingObj.mapping[entryId].excelColumn = sel.value;
                }
            });

            try {
                const res = await fetch('api.php?action=save_mapping&_t=' + Date.now(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(currentMappingObj)
                });
                const d = await res.json();
                if (d.success) {
                    alert('Pemetaan kolom formulir berhasil disimpan!');
                    closeMappingModal();
                } else {
                    alert(d.error);
                }
            } catch (e) {
                alert('Gagal menyimpan: ' + e.message);
            }
        }

        function openDataViewer() {
            document.getElementById('dataViewerModal').style.display = 'flex';
            filterViewerData();
        }

        function closeDataViewer() {
            document.getElementById('dataViewerModal').style.display = 'none';
        }

        function filterViewerData() {
            const query = (document.getElementById('viewerSearch').value || '').toLowerCase();
            const statusFilter = document.getElementById('viewerFilterStatus').value;
            const tbody = document.getElementById('viewerTableBody');

            const filtered = allRowsData.filter(r => {
                const matchName = r.namaUsaha.toLowerCase().includes(query) || String(r.rowNum).includes(query);
                const matchStatus = statusFilter === 'ALL' || r.status === statusFilter;
                return matchName && matchStatus;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:24px; color:#64748b;">Tidak ada data yang cocok dengan pencarian.</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(r => {
                const tag = r.status === 'SUCCESS' ? '<span class="tag-success">Sukses</span>' : r.status === 'FAILED' ? '<span class="tag-failed">Gagal</span>' : '<span style="color:#64748b; font-weight:600; font-size:11px;">Pending</span>';
                const d = r.timestamp ? new Date(r.timestamp).toLocaleTimeString('id-ID') : '-';
                return `
                    <tr>
                        <td>${r.index + 1}</td>
                        <td><strong>#${r.rowNum}</strong></td>
                        <td>${r.namaUsaha}</td>
                        <td>${tag}</td>
                        <td>${d}</td>
                        <td>
                            <button type="button" class="btn-secondary" style="padding:4px 8px; font-size:11px;" onclick="injectSingleFromViewer(${r.index})">Suntik Ini</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function injectSingleFromViewer(idx) {
            closeDataViewer();
            switchMode('selective');
            document.querySelectorAll('.sel-row-cb').forEach(cb => cb.checked = (parseInt(cb.value, 10) === idx));
            onConfigChange();
            startInjectionLoop();
        }

        function startRetryFailed() {
            const failedTargets = allRowsData.filter(r => r.status === 'FAILED');
            if (failedTargets.length === 0) {
                alert('Tidak ada data gagal yang tercatat.');
                return;
            }
            if (confirm(`Ulangi injeksi untuk ${failedTargets.length} baris yang gagal?`)) {
                switchMode('selective');
                document.querySelectorAll('.sel-row-cb').forEach(cb => {
                    const idx = parseInt(cb.value, 10);
                    cb.checked = failedTargets.some(f => f.index === idx);
                });
                onConfigChange();
                startInjectionLoop();
            }
        }

        async function uploadDataFile() {
            const fileInput = document.getElementById('fileUploader');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Silakan pilih file Excel (.xlsx) terlebih dahulu!');
                return;
            }

            const file = fileInput.files[0];
            const statusLabel = document.getElementById('uploadStatus');
            statusLabel.innerHTML = '⏳ Sedang mengunggah & mengekstrak data... Mohon tunggu.';

            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch('api.php?action=upload&_t=' + Date.now(), {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();

                if (res.ok && result.success) {
                    statusLabel.innerHTML = `<span style="color: #16a34a; font-weight: 600;">✅ ${result.message}</span>`;
                    await fetchStatus();
                    alert(result.message);
                } else {
                    statusLabel.innerHTML = `<span style="color: #dc2626;">❌ Error: ${result.error}</span>`;
                }
            } catch (err) {
                statusLabel.innerHTML = `<span style="color: #dc2626;">❌ Gagal upload: ${err.message}</span>`;
            }
        }

        function generateRandomDelays() {
            const minM = parseInt(document.getElementById('genMinM').value, 10) || 0;
            const minS = parseInt(document.getElementById('genMinS').value, 10) || 0;
            const maxM = parseInt(document.getElementById('genMaxM').value, 10) || 0;
            const maxS = parseInt(document.getElementById('genMaxS').value, 10) || 0;

            const minTotal = (minM * 60) + minS;
            const maxTotal = Math.max(minTotal, (maxM * 60) + maxS);

            const mInputs = document.querySelectorAll('.delay-m');
            const sInputs = document.querySelectorAll('.delay-s');

            for (let i = 0; i < mInputs.length; i++) {
                const randSec = Math.floor(Math.random() * (maxTotal - minTotal + 1)) + minTotal;
                const m = Math.floor(randSec / 60);
                const s = randSec % 60;
                mInputs[i].value = m;
                sInputs[i].value = s;
            }

            calcTotalEstimate();
        }

        function calcTotalEstimate() {
            const mInputs = document.querySelectorAll('.delay-m');
            const sInputs = document.querySelectorAll('.delay-s');

            let totalSec = 0;
            for (let i = 0; i < mInputs.length; i++) {
                const m = parseInt(mInputs[i].value, 10) || 0;
                const s = parseInt(sInputs[i].value, 10) || 0;
                totalSec += (m * 60) + s;
            }

            const totalM = Math.floor(totalSec / 60);
            const remainS = totalSec % 60;
            document.getElementById('totalTimeEstimate').innerText = `${totalM} menit ${remainS} detik`;
        }

        function getCustomDelays() {
            const mInputs = document.querySelectorAll('.delay-m');
            const sInputs = document.querySelectorAll('.delay-s');
            const delays = [];

            for (let i = 0; i < mInputs.length; i++) {
                const m = parseInt(mInputs[i].value, 10) || 0;
                const s = parseInt(sInputs[i].value, 10) || 0;
                delays.push((m * 60) + s);
            }
            return delays;
        }

        // Live Status & Cross-Device Sync Heartbeat
        async function fetchStatus() {
            try {
                const res = await fetch('api.php?action=status&_t=' + Date.now());
                if (res.status === 401) {
                    location.reload();
                    return;
                }
                const data = await res.json();
                allRowsData = data.allRows || [];
                excelHeadersGlobal = data.excelHeaders || [];

                if (data.settings && data.settings.formUrl) {
                    const urlInput = document.getElementById('formUrl');
                    if (!urlInput.dataset.modified) {
                        urlInput.value = data.settings.formUrl;
                    }
                }

                // Update Active Excel & Form Info Badges
                if (data.activeFileName) {
                    const el = document.getElementById('activeExcelName');
                    if (el) el.textContent = data.activeFileName;
                }
                if (data.fileLastModified) {
                    const el = document.getElementById('activeExcelDate');
                    if (el) el.textContent = data.fileLastModified;
                }
                if (data.activeFormUrl) {
                    const linkEl = document.getElementById('activeFormLinkDisplay');
                    if (linkEl) {
                        linkEl.textContent = data.activeFormUrl;
                        linkEl.href = data.activeFormUrl;
                    }
                }
                if (data.formLastUpdated) {
                    const dateEl = document.getElementById('activeFormDate');
                    if (dateEl) {
                        dateEl.textContent = data.formLastUpdated.startsWith('Disimpan') ? data.formLastUpdated : 'Disimpan: ' + data.formLastUpdated;
                    }
                }

                updateStats(data);
                renderTable(data.history);
                renderSelectiveList(allRowsData);

                if (document.getElementById('delaysList').children.length === 0) {
                    onConfigChange();
                }

                // Synchronize Live Status from other devices (e.g. if Laptop is running, HP displays it!)
                syncRemoteJobState(data.jobState);

            } catch (e) {
                console.error('Error fetching status:', e);
            }
        }

        // Cross-device sync handler
        function syncRemoteJobState(jobState) {
            if (!jobState) return;

            // If THIS device is running the loop, do not overwrite own controls
            if (isLocalRunning) return;

            const liveStatus = document.getElementById('liveStatus');
            const pBar = document.getElementById('progressBar');
            const pLabel = document.getElementById('progressLabel');
            const pNum = document.getElementById('progressNumber');
            const btnStart = document.getElementById('btnStart');
            const btnStop = document.getElementById('btnStop');
            const badge = document.getElementById('badgeStatus');
            const badgeText = document.getElementById('badgeText');

            if (jobState.isRunning) {
                btnStart.disabled = true;
                btnStop.disabled = false;
                badge.className = 'badge-status active';
                badgeText.innerText = 'Menyuntik (Perangkat Lain)...';
                liveStatus.style.display = 'block';

                pLabel.innerText = `Menyuntik ${jobState.total || ''} data (Live Sync)...`;
                pNum.innerText = `${jobState.current || 0} / ${jobState.total || 0}`;
                const pct = jobState.total > 0 ? Math.round(((jobState.current || 0) / jobState.total) * 100) : 0;
                pBar.style.width = pct + '%';

                if (jobState.statusText) {
                    liveStatus.innerHTML = jobState.statusText;
                }
            } else {
                if (!isLocalRunning) {
                    btnStart.disabled = false;
                    btnStop.disabled = true;
                    badge.className = 'badge-status';
                    badgeText.innerText = 'Siap';
                }
            }
        }

        document.getElementById('formUrl').addEventListener('input', function() {
            this.dataset.modified = 'true';
        });

        function updateStats(data) {
            document.getElementById('statTotal').innerText = data.total;
            document.getElementById('statSubmitted').innerText = data.submitted;
            document.getElementById('statPending').innerText = data.pending;
            const pct = data.total > 0 ? Math.round((data.submitted / data.total) * 100) : 0;
            document.getElementById('statPercent').innerText = pct + '%';
            document.getElementById('logCounter').innerText = `${data.submitted} dari ${data.total} data selesai`;

            const cardFail = document.getElementById('cardFailedBox');
            const btnRetry = document.getElementById('btnRetryFailed');
            if (data.failedCount && data.failedCount > 0) {
                cardFail.style.display = 'block';
                document.getElementById('statFailed').innerText = data.failedCount;
                btnRetry.style.display = 'inline-flex';
                document.getElementById('failedBadgeCount').innerText = data.failedCount;
            } else {
                cardFail.style.display = 'none';
                btnRetry.style.display = 'none';
            }
        }

        function renderTable(history) {
            const tbody = document.getElementById('tableBody');
            if (!history || history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Belum ada riwayat data terinjeksi.</td></tr>';
                return;
            }

            tbody.innerHTML = history.map((item, idx) => {
                const date = item.timestamp ? new Date(item.timestamp).toLocaleTimeString('id-ID') : '-';
                const tag = item.status === 'SUCCESS' ? '<span class="tag-success">Sukses</span>' : '<span class="tag-failed">Gagal</span>';
                const device = item.device ? `<span class="device-pill">${item.device}</span>` : '';
                return `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><strong>#${item.rowNum}</strong></td>
                        <td>${item.namaUsaha} ${device}</td>
                        <td>${date}</td>
                        <td>${item.duration || '-'}</td>
                        <td>${tag}</td>
                    </tr>
                `;
            }).join('');
        }

        function setLocalRunningUI(running) {
            isLocalRunning = running;
            const btnStart = document.getElementById('btnStart');
            const btnStop = document.getElementById('btnStop');
            const badge = document.getElementById('badgeStatus');
            const badgeText = document.getElementById('badgeText');
            const liveStatus = document.getElementById('liveStatus');

            if (running) {
                btnStart.disabled = true;
                btnStop.disabled = false;
                badge.className = 'badge-status active';
                badgeText.innerText = 'Sedang Menyuntik...';
                liveStatus.style.display = 'block';
            } else {
                btnStart.disabled = false;
                btnStop.disabled = true;
                badge.className = 'badge-status';
                badgeText.innerText = 'Siap';
                if (countdownTimer) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                }
            }
        }

        async function broadcastJobState(stateObj) {
            try {
                await fetch('api.php?action=update_job_state&_t=' + Date.now(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(stateObj)
                });
            } catch (e) {}
        }

        // Client-Orchestrated Injection Loop with Real-Time Server Broadcast
        async function startInjectionLoop() {
            const targets = getTargetRows();
            if (targets.length === 0) {
                alert('Pilih minimal 1 data responden untuk disuntikkan!');
                return;
            }

            const customDelays = getCustomDelays();
            setLocalRunningUI(true);
            stopRequested = false;

            const pBar = document.getElementById('progressBar');
            const pLabel = document.getElementById('progressLabel');
            const pNum = document.getElementById('progressNumber');
            const liveStatus = document.getElementById('liveStatus');

            pLabel.innerText = `Memproses batch ${targets.length} data...`;
            pNum.innerText = `0 / ${targets.length}`;
            pBar.style.width = '0%';

            await broadcastJobState({
                isRunning: true,
                current: 0,
                total: targets.length,
                statusText: `🚀 Memulai batch ${targets.length} data...`
            });

            let successCount = 0;
            let failCount = 0;

            for (let i = 0; i < targets.length; i++) {
                if (stopRequested) {
                    liveStatus.innerHTML = '🛑 Injeksi dihentikan oleh pengguna.';
                    await broadcastJobState({ isRunning: false });
                    break;
                }

                const target = targets[i];
                pNum.innerText = `${i} / ${targets.length}`;
                pBar.style.width = `${Math.round((i / targets.length) * 100)}%`;
                const statusTxt = `🚀 [${i + 1}/${targets.length}] Menyuntik: <strong>"${target.namaUsaha}"</strong> (Baris #${target.rowNum})...`;
                liveStatus.innerHTML = statusTxt;

                await broadcastJobState({
                    isRunning: true,
                    current: i + 1,
                    total: targets.length,
                    statusText: statusTxt
                });

                try {
                    const res = await fetch('api.php?action=submit_item&_t=' + Date.now(), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ index: target.index })
                    });
                    const d = await res.json();

                    if (d.success) {
                        successCount++;
                        const succTxt = `✅ Berhasil menginjeksi <strong>"${target.namaUsaha}"</strong> [${d.item.device}] (${d.item.duration})`;
                        liveStatus.innerHTML = succTxt;
                        await broadcastJobState({
                            isRunning: true,
                            current: i + 1,
                            total: targets.length,
                            statusText: succTxt
                        });
                    } else {
                        failCount++;
                        liveStatus.innerHTML = `❌ Gagal menginjeksi baris #${target.rowNum}: ${d.error || 'Respons tidak valid'}`;
                    }
                    await fetchStatus();
                } catch (e) {
                    failCount++;
                    liveStatus.innerHTML = `❌ Error jaringan: ${e.message}`;
                }

                // Delay between items
                if (i < targets.length - 1 && !stopRequested) {
                    let delaySec = customDelays[i] !== undefined ? customDelays[i] : 180;
                    if (delaySec <= 0) delaySec = 3;

                    const nextTarget = targets[i + 1];
                    const nextNama = nextTarget ? nextTarget.namaUsaha : `Data #${i + 2}`;

                    await runClientCountdown(delaySec, i + 2, targets.length, nextNama);
                }
            }

            pBar.style.width = '100%';
            pNum.innerText = `${targets.length} / ${targets.length}`;
            pLabel.innerText = stopRequested ? 'Proses dihentikan.' : 'Batch selesai!';
            liveStatus.innerHTML = `🏁 <strong>Selesai:</strong> ${successCount} berhasil, ${failCount} gagal.`;

            await broadcastJobState({ isRunning: false });
            setLocalRunningUI(false);
            await fetchStatus();
        }

        function stopInjectionLoop() {
            stopRequested = true;
            document.getElementById('liveStatus').innerHTML = '⚠️ Menghentikan proses...';
            broadcastJobState({ isRunning: false });
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
        }

        function runClientCountdown(totalSeconds, nextIndex, totalBatch, nextNama) {
            return new Promise(resolve => {
                let remaining = totalSeconds;
                const liveStatus = document.getElementById('liveStatus');

                async function tick() {
                    if (stopRequested || remaining <= 0) {
                        clearInterval(countdownTimer);
                        countdownTimer = null;
                        resolve();
                        return;
                    }

                    const m = Math.floor(remaining / 60);
                    const s = remaining % 60;
                    const formattedTime = `${m > 0 ? m + ' menit ' : ''}${s} detik`;
                    const padM = String(m).padStart(2, '0');
                    const padS = String(s).padStart(2, '0');

                    const delayTxt = `
                        <div>⏳ <strong>Jeda antar data:</strong> Menunggu <strong>${formattedTime}</strong> sebelum data ke-${nextIndex} (${nextNama}).</div>
                        <div style="margin-top:4px;">Hitung mundur: <span class="countdown-badge">${padM}:${padS}</span></div>
                    `;
                    liveStatus.innerHTML = delayTxt;

                    if (remaining % 3 === 0) {
                        broadcastJobState({
                            isRunning: true,
                            current: nextIndex - 1,
                            total: totalBatch,
                            statusText: delayTxt
                        });
                    }

                    remaining--;
                }

                tick();
                countdownTimer = setInterval(tick, 1000);
            });
        }

        async function resetProgress() {
            if (confirm('Yakin ingin mereset progress? Catatan data yang sudah di-submit akan dihapus dan dimulai dari awal.')) {
                await fetch('api.php?action=reset&_t=' + Date.now(), { method: 'POST' });
                await fetchStatus();
                document.getElementById('progressBar').style.width = '0%';
                document.getElementById('progressLabel').innerText = 'Progress telah direset.';
            }
        }

        async function doLogout() {
            if (confirm('Keluar dari panel admin?')) {
                await fetch('api.php?action=logout&_t=' + Date.now(), { method: 'POST' });
                location.reload();
            }
        }

        // Heartbeat sync every 3 seconds to keep all devices (Laptop & HP) in 100% sync
        fetchStatus();
        setInterval(fetchStatus, 3000);
    </script>
    </div>
<?php endif; ?>
</body>
</html>
