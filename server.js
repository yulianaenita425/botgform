const http = require('http');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { exec } = require('child_process');

const PORT = 3000;
const WORK_DIR = 'd:/botgform';
const EXCEL_DATA_PATH = path.join(WORK_DIR, 'excel_data.json');
const PROGRESS_PATH = path.join(WORK_DIR, 'progress.json');
const SETTINGS_PATH = path.join(WORK_DIR, 'settings.json');
const MAPPING_PATH = path.join(WORK_DIR, 'current_mapping.json');

const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// Comprehensive Device Profiles for Realistic Rotation
const DEVICE_PROFILES = [
    {
        name: 'Chrome Windows 11',
        deviceType: 'Desktop',
        ua: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        secChUa: '"Google Chrome";v="126", "Chromium";v="126", "Not.A/Brand";v="24"',
        secChUaMobile: '?0',
        secChUaPlatform: '"Windows"'
    },
    {
        name: 'Edge Windows 10',
        deviceType: 'Desktop',
        ua: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 Edg/125.0.0.0',
        secChUa: '"Microsoft Edge";v="125", "Chromium";v="125", "Not.A/Brand";v="24"',
        secChUaMobile: '?0',
        secChUaPlatform: '"Windows"'
    },
    {
        name: 'Android Samsung Galaxy S24',
        deviceType: 'Mobile',
        ua: 'Mozilla/5.0 (Linux; Android 14; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.6478.122 Mobile Safari/537.36',
        secChUa: '"Google Chrome";v="126", "Chromium";v="126", "Not.A/Brand";v="24"',
        secChUaMobile: '?1',
        secChUaPlatform: '"Android"'
    },
    {
        name: 'Android Xiaomi Redmi Note 13',
        deviceType: 'Mobile',
        ua: 'Mozilla/5.0 (Linux; Android 13; 2312DRA50G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.6422.165 Mobile Safari/537.36',
        secChUa: '"Google Chrome";v="125", "Chromium";v="125", "Not.A/Brand";v="99"',
        secChUaMobile: '?1',
        secChUaPlatform: '"Android"'
    },
    {
        name: 'Android Oppo Reno 11',
        deviceType: 'Mobile',
        ua: 'Mozilla/5.0 (Linux; Android 14; CPH2599) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.6367.179 Mobile Safari/537.36',
        secChUa: '"Google Chrome";v="124", "Chromium";v="124", "Not.A/Brand";v="8"',
        secChUaMobile: '?1',
        secChUaPlatform: '"Android"'
    },
    {
        name: 'macOS Chrome',
        deviceType: 'Desktop',
        ua: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        secChUa: '"Google Chrome";v="126", "Chromium";v="126", "Not.A/Brand";v="24"',
        secChUaMobile: '?0',
        secChUaPlatform: '"macOS"'
    },
    {
        name: 'iPhone 15 Pro (Safari)',
        deviceType: 'Mobile',
        ua: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
        secChUa: null,
        secChUaMobile: '?1',
        secChUaPlatform: '"iOS"'
    }
];

function getRandomDeviceProfile() {
    return DEVICE_PROFILES[Math.floor(Math.random() * DEVICE_PROFILES.length)];
}

const activeSessions = new Set();

function loadSettings() {
    if (fs.existsSync(SETTINGS_PATH)) {
        try {
            return JSON.parse(fs.readFileSync(SETTINGS_PATH, 'utf8'));
        } catch (e) {}
    }
    return {
        formUrl: 'https://forms.gle/d9Z6dsyNxaLQgYvb8',
        batchSize: 10,
        delayMinMinutes: 3,
        delayMinSeconds: 10,
        delayMaxMinutes: 5,
        delayMaxSeconds: 30,
        adminUsername: 'admin',
        adminPassword: 'admin123',
        authRequired: false
    };
}

function saveSettings(settings) {
    settings.lastUpdated = new Date().toISOString();
    fs.writeFileSync(SETTINGS_PATH, JSON.stringify(settings, null, 2), 'utf8');
}

function loadExcelData() {
    if (fs.existsSync(EXCEL_DATA_PATH)) {
        try {
            return JSON.parse(fs.readFileSync(EXCEL_DATA_PATH, 'utf8'));
        } catch (e) {}
    }
    return [];
}

function loadProgress() {
    if (fs.existsSync(PROGRESS_PATH)) {
        try {
            return JSON.parse(fs.readFileSync(PROGRESS_PATH, 'utf8'));
        } catch (e) {
            console.error('Error reading progress:', e.message);
        }
    }
    return { submittedIndices: [], history: [], lastUpdated: null };
}

function saveProgress(prog) {
    prog.lastUpdated = new Date().toISOString();
    fs.writeFileSync(PROGRESS_PATH, JSON.stringify(prog, null, 2), 'utf8');
}

function loadMapping() {
    if (fs.existsSync(MAPPING_PATH)) {
        try {
            return JSON.parse(fs.readFileSync(MAPPING_PATH, 'utf8'));
        } catch (e) {}
    }
    return null;
}

function saveMapping(mapping) {
    fs.writeFileSync(MAPPING_PATH, JSON.stringify(mapping, null, 2), 'utf8');
}

// Convert Excel Serial Date to YYYY, MM, DD
function parseExcelDate(serial) {
    const s = Number(serial);
    if (isNaN(s)) return { year: '2026', month: '11', day: '18', str: String(serial) };
    const d = new Date(Math.round((s - 25569) * 86400 * 1000));
    const year = String(d.getUTCFullYear());
    const month = String(d.getUTCMonth() + 1).padStart(2, '0');
    const day = String(d.getUTCDate()).padStart(2, '0');
    return { year, month, day, str: `${year}-${month}-${day}` };
}

// Universal Form Parser: Fetches any Google Form and returns question schema
async function scanGoogleForm(inputUrl) {
    let cleanUrl = inputUrl.trim();
    if (cleanUrl.includes('forms.gle/')) {
        const res = await fetch(cleanUrl, { redirect: 'follow' });
        cleanUrl = res.url;
    }
    let viewUrl = cleanUrl;
    if (viewUrl.includes('/formResponse')) {
        viewUrl = viewUrl.replace('/formResponse', '/viewform');
    } else if (!viewUrl.includes('/viewform')) {
        viewUrl = viewUrl.replace(/\/+$/, '') + '/viewform';
    }
    let submitUrl = viewUrl.replace('/viewform', '/formResponse');

    const res = await fetch(viewUrl, {
        headers: {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
    });
    const html = await res.text();
    const match = html.match(/FB_PUBLIC_LOAD_DATA_\s*=\s*(.+?);\s*<\/script>/s);
    if (!match) {
        throw new Error('Gagal mengekstrak data formulir Google (FB_PUBLIC_LOAD_DATA_ tidak ditemukan). Pastikan form dapat diakses publik.');
    }

    const data = JSON.parse(match[1]);
    const title = data[1]?.[8] || data[8] || 'Google Form';
    const items = data[1][1] || [];

    const questions = [];
    let pageCount = 1;
    let pageHistory = ['0'];

    items.forEach((item, idx) => {
        const itemTitle = item[1] || '';
        const type = item[3];
        const entryData = item[4];

        if (type === 8) {
            pageCount++;
            pageHistory.push(String(pageCount - 1));
        } else if (entryData && Array.isArray(entryData) && entryData.length > 0) {
            entryData.forEach(sub => {
                const entryId = sub[0];
                const options = sub[1] ? sub[1].map(o => o[0]) : [];
                questions.push({
                    idx,
                    entryId,
                    title: itemTitle.trim(),
                    type, // 0: text, 2: radio, 3: dropdown, 4: checkbox, 9: date
                    options
                });
            });
        }
    });

    return {
        title,
        viewUrl,
        submitUrl,
        questions,
        pageHistory: pageHistory.join(','),
        totalPages: pageCount
    };
}

// Auto-match questions with Excel columns
function autoMatchColumns(questions, excelHeaders) {
    const mapping = {};
    questions.forEach(q => {
        const cleanQ = q.title.toLowerCase().replace(/[^a-z0-9]/g, '');
        let bestMatch = '';

        for (const h of excelHeaders) {
            const cleanH = h.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (cleanQ === cleanH) {
                bestMatch = h;
                break;
            }
            if (cleanQ.includes(cleanH) || cleanH.includes(cleanQ)) {
                bestMatch = h;
            }
            const codeQ = q.title.match(/^([A-Za-z0-9]+)\./);
            const codeH = h.match(/^([A-Za-z0-9]+)\./);
            if (codeQ && codeH && codeQ[1].toLowerCase() === codeH[1].toLowerCase()) {
                bestMatch = h;
                break;
            }
        }
        mapping[q.entryId] = {
            entryId: q.entryId,
            questionTitle: q.title,
            type: q.type,
            options: q.options,
            excelColumn: bestMatch
        };
    });
    return mapping;
}

// Build Payload using Dynamic Mapping or Fallback
function buildPayloadFromMapping(row, mappingInfo, fbzxToken) {
    const params = new URLSearchParams();
    params.append('fvv', '1');
    params.append('pageHistory', mappingInfo.pageHistory || '0,1,2,3,4,5,6,7,8,9');
    if (fbzxToken) {
        params.append('fbzx', fbzxToken);
    }

    const mapping = mappingInfo.mapping || {};

    for (const [entryId, q] of Object.entries(mapping)) {
        if (!q.excelColumn) continue;
        const rawVal = row[q.excelColumn];
        if (rawVal === undefined || rawVal === null || rawVal === '') continue;

        if (q.type === 9) {
            const dateObj = parseExcelDate(rawVal);
            params.append(`entry.${entryId}_year`, dateObj.year);
            params.append(`entry.${entryId}_month`, dateObj.month);
            params.append(`entry.${entryId}_day`, dateObj.day);
            params.append(`entry.${entryId}`, dateObj.str);
        } else if (q.type === 4) {
            const choices = String(rawVal).split(',').map(s => s.trim()).filter(Boolean);
            choices.forEach(c => {
                const matchedOpt = (q.options || []).find(o => o.trim().toLowerCase() === c.toLowerCase()) || c;
                params.append(`entry.${entryId}`, matchedOpt);
            });
        } else if (q.options && q.options.length > 0) {
            const sVal = String(rawVal).trim();
            const matchedOpt = q.options.find(o => o.trim().toLowerCase() === sVal.toLowerCase()) || sVal;
            params.append(`entry.${entryId}`, matchedOpt);
        } else {
            params.append(`entry.${entryId}`, String(rawVal).trim());
        }
    }

    return params;
}

// Active injection state
let isInjecting = false;
let stopRequested = false;
let currentJobInfo = null;
const sseClients = [];

function broadcastSSE(data) {
    const msg = `data: ${JSON.stringify(data)}\n\n`;
    sseClients.forEach(client => {
        try {
            client.write(msg);
        } catch (e) {}
    });
}

function formatDelay(totalSeconds) {
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;
    if (mins > 0 && secs > 0) {
        return `${mins} menit ${secs} detik`;
    } else if (mins > 0) {
        return `${mins} menit`;
    } else {
        return `${secs} detik`;
    }
}

async function getFreshFbzx(viewUrl, profile) {
    try {
        const res = await fetch(viewUrl, {
            headers: {
                'User-Agent': profile.ua,
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
            }
        });
        const html = await res.text();
        const match = html.match(/name="fbzx"\s+value="([^"]+)"/);
        return match ? match[1] : null;
    } catch (err) {
        return null;
    }
}

// Injection Engine with Smart Pause / Auto Recovery
async function runInjectionJob(config) {
    isInjecting = true;
    stopRequested = false;

    const excelRows = loadExcelData();
    const progress = loadProgress();
    const submittedSet = new Set(progress.submittedIndices);

    let targetIndices = [];

    if (config.mode === 'failed_only') {
        const failedIndices = new Set();
        (progress.history || []).forEach(h => {
            if (h.status === 'FAILED') failedIndices.add(h.index);
        });
        targetIndices = Array.from(failedIndices);
    } else if (config.mode === 'selective' && Array.isArray(config.selectedIndices) && config.selectedIndices.length > 0) {
        targetIndices = config.selectedIndices.filter(idx => idx >= 0 && idx < excelRows.length);
    } else if (config.mode === 'range' && config.rangeStart && config.rangeEnd) {
        const startNum = parseInt(config.rangeStart, 10);
        const endNum = parseInt(config.rangeEnd, 10);
        for (let i = 0; i < excelRows.length; i++) {
            const rowNum = excelRows[i]._rowNum;
            if (rowNum >= startNum && rowNum <= endNum) {
                targetIndices.push(i);
            }
        }
    } else {
        const pending = [];
        for (let i = 0; i < excelRows.length; i++) {
            if (!submittedSet.has(i)) {
                pending.push(i);
            }
        }
        const countToSubmit = config.batchSize && config.batchSize > 0 
            ? Math.min(config.batchSize, pending.length) 
            : pending.length;
        targetIndices = pending.slice(0, countToSubmit);
    }

    currentJobInfo = {
        totalTarget: targetIndices.length,
        completed: 0,
        success: 0,
        failed: 0,
        status: 'RUNNING'
    };

    broadcastSSE({
        type: 'JOB_START',
        job: currentJobInfo,
        totalInExcel: excelRows.length,
        alreadySubmitted: submittedSet.size
    });

    const settings = loadSettings();
    let targetFormUrl = config.formUrl || settings.formUrl || 'https://forms.gle/d9Z6dsyNxaLQgYvb8';

    // Scan / load form metadata & mapping
    let mappingInfo = loadMapping();
    if (!mappingInfo || mappingInfo.formUrl !== targetFormUrl) {
        try {
            const scanned = await scanGoogleForm(targetFormUrl);
            const headers = Object.keys(excelRows[0] || {}).filter(k => k !== '_rowNum' && k !== 'Timestamp');
            const autoMapped = autoMatchColumns(scanned.questions, headers);
            mappingInfo = {
                formUrl: targetFormUrl,
                viewUrl: scanned.viewUrl,
                submitUrl: scanned.submitUrl,
                pageHistory: scanned.pageHistory,
                mapping: autoMapped
            };
            saveMapping(mappingInfo);
        } catch (e) {
            console.error('Error auto-scanning form:', e.message);
        }
    }

    const viewUrl = mappingInfo?.viewUrl || targetFormUrl;
    const submitUrl = mappingInfo?.submitUrl || targetFormUrl.replace('/viewform', '/formResponse');

    for (let i = 0; i < targetIndices.length; i++) {
        if (stopRequested) {
            broadcastSSE({ type: 'JOB_STOPPED', message: 'Injeksi dihentikan oleh pengguna.' });
            break;
        }

        const rowIdx = targetIndices[i];
        const row = excelRows[rowIdx];
        const namaUsaha = row['Nama Usaha :'] || `Data Baris ${rowIdx + 1}`;

        let itemSuccess = false;
        let attempt = 0;
        const maxRetries = 3;

        while (attempt < maxRetries && !itemSuccess && !stopRequested) {
            attempt++;
            const deviceProfile = getRandomDeviceProfile();
            const currentFbzx = await getFreshFbzx(viewUrl, deviceProfile);

            broadcastSSE({
                type: 'ITEM_PROCESSING',
                index: rowIdx,
                rowNum: row._rowNum,
                namaUsaha: namaUsaha,
                current: i + 1,
                totalBatch: targetIndices.length,
                device: deviceProfile.name,
                attempt
            });

            const startTime = Date.now();
            let statusCode = 0;
            let errorMsg = null;
            let isRateLimited = false;

            try {
                const payload = buildPayloadFromMapping(row, mappingInfo, currentFbzx);

                const headers = {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'User-Agent': deviceProfile.ua,
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Upgrade-Insecure-Requests': '1',
                    'Referer': viewUrl,
                    'Origin': 'https://docs.google.com',
                    'Sec-Fetch-Dest': 'document',
                    'Sec-Fetch-Mode': 'navigate',
                    'Sec-Fetch-Site': 'same-origin',
                    'Sec-Fetch-User': '?1'
                };

                if (deviceProfile.secChUa) {
                    headers['Sec-Ch-Ua'] = deviceProfile.secChUa;
                    headers['Sec-Ch-Ua-Mobile'] = deviceProfile.secChUaMobile;
                    headers['Sec-Ch-Ua-Platform'] = deviceProfile.secChUaPlatform;
                }

                const res = await fetch(submitUrl, {
                    method: 'POST',
                    headers: headers,
                    body: payload.toString()
                });

                statusCode = res.status;
                const text = await res.text();

                // Check for rate limit or captcha
                if (statusCode === 429 || text.includes('unusual traffic') || text.includes('recaptcha')) {
                    isRateLimited = true;
                    errorMsg = 'Rate limit / Captcha terdeteksi dari Google.';
                } else if (text.includes('usp=form_confirm') || text.includes('Kirim jawaban lain') || text.includes('Tanggapan Anda telah direkam') || res.ok) {
                    itemSuccess = true;
                } else {
                    errorMsg = 'Form tidak memberikan konfirmasi jawaban.';
                }
            } catch (e) {
                errorMsg = e.message;
            }

            const duration = ((Date.now() - startTime) / 1000).toFixed(1);

            if (itemSuccess) {
                currentJobInfo.success++;
                submittedSet.add(rowIdx);
                if (!progress.submittedIndices.includes(rowIdx)) {
                    progress.submittedIndices.push(rowIdx);
                }
                const itemRecord = {
                    index: rowIdx,
                    rowNum: row._rowNum,
                    namaUsaha: namaUsaha,
                    device: deviceProfile.name,
                    timestamp: new Date().toISOString(),
                    duration: `${duration}s`,
                    status: 'SUCCESS'
                };
                progress.history.unshift(itemRecord);
                saveProgress(progress);

                broadcastSSE({
                    type: 'ITEM_SUCCESS',
                    item: itemRecord,
                    job: currentJobInfo,
                    totalSubmitted: progress.submittedIndices.length,
                    totalPending: excelRows.length - progress.submittedIndices.length
                });
                break;
            } else if (isRateLimited) {
                // Feature: Auto-Recovery & Smart Pause (10 mins safe cooldown)
                const pauseSeconds = 600;
                broadcastSSE({
                    type: 'SMART_PAUSE',
                    message: '⚠️ Rate limit terdeteksi. Mengaktifkan Smart Pause selama 10 menit sebelum mencoba kembali...',
                    pauseSeconds: pauseSeconds,
                    rowNum: row._rowNum,
                    namaUsaha: namaUsaha
                });
                const endPause = Date.now() + (pauseSeconds * 1000);
                while (Date.now() < endPause && !stopRequested) {
                    await sleep(1000);
                }
            } else if (attempt < maxRetries) {
                // Short retry delay
                broadcastSSE({
                    type: 'ITEM_RETRY',
                    message: `Gagal (${errorMsg}), mencoba ulang (Upaya ${attempt + 1}/${maxRetries})...`,
                    rowNum: row._rowNum
                });
                await sleep(5000);
            } else {
                // Mark as failed
                currentJobInfo.failed++;
                const failRecord = {
                    index: rowIdx,
                    rowNum: row._rowNum,
                    namaUsaha: namaUsaha,
                    device: deviceProfile.name,
                    timestamp: new Date().toISOString(),
                    duration: `${duration}s`,
                    status: 'FAILED',
                    error: errorMsg
                };
                progress.history.unshift(failRecord);
                saveProgress(progress);

                broadcastSSE({
                    type: 'ITEM_FAILED',
                    rowNum: row._rowNum,
                    namaUsaha: namaUsaha,
                    status: statusCode,
                    error: errorMsg,
                    job: currentJobInfo
                });
            }
        }

        currentJobInfo.completed++;

        // Delay before next item
        if (i < targetIndices.length - 1 && !stopRequested) {
            let delaySeconds = 0;
            if (config.customDelays && Array.isArray(config.customDelays) && config.customDelays[i] !== undefined) {
                const itemDelay = config.customDelays[i];
                if (typeof itemDelay === 'object') {
                    const m = parseInt(itemDelay.minutes || 0, 10);
                    const s = parseInt(itemDelay.seconds || 0, 10);
                    delaySeconds = (m * 60) + s;
                } else {
                    delaySeconds = parseInt(itemDelay, 10) || 5;
                }
            } else {
                delaySeconds = 180;
            }

            if (delaySeconds <= 0) delaySeconds = 3;

            const nextRow = excelRows[targetIndices[i + 1]];
            const nextNama = nextRow ? nextRow['Nama Usaha :'] : `Data ke-${i + 2}`;

            broadcastSSE({
                type: 'ITEM_DELAY',
                delaySeconds: delaySeconds,
                formatted: formatDelay(delaySeconds),
                nextIndex: i + 2,
                totalBatch: targetIndices.length,
                nextNamaUsaha: nextNama
            });

            const endWait = Date.now() + (delaySeconds * 1000);
            while (Date.now() < endWait && !stopRequested) {
                await sleep(500);
            }
        }
    }

    currentJobInfo.status = stopRequested ? 'STOPPED' : 'FINISHED';
    broadcastSSE({
        type: 'JOB_COMPLETE',
        job: currentJobInfo,
        totalSubmitted: progress.submittedIndices.length,
        totalPending: excelRows.length - progress.submittedIndices.length
    });

    isInjecting = false;
    stopRequested = false;
}

// HTTP Server
const server = http.createServer((req, res) => {
    const parsed = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
    const pathname = parsed.pathname;

    // Serve HTML UI
    if (pathname === '/' || pathname === '/index.html') {
        const html = fs.readFileSync(path.join(WORK_DIR, 'index.html'), 'utf8');
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end(html);
        return;
    }

    // SSE Endpoint
    if (pathname === '/api/events') {
        res.writeHead(200, {
            'Content-Type': 'text/event-stream',
            'Cache-Control': 'no-cache',
            'Connection': 'keep-alive',
            'Access-Control-Allow-Origin': '*'
        });
        res.write('retry: 10000\n\n');
        sseClients.push(res);

        req.on('close', () => {
            const idx = sseClients.indexOf(res);
            if (idx !== -1) sseClients.splice(idx, 1);
        });
        return;
    }

    // Auth Login
    if (pathname === '/api/login' && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
            try {
                const { username, password } = JSON.parse(body || '{}');
                const settings = loadSettings();
                const validUser = settings.adminUsername || 'admin';
                const validPass = settings.adminPassword || 'admin123';

                if (username === validUser && password === validPass) {
                    const token = crypto.randomBytes(24).toString('hex');
                    activeSessions.add(token);
                    res.writeHead(200, {
                        'Content-Type': 'application/json',
                        'Set-Cookie': `auth_token=${token}; Path=/; HttpOnly; SameSite=Lax`
                    });
                    res.end(JSON.stringify({ success: true, token, username: validUser }));
                } else {
                    res.writeHead(401, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'Username atau password salah!' }));
                }
            } catch (e) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: e.message }));
            }
        });
        return;
    }

    // Auth Logout
    if (pathname === '/api/logout' && req.method === 'POST') {
        res.writeHead(200, {
            'Content-Type': 'application/json',
            'Set-Cookie': 'auth_token=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT'
        });
        res.end(JSON.stringify({ success: true }));
        return;
    }

    // API Status & Full Dataset
    if (pathname === '/api/status' && req.method === 'GET') {
        const excelRows = loadExcelData();
        const progress = loadProgress();
        const settings = loadSettings();
        const total = excelRows.length;
        const submitted = progress.submittedIndices.length;
        const submittedSet = new Set(progress.submittedIndices);

        const historyMap = new Map();
        (progress.history || []).forEach(h => historyMap.set(h.index, h));

        const allRowsSummary = excelRows.map((r, i) => {
            const h = historyMap.get(i);
            return {
                index: i,
                rowNum: r._rowNum,
                namaUsaha: r['Nama Usaha :'] || `Data ${i + 1}`,
                isSubmitted: submittedSet.has(i),
                status: h ? h.status : (submittedSet.has(i) ? 'SUCCESS' : 'PENDING'),
                duration: h ? h.duration : null,
                device: h ? h.device : null,
                timestamp: h ? h.timestamp : null
            };
        });

        // Count failed
        const failedCount = (progress.history || []).filter(h => h.status === 'FAILED').length;

        // Excel headers for mapping
        const excelHeaders = Object.keys(excelRows[0] || {}).filter(k => k !== '_rowNum' && k !== 'Timestamp');

        let fileModifiedStr = '-';
        if (settings.fileLastModified) {
            fileModifiedStr = settings.fileLastModified;
        } else if (fs.existsSync(EXCEL_FILE_PATH)) {
            const stats = fs.statSync(EXCEL_FILE_PATH);
            const d = new Date(stats.mtime);
            fileModifiedStr = d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID') + ' WIB';
        }

        let formLastUpdatedStr = settings.formLastUpdated || (settings.lastUpdated ? new Date(settings.lastUpdated).toLocaleDateString('id-ID') + ' ' + new Date(settings.lastUpdated).toLocaleTimeString('id-ID') + ' WIB' : '-');

        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            total,
            submitted,
            pending: total - submitted,
            failedCount,
            history: progress.history.slice(0, 50),
            isInjecting,
            currentJob: currentJobInfo,
            allRows: allRowsSummary,
            excelHeaders,
            activeFileName: settings.activeFileName || path.basename(EXCEL_FILE_PATH),
            fileLastModified: fileModifiedStr,
            activeFormUrl: settings.formUrl,
            formLastUpdated: formLastUpdatedStr,
            settings: {
                formUrl: settings.formUrl,
                batchSize: settings.batchSize,
                authRequired: settings.authRequired,
                adminUsername: settings.adminUsername
            }
        }));
        return;
    }

    // API Scan Google Form & Get Mapping
    if (pathname === '/api/scan-form') {
        if (req.method === 'POST') {
            let body = '';
            req.on('data', chunk => body += chunk);
            req.on('end', async () => {
                try {
                    const { formUrl } = JSON.parse(body || '{}');
                    if (!formUrl) throw new Error('URL Google Form harus diisi!');
                    const scanned = await scanGoogleForm(formUrl);
                    const excelRows = loadExcelData();
                    const headers = Object.keys(excelRows[0] || {}).filter(k => k !== '_rowNum' && k !== 'Timestamp');
                    const mapping = autoMatchColumns(scanned.questions, headers);

                    const mappingInfo = {
                        formUrl,
                        viewUrl: scanned.viewUrl,
                        submitUrl: scanned.submitUrl,
                        pageHistory: scanned.pageHistory,
                        mapping
                    };
                    saveMapping(mappingInfo);

                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        success: true,
                        title: scanned.title,
                        totalQuestions: scanned.questions.length,
                        mapping: mappingInfo
                    }));
                } catch (e) {
                    res.writeHead(500, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: e.message }));
                }
            });
            return;
        } else if (req.method === 'GET') {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify(loadMapping() || {}));
            return;
        }
    }

    // API Save Mapping
    if (pathname === '/api/save-mapping' && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
            try {
                const newMapping = JSON.parse(body || '{}');
                saveMapping(newMapping);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, message: 'Pemetaan kolom berhasil disimpan!' }));
            } catch (e) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: e.message }));
            }
        });
        return;
    }

    // API Export Report (CSV / Excel format)
    if (pathname === '/api/export-report' && req.method === 'GET') {
        const progress = loadProgress();
        const history = progress.history || [];

        let csv = '\uFEFFNo,Baris Excel,Nama Usaha,Tanggal Submit,Jam Submit,Durasi,Status,Perangkat\r\n';
        history.forEach((h, idx) => {
            const d = h.timestamp ? new Date(h.timestamp) : null;
            const tgl = d ? d.toLocaleDateString('id-ID') : '-';
            const jam = d ? d.toLocaleTimeString('id-ID') : '-';
            const nama = (h.namaUsaha || '').replace(/"/g, '""');
            const dev = (h.device || '-').replace(/"/g, '""');
            csv += `${idx + 1},${h.rowNum},"${nama}",${tgl},${jam},"${h.duration || '-'}",${h.status},"${dev}"\r\n`;
        });

        res.writeHead(200, {
            'Content-Type': 'text/csv; charset=utf-8',
            'Content-Disposition': `attachment; filename="Laporan_Injeksi_${new Date().toISOString().slice(0, 10)}.csv"`
        });
        res.end(csv);
        return;
    }

    // API Settings (Get / Save)
    if (pathname === '/api/settings') {
        if (req.method === 'GET') {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify(loadSettings()));
            return;
        } else if (req.method === 'POST') {
            let body = '';
            req.on('data', chunk => body += chunk);
            req.on('end', () => {
                try {
                    const newSettings = JSON.parse(body || '{}');
                    const cur = loadSettings();
                    const d = new Date();
                    const merged = { 
                        ...cur, 
                        ...newSettings,
                        lastUpdated: d.toISOString(),
                        formLastUpdated: d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID') + ' WIB'
                    };
                    saveSettings(merged);
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ 
                        success: true, 
                        message: 'Pengaturan berhasil disimpan!', 
                        settings: merged,
                        activeFormUrl: merged.formUrl,
                        formLastUpdated: merged.formLastUpdated
                    }));
                } catch (e) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: e.message }));
                }
            });
            return;
        }
    }

    // API File Upload (.xlsx / .csv)
    if (pathname === '/api/upload' && req.method === 'POST') {
        const filename = parsed.searchParams.get('filename') || 'uploaded_data.xlsx';
        const targetPath = path.join(WORK_DIR, 'uploaded_' + path.basename(filename));
        const fileStream = fs.createWriteStream(targetPath);

        req.pipe(fileStream);

        fileStream.on('finish', () => {
            const cmd = `powershell -ExecutionPolicy Bypass -File "d:\\botgform\\extract_excel.ps1" -FilePath "${targetPath}"`;
            exec(cmd, (err, stdout, stderr) => {
                if (err) {
                    res.writeHead(500, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'Gagal mengekstrak data Excel: ' + stderr }));
                    return;
                }

                const updatedRows = loadExcelData();
                try {
                    exec(`node d:\\botgform\\generate_sql.js`, () => {});
                } catch (e) {}

                const cur = loadSettings();
                const d = new Date();
                cur.activeFileName = filename;
                cur.fileLastModified = d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID') + ' WIB';
                saveSettings(cur);

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    totalRows: updatedRows.length,
                    activeFileName: cur.activeFileName,
                    fileLastModified: cur.fileLastModified,
                    message: `Berhasil mengunggah & memuat ${updatedRows.length} baris data dari ${filename}!`
                }));
            });
        });

        fileStream.on('error', (err) => {
            res.writeHead(500, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: 'Gagal menyimpan file upload: ' + err.message }));
        });
        return;
    }

    // API Start
    if (pathname === '/api/start' && req.method === 'POST') {
        if (isInjecting) {
            res.writeHead(400, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: 'Proses injeksi sedang berjalan!' }));
            return;
        }

        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
            try {
                const config = JSON.parse(body || '{}');
                if (config.formUrl) {
                    const cur = loadSettings();
                    cur.formUrl = config.formUrl;
                    saveSettings(cur);
                }
                runInjectionJob(config);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, message: 'Proses injeksi dimulai.' }));
            } catch (err) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: err.message }));
            }
        });
        return;
    }

    // API Stop
    if (pathname === '/api/stop' && req.method === 'POST') {
        if (isInjecting) {
            stopRequested = true;
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: true, message: 'Permintaan penghentian diterima.' }));
        } else {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: true, message: 'Tidak ada proses yang berjalan.' }));
        }
        return;
    }

    // API Reset
    if (pathname === '/api/reset' && req.method === 'POST') {
        if (isInjecting) {
            res.writeHead(400, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: 'Tidak bisa reset saat injeksi berjalan.' }));
            return;
        }
        saveProgress({ submittedIndices: [], history: [] });
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: true, message: 'Progress berhasil direset.' }));
        return;
    }

    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end('Not Found');
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`====================================================`);
    console.log(`🌐 Server Web Local berjalan di: http://localhost:${PORT}`);
    console.log(`====================================================`);
});
