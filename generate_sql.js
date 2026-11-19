const fs = require('fs');
const path = require('path');

const excelRows = JSON.parse(fs.readFileSync('d:/botgform/excel_data.json', 'utf8'));
const progress = JSON.parse(fs.readFileSync('d:/botgform/progress.json', 'utf8'));
const submittedSet = new Set(progress.submittedIndices);

const historyMap = new Map();
if (progress.history) {
    progress.history.forEach(h => {
        historyMap.set(h.index, h);
    });
}

let sql = `-- ========================================================
-- Database Schema & Data untuk Auto-Injector Google Form
-- Target: XAMPP MySQL / MariaDB / cPanel domain.my.id
-- Tanggal: 18 November 2026
-- ========================================================

CREATE DATABASE IF NOT EXISTS \`botgform_db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE \`botgform_db\`;

-- 1. Tabel Pengaturan Sistem
DROP TABLE IF EXISTS \`settings\`;
CREATE TABLE \`settings\` (
    \`id\` INT AUTO_INCREMENT PRIMARY KEY,
    \`setting_key\` VARCHAR(100) UNIQUE NOT NULL,
    \`setting_value\` TEXT,
    \`updated_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO \`settings\` (\`setting_key\`, \`setting_value\`) VALUES
('form_url', 'https://forms.gle/NxsK4k16ZHecHsk27'),
('batch_size', '10'),
('delay_min_m', '3'),
('delay_min_s', '10'),
('delay_max_m', '5'),
('delay_max_s', '30');

-- 2. Tabel Data Responden Kuesioner
DROP TABLE IF EXISTS \`respondents\`;
CREATE TABLE \`respondents\` (
    \`id\` INT AUTO_INCREMENT PRIMARY KEY,
    \`row_index\` INT NOT NULL,
    \`row_num\` INT NOT NULL,
    \`nama_usaha\` VARCHAR(255) NOT NULL,
    \`status\` ENUM('PENDING', 'SUBMITTED', 'FAILED') DEFAULT 'PENDING',
    \`submitted_at\` DATETIME NULL,
    \`duration\` VARCHAR(50) NULL,
    \`data_json\` LONGTEXT NOT NULL,
    \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (\`status\`),
    INDEX (\`row_num\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Log Riwayat Injeksi
DROP TABLE IF EXISTS \`injection_logs\`;
CREATE TABLE \`injection_logs\` (
    \`id\` INT AUTO_INCREMENT PRIMARY KEY,
    \`respondent_id\` INT NULL,
    \`nama_usaha\` VARCHAR(255),
    \`status\` VARCHAR(50),
    \`message\` TEXT,
    \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Inisialisasi Data Responden (150 Data)
`;

excelRows.forEach((row, idx) => {
    const isSubmitted = submittedSet.has(idx);
    const h = historyMap.get(idx);
    const status = isSubmitted ? 'SUBMITTED' : 'PENDING';
    const subAt = h && h.timestamp ? `'${new Date(h.timestamp).toISOString().slice(0, 19).replace('T', ' ')}'` : 'NULL';
    const dur = h && h.duration ? `'${h.duration}'` : 'NULL';
    const namaUsaha = (row['Nama Usaha :'] || `Data ${idx + 1}`).replace(/'/g, "''");
    const jsonStr = JSON.stringify(row).replace(/'/g, "''").replace(/\\/g, '\\\\');

    sql += `INSERT INTO \`respondents\` (\`row_index\`, \`row_num\`, \`nama_usaha\`, \`status\`, \`submitted_at\`, \`duration\`, \`data_json\`) VALUES (${idx}, ${row._rowNum}, '${namaUsaha}', '${status}', ${subAt}, ${dur}, '${jsonStr}');\n`;
});

fs.writeFileSync('d:/botgform/botgform.sql', sql, 'utf8');
console.log('Successfully generated botgform.sql with', excelRows.length, 'records.');
