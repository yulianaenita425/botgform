# Google Form Auto-Injector

Sistem otomatisasi injeksi data kuesioner dari file Excel (`.xlsx`) ke Google Form dengan fitur anti-deteksi multi-perangkat (rotasi User-Agent), pemetaan kolom universal (Auto-Mapper), penjadwalan jeda dinamis (menit & detik), seleksi baris (Selective / Range), ekspor rekap CSV, serta sinkronisasi multi-device dengan proteksi login admin.

## Fitur Utama

- **Anti-Deteksi Multi-Perangkat:** Rotasi header browser dan User-Agent realistis (Chrome Windows, Edge, macOS Safari, Android Samsung S24, iPhone 15 Pro) dengan jeda acak per data.
- **Universal Form Auto-Mapper:** Pindai (*scan*) struktur link Google Form baru secara otomatis dan lakukan visual column mapping (*Excel column -> Form question*).
- **Fleksibilitas Data:** Unggah dataset Excel baru langsung dari browser, preview data responden, pilih metode injeksi (Batch Otomatis, Rentang Baris, atau Centang Selektif).
- **Sinkronisasi Antar-Perangkat (Live Sync):** Tampilan progres dan hitungan mundur berjalan sinkron secara real-time antara laptop dan smartphone via *Central Job State*.
- **Akses Aman:** Dilengkapi gerbang autentikasi login admin berbasis PHP Session.
- **Kesiapan cPanel / Shared Hosting:** Berjalan mandiri di PHP & Apache tanpa timeout server, serta dilengkapi file `.cpanel.yml` untuk deployment otomatis dari GitHub.

## Deployment cPanel
Repository ini dilengkapi konfigurasi `.cpanel.yml` yang otomatis menyalin file dari direktori `cpanel_deploy/` ke `/home/manjapos/botgfrom.manjapos.my.id`.
