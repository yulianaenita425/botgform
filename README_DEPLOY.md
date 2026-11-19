# Panduan Penggunaan & Deploy (Local XAMPP & Hosting Domain .my.id)

File skema database MySQL telah siap di:
👉 `D:\botgform\botgform.sql`

---

## 1. Menghubungkan ke Database MySQL / XAMPP (Local)
1. Buka **XAMPP Control Panel** dan klik **Start** pada modul **MySQL** & **Apache**.
2. Buka browser dan masuk ke **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Buat database baru bernama `botgform_db` (atau klik menu **Import**).
4. Pilih file `D:\botgform\botgform.sql` lalu klik **Import**.
5. Seluruh struktur tabel (`settings`, `respondents`, `injection_logs`) beserta 150 data awal dan status terinjeksinya (34 terinjeksi) sudah otomatis masuk.

---

## 2. Deploy ke Hosting / Domain (.my.id)
Jika nantinya ingin dipasang online di domain pribadi:
1. **Import Database di cPanel / Hosting:**
   * Buat database MySQL di cPanel (misal `u123_botgform`).
   * Buka **phpMyAdmin** di cPanel dan import file `botgform.sql`.
2. **Upload File Web:**
   * Di cPanel, Anda bisa menjalankan Node.js via menu **Setup Node.js App** dengan entry point `server.js`.
   * Atau gunakan versi PHP yang telah disediakan di folder `D:\botgform\php_web`.

---

## 3. Fitur yang Tersedia di Web Saat Ini:
* **Simpan Link Google Form:** Anda bisa mengganti URL Google Form kapan saja dan klik tombol **💾 Simpan** agar tersimpan permanen di `settings.json` (tidak akan reset saat refresh).
* **Unggah Data Baru (.xlsx):** Tersedia kolom upload file Excel di bagian atas untuk memuat dataset responden baru langsung dari browser.
* **Pengaturan Jeda Tiap Data (Menit & Detik):** Jeda untuk masing-masing data (misal 5 atau 10 baris) dapat diatur mandiri dengan menit dan detik yang berbeda-beda.
* **Auto-Countdown & Live Log:** Hitung mundur live per detik dan tabel riwayat real-time.
