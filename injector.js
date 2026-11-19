/**
 * Google Form Data Injector Bot
 * Form: Salinan Kuesioner Penelitian (IKM JUARA Madiun)
 * Source: Data_Kuesioner_Penelitian_Lengkap.xlsx
 */

const fs = require('fs');
const path = require('path');

const FORM_VIEW_URL = 'https://docs.google.com/forms/d/e/1FAIpQLSdV9w8CEwmI7k4u5Q-HZaaWLgxc8ly5Y5i5NTtr_022Ae0z1w/viewform';
const FORM_SUBMIT_URL = 'https://docs.google.com/forms/d/e/1FAIpQLSdV9w8CEwmI7k4u5Q-HZaaWLgxc8ly5Y5i5NTtr_022Ae0z1w/formResponse';

const WORK_DIR = 'd:/botgform';
const EXCEL_DATA_PATH = path.join(WORK_DIR, 'excel_data.json');
const FORM_DETAILED_PATH = path.join(WORK_DIR, 'form_detailed.json');
const PROGRESS_PATH = path.join(WORK_DIR, 'progress.json');

// Sleep helper
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

function getRandomDelay(minSec, maxSec) {
    const min = minSec * 1000;
    const max = maxSec * 1000;
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Load or initialize progress
function loadProgress() {
    if (fs.existsSync(PROGRESS_PATH)) {
        try {
            return JSON.parse(fs.readFileSync(PROGRESS_PATH, 'utf8'));
        } catch (e) {
            console.error('Error reading progress.json, creating new one.');
        }
    }
    return {
        submittedIndices: [],
        history: [],
        lastUpdated: null
    };
}

function saveProgress(progress) {
    progress.lastUpdated = new Date().toISOString();
    fs.writeFileSync(PROGRESS_PATH, JSON.stringify(progress, null, 2), 'utf8');
}

// Load Form Question Definitions
const formDetails = JSON.parse(fs.readFileSync(FORM_DETAILED_PATH, 'utf8'));
const excelRows = JSON.parse(fs.readFileSync(EXCEL_DATA_PATH, 'utf8'));

const formQuestions = formDetails.filter(f => f.type !== 8);
const questionMap = new Map();
formQuestions.forEach(q => {
    const cleanTitle = q.title.trim().replace(/\s+/g, ' ');
    questionMap.set(cleanTitle, {
        id: q.entries[0]?.id,
        options: q.entries[0]?.options || [],
        type: q.type,
        origTitle: q.title
    });
});

function findEntry(excelKey) {
    const cleanKey = excelKey.trim().replace(/\s+/g, ' ');
    for (const [title, entry] of questionMap.entries()) {
        if (title === cleanKey || title.startsWith(cleanKey) || cleanKey.startsWith(title)) {
            return entry;
        }
        const codeMatchKey = cleanKey.match(/^([A-Z0-9]+)\./);
        const codeMatchTitle = title.match(/^([A-Z0-9]+)\./);
        if (codeMatchKey && codeMatchTitle && codeMatchKey[1] === codeMatchTitle[1]) {
            return entry;
        }
    }
    return null;
}

function parseExcelDate(serial) {
    const s = Number(serial);
    const d = new Date(Math.round((s - 25569) * 86400 * 1000));
    const year = String(d.getUTCFullYear());
    const month = String(d.getUTCMonth() + 1).padStart(2, '0');
    const day = String(d.getUTCDate()).padStart(2, '0');
    return { year, month, day, str: `${year}-${month}-${day}` };
}

function buildPayload(row, fbzxToken) {
    const params = new URLSearchParams();
    params.append('fvv', '1');
    params.append('pageHistory', '0,1,2,3,4,5,6,7,8,9');
    if (fbzxToken) {
        params.append('fbzx', fbzxToken);
    }

    for (const [key, rawVal] of Object.entries(row)) {
        if (key === '_rowNum' || key === 'Timestamp' || key === 'Kategori Penilaian (Berdasarkan Total Skor)') {
            continue;
        }

        const entry = findEntry(key);
        if (!entry) continue;

        const entryId = entry.id;
        if (entry.type === 9) {
            const dateObj = parseExcelDate(rawVal);
            params.append(`entry.${entryId}_year`, dateObj.year);
            params.append(`entry.${entryId}_month`, dateObj.month);
            params.append(`entry.${entryId}_day`, dateObj.day);
            params.append(`entry.${entryId}`, dateObj.str);
        } else if (entry.type === 4) {
            const choices = String(rawVal).split(',').map(s => s.trim()).filter(Boolean);
            choices.forEach(c => {
                const matchedOpt = entry.options.find(o => o.trim().toLowerCase() === c.toLowerCase()) || c;
                params.append(`entry.${entryId}`, matchedOpt);
            });
        } else if (entry.options && entry.options.length > 0) {
            const sVal = String(rawVal).trim();
            const matchedOpt = entry.options.find(o => o.trim().toLowerCase() === sVal.toLowerCase()) || sVal;
            params.append(`entry.${entryId}`, matchedOpt);
        } else {
            params.append(`entry.${entryId}`, String(rawVal).trim());
        }
    }
    return params;
}

async function getFreshFbzx() {
    try {
        const res = await fetch(FORM_VIEW_URL, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            }
        });
        const html = await res.text();
        const match = html.match(/name="fbzx"\s+value="([^"]+)"/);
        return match ? match[1] : null;
    } catch (err) {
        console.warn('Could not fetch new fbzx token, using fallback.', err.message);
        return null;
    }
}

async function submitRow(row, fbzx) {
    const payload = buildPayload(row, fbzx);
    const res = await fetch(FORM_SUBMIT_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        },
        body: payload.toString()
    });

    const text = await res.text();
    const isSuccess = text.includes('usp=form_confirm') ||
                      text.includes('Kirim jawaban lain') ||
                      text.includes('Tanggapan Anda telah direkam') ||
                      text.includes('Jawaban Anda telah direkam');

    return { status: res.status, ok: res.ok, isSuccess };
}

// Parse CLI arguments
const args = process.argv.slice(2);
let batchSize = null;
let delayMin = 3; // seconds
let delayMax = 7; // seconds
let isDryRun = false;
let checkStatusOnly = false;
let resetProgress = false;

for (let i = 0; i < args.length; i++) {
    if (args[i] === '--batch' && args[i + 1]) {
        batchSize = parseInt(args[++i], 10);
    } else if (args[i] === '--delay-min' && args[i + 1]) {
        delayMin = parseFloat(args[++i]);
    } else if (args[i] === '--delay-max' && args[i + 1]) {
        delayMax = parseFloat(args[++i]);
    } else if (args[i] === '--dry-run') {
        isDryRun = true;
    } else if (args[i] === '--status') {
        checkStatusOnly = true;
    } else if (args[i] === '--reset') {
        resetProgress = true;
    }
}

async function main() {
    console.log('====================================================');
    console.log('🤖 GOOGLE FORM INJECTOR BOT - IKM JUARA MADIUN');
    console.log('====================================================');
    console.log(`Total data in Excel: ${excelRows.length} baris`);

    if (resetProgress) {
        fs.writeFileSync(PROGRESS_PATH, JSON.stringify({ submittedIndices: [], history: [] }, null, 2));
        console.log('Progress has been reset.');
        return;
    }

    const progress = loadProgress();
    const submittedSet = new Set(progress.submittedIndices);

    console.log(`Sudah di-submit sebelumnya: ${submittedSet.size} / ${excelRows.length}`);

    if (checkStatusOnly) {
        console.log(`Sisa yang belum di-submit: ${excelRows.length - submittedSet.size}`);
        return;
    }

    // Determine pending rows
    const pendingIndices = [];
    for (let i = 0; i < excelRows.length; i++) {
        if (!submittedSet.has(i)) {
            pendingIndices.push(i);
        }
    }

    if (pendingIndices.length === 0) {
        console.log('🎉 SEMUA DATA (150 BARIS) SUDAH BERHASIL DI-SUBMIT!');
        return;
    }

    const toProcess = batchSize ? pendingIndices.slice(0, batchSize) : pendingIndices;
    console.log(`Jumlah baris yang akan diproses sekarang: ${toProcess.length}`);
    console.log(`Rentang jeda antar submit: ${delayMin} - ${delayMax} detik`);
    if (isDryRun) {
        console.log('MODE: DRY RUN (Tidak mengirim ke Google Form)');
    }
    console.log('----------------------------------------------------');

    let currentFbzx = null;
    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < toProcess.length; i++) {
        const rowIdx = toProcess[i];
        const row = excelRows[rowIdx];
        const namaUsaha = row['Nama Usaha :'] || `Row ${rowIdx + 1}`;
        const numInBatch = i + 1;
        const totalOverall = submittedSet.size + successCount + 1;

        console.log(`[${numInBatch}/${toProcess.length}] (Total Form: ${totalOverall}/${excelRows.length}) Menyuntik: "${namaUsaha}" (Excel Row ${row._rowNum})...`);

        if (isDryRun) {
            console.log(`   -> [DRY RUN] Payload valid. Siap disubmit.`);
            continue;
        }

        // Fetch fresh fbzx periodically (every 5 submissions or on start)
        if (!currentFbzx || i % 5 === 0) {
            currentFbzx = await getFreshFbzx();
        }

        const startTime = Date.now();
        try {
            const result = await submitRow(row, currentFbzx);
            const duration = ((Date.now() - startTime) / 1000).toFixed(1);

            if (result.isSuccess) {
                console.log(`   ✅ BERHASIL (Status: ${result.status}, durasi: ${duration}s)`);
                submittedSet.add(rowIdx);
                progress.submittedIndices.push(rowIdx);
                progress.history.push({
                    index: rowIdx,
                    rowNum: row._rowNum,
                    namaUsaha: namaUsaha,
                    timestamp: new Date().toISOString(),
                    status: 'SUCCESS'
                });
                saveProgress(progress);
                successCount++;
            } else {
                console.error(`   ❌ GAGAL - Respon tidak mengandung konfirmasi Google Form (Status: ${result.status})`);
                failCount++;
            }
        } catch (err) {
            console.error(`   ❌ ERROR JARINGAN / FETCH:`, err.message);
            failCount++;
        }

        // Delay before next submission (unless last one in batch)
        if (i < toProcess.length - 1) {
            const delayMs = getRandomDelay(delayMin, delayMax);
            const delaySec = (delayMs / 1000).toFixed(1);
            console.log(`   ⏳ Menunggu jeda ${delaySec} detik sebelum data berikutnya...`);
            await sleep(delayMs);
        }
    }

    console.log('====================================================');
    console.log('Ringkasan Eksekusi Batch:');
    console.log(`- Berhasil: ${successCount}`);
    console.log(`- Gagal: ${failCount}`);
    console.log(`- Total telah terisi: ${submittedSet.size} / ${excelRows.length}`);
    console.log(`- Sisa: ${excelRows.length - submittedSet.size}`);
    console.log('====================================================');
}

main().catch(console.error);
