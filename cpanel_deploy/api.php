<?php
session_start();

// Prevent all forms of caching across proxies, browsers, and cPanel
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$workDir = __DIR__;
$excelDataPath = $workDir . '/excel_data.json';
$progressPath = $workDir . '/progress.json';
$settingsPath = $workDir . '/settings.json';
$mappingPath = $workDir . '/current_mapping.json';
$jobStatePath = $workDir . '/job_state.json';

// Device Profiles
$deviceProfiles = [
    [
        'name' => 'Chrome Windows 11',
        'deviceType' => 'Desktop',
        'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        'secChUa' => '"Google Chrome";v="126", "Chromium";v="126", "Not.A/Brand";v="24"',
        'secChUaMobile' => '?0',
        'secChUaPlatform' => '"Windows"'
    ],
    [
        'name' => 'Edge Windows 10',
        'deviceType' => 'Desktop',
        'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 Edg/125.0.0.0',
        'secChUa' => '"Microsoft Edge";v="125", "Chromium";v="125", "Not.A/Brand";v="24"',
        'secChUaMobile' => '?0',
        'secChUaPlatform' => '"Windows"'
    ],
    [
        'name' => 'Android Samsung Galaxy S24',
        'deviceType' => 'Mobile',
        'ua' => 'Mozilla/5.0 (Linux; Android 14; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.6478.122 Mobile Safari/537.36',
        'secChUa' => '"Google Chrome";v="126", "Chromium";v="126", "Not.A/Brand";v="24"',
        'secChUaMobile' => '?1',
        'secChUaPlatform' => '"Android"'
    ],
    [
        'name' => 'Android Xiaomi Redmi Note 13',
        'deviceType' => 'Mobile',
        'ua' => 'Mozilla/5.0 (Linux; Android 13; 2312DRA50G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.6422.165 Mobile Safari/537.36',
        'secChUa' => '"Google Chrome";v="125", "Chromium";v="125", "Not.A/Brand";v="99"',
        'secChUaMobile' => '?1',
        'secChUaPlatform' => '"Android"'
    ],
    [
        'name' => 'Android Oppo Reno 11',
        'deviceType' => 'Mobile',
        'ua' => 'Mozilla/5.0 (Linux; Android 14; CPH2599) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.6367.179 Mobile Safari/537.36',
        'secChUa' => '"Google Chrome";v="124", "Chromium";v="124", "Not.A/Brand";v="8"',
        'secChUaMobile' => '?1',
        'secChUaPlatform' => '"Android"'
    ],
    [
        'name' => 'macOS Chrome',
        'deviceType' => 'Desktop',
        'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        'secChUa' => '"Google Chrome";v="126", "Chromium";v="126", "Not.A/Brand";v="24"',
        'secChUaMobile' => '?0',
        'secChUaPlatform' => '"macOS"'
    ],
    [
        'name' => 'iPhone 15 Pro (Safari)',
        'deviceType' => 'Mobile',
        'ua' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
        'secChUa' => null,
        'secChUaMobile' => '?1',
        'secChUaPlatform' => '"iOS"'
    ]
];

function loadJson($path, $default = []) {
    if (file_exists($path)) {
        clearstatcache(true, $path);
        $content = @file_get_contents($path);
        if ($content !== false) {
            $data = json_decode($content, true);
            if ($data !== null) return $data;
        }
    }
    return $default;
}

function saveJson($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    clearstatcache(true, $path);
}

function parseExcelDate($serial) {
    if (!is_numeric($serial)) return ['year' => '2026', 'month' => '11', 'day' => '18', 'str' => (string)$serial];
    $days = (float)$serial - 25569;
    $ts = round($days * 86400);
    $y = gmdate('Y', $ts);
    $m = gmdate('m', $ts);
    $d = gmdate('d', $ts);
    return ['year' => $y, 'month' => $m, 'day' => $d, 'str' => "$y-$m-$d"];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. Login
if ($action === 'login') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $settings = loadJson($settingsPath, []);
    $u = isset($settings['adminUsername']) ? $settings['adminUsername'] : 'admin';
    $p = isset($settings['adminPassword']) ? $settings['adminPassword'] : 'admin123';

    if (isset($input['username']) && isset($input['password']) && $input['username'] === $u && $input['password'] === $p) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $u;
        echo json_encode(['success' => true, 'username' => $u]);
    } else {
        echo json_encode(['error' => 'Username atau password salah!']);
    }
    exit;
}

// 2. Logout
if ($action === 'logout') {
    header('Content-Type: application/json');
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// 3. Check Session Status
if ($action === 'check_auth') {
    header('Content-Type: application/json');
    $isLogged = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    echo json_encode(['isLoggedIn' => $isLogged, 'username' => $isLogged ? $_SESSION['admin_user'] : null]);
    exit;
}

// Protect all other actions if not logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Akses ditolak. Silakan login terlebih dahulu.', 'needLogin' => true]);
    exit;
}

// 4. Update Job State (Synchronizes live progress between Laptop & HP)
if ($action === 'update_job_state') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $input['lastUpdated'] = date('c');
    saveJson($jobStatePath, $input);
    echo json_encode(['success' => true]);
    exit;
}

// 5. Status
if ($action === 'status') {
    header('Content-Type: application/json');
    $excelRows = loadJson($excelDataPath, []);
    $progress = loadJson($progressPath, ['submittedIndices' => [], 'history' => []]);
    $settings = loadJson($settingsPath, [
        'formUrl' => 'https://forms.gle/d9Z6dsyNxaLQgYvb8',
        'batchSize' => 10,
        'adminUsername' => 'admin'
    ]);
    $jobState = loadJson($jobStatePath, ['isRunning' => false]);

    $total = count($excelRows);
    $submittedIndices = isset($progress['submittedIndices']) ? $progress['submittedIndices'] : [];
    $submittedSet = array_flip($submittedIndices);
    $submitted = count($submittedIndices);

    $history = isset($progress['history']) ? $progress['history'] : [];
    $historyMap = [];
    foreach ($history as $h) {
        $historyMap[$h['index']] = $h;
    }

    $allRows = [];
    $failedCount = 0;
    foreach ($excelRows as $i => $r) {
        $h = isset($historyMap[$i]) ? $historyMap[$i] : null;
        $isSub = isset($submittedSet[$i]);
        $status = $h ? $h['status'] : ($isSub ? 'SUCCESS' : 'PENDING');
        if ($status === 'FAILED') $failedCount++;

        $allRows[] = [
            'index' => $i,
            'rowNum' => isset($r['_rowNum']) ? $r['_rowNum'] : ($i + 2),
            'namaUsaha' => isset($r['Nama Usaha :']) ? $r['Nama Usaha :'] : "Data " . ($i + 1),
            'isSubmitted' => $isSub,
            'status' => $status,
            'duration' => $h ? $h['duration'] : null,
            'device' => $h ? (isset($h['device']) ? $h['device'] : null) : null,
            'timestamp' => $h ? $h['timestamp'] : null
        ];
    }

    $excelHeaders = [];
    if (!empty($excelRows[0])) {
        foreach (array_keys($excelRows[0]) as $k) {
            if ($k !== '_rowNum' && $k !== 'Timestamp') $excelHeaders[] = $k;
        }
    }

    // Active Excel metadata
    $activeExcelName = isset($settings['activeFileName']) ? $settings['activeFileName'] : 'Data_Kuesioner_Penelitian_Lengkap.xlsx';
    if (!empty($settings['fileLastModified'])) {
        $fileModifiedStr = $settings['fileLastModified'];
    } elseif (file_exists($excelDataPath)) {
        $fileModifiedStr = date('d/m/Y H:i:s', filemtime($excelDataPath)) . ' WIB';
    } else {
        $fileModifiedStr = date('d/m/Y H:i:s') . ' WIB';
    }

    $activeFormUrl = isset($settings['formUrl']) ? $settings['formUrl'] : 'https://forms.gle/d9Z6dsyNxaLQgYvb8';
    $formLastUpdated = !empty($settings['formLastUpdated']) ? $settings['formLastUpdated'] : (!empty($settings['lastUpdated']) ? $settings['lastUpdated'] : '-');
    if ($formLastUpdated !== '-' && !strpos($formLastUpdated, 'WIB') && strtotime($formLastUpdated)) {
        $formLastUpdated = date('d/m/Y H:i:s', strtotime($formLastUpdated)) . ' WIB';
    }

    echo json_encode([
        'total' => $total,
        'submitted' => $submitted,
        'pending' => $total - $submitted,
        'failedCount' => $failedCount,
        'history' => array_slice($history, 0, 50),
        'allRows' => $allRows,
        'excelHeaders' => $excelHeaders,
        'settings' => $settings,
        'jobState' => $jobState,
        'activeFileName' => $activeExcelName,
        'fileLastModified' => $fileModifiedStr,
        'activeFormUrl' => $activeFormUrl,
        'formLastUpdated' => $formLastUpdated,
        'serverTime' => time()
    ]);
    exit;
}

// 6. Settings
if ($action === 'settings') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $cur = loadJson($settingsPath, []);
        $merged = array_merge($cur, $input);
        $merged['lastUpdated'] = date('c');
        $merged['formLastUpdated'] = date('d/m/Y H:i:s') . ' WIB';
        saveJson($settingsPath, $merged);
        echo json_encode([
            'success' => true, 
            'message' => 'Pengaturan berhasil disimpan!', 
            'settings' => $merged,
            'activeFormUrl' => isset($merged['formUrl']) ? $merged['formUrl'] : '',
            'formLastUpdated' => $merged['formLastUpdated']
        ]);
    } else {
        echo json_encode(loadJson($settingsPath, []));
    }
    exit;
}

// 7. Scan Google Form
if ($action === 'scan') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $formUrl = isset($input['formUrl']) ? trim($input['formUrl']) : '';

    if (empty($formUrl)) {
        echo json_encode(['error' => 'URL Google Form tidak boleh kosong']);
        exit;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $html = curl_exec($ch);
    $viewUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if (strpos($viewUrl, '/formResponse') !== false) {
        $viewUrl = str_replace('/formResponse', '/viewform', $viewUrl);
    } elseif (strpos($viewUrl, '/viewform') === false) {
        $viewUrl = rtrim($viewUrl, '/') . '/viewform';
    }
    $submitUrl = str_replace('/viewform', '/formResponse', $viewUrl);

    if (!preg_match('/FB_PUBLIC_LOAD_DATA_\s*=\s*(.+?);\s*<\/script>/s', $html, $matches)) {
        echo json_encode(['error' => 'Gagal membaca form. Pastikan form terbuka untuk publik.']);
        exit;
    }

    $formData = json_decode($matches[1], true);
    $title = isset($formData[1][8]) ? $formData[1][8] : (isset($formData[8]) ? $formData[8] : 'Google Form');
    $items = isset($formData[1][1]) ? $formData[1][1] : [];

    $questions = [];
    $pageCount = 1;
    $pageHistory = ['0'];

    foreach ($items as $idx => $item) {
        $itemTitle = isset($item[1]) ? $item[1] : '';
        $type = isset($item[3]) ? $item[3] : 0;
        $entryData = isset($item[4]) ? $item[4] : null;

        if ($type === 8) {
            $pageCount++;
            $pageHistory[] = (string)($pageCount - 1);
        } elseif (is_array($entryData) && count($entryData) > 0) {
            foreach ($entryData as $sub) {
                $entryId = $sub[0];
                $options = [];
                if (isset($sub[1]) && is_array($sub[1])) {
                    foreach ($sub[1] as $opt) {
                        $options[] = $opt[0];
                    }
                }
                $questions[] = [
                    'idx' => $idx,
                    'entryId' => $entryId,
                    'title' => trim($itemTitle),
                    'type' => $type,
                    'options' => $options
                ];
            }
        }
    }

    $excelRows = loadJson($excelDataPath, []);
    $excelHeaders = [];
    if (!empty($excelRows[0])) {
        foreach (array_keys($excelRows[0]) as $k) {
            if ($k !== '_rowNum' && $k !== 'Timestamp') $excelHeaders[] = $k;
        }
    }

    $mapping = [];
    foreach ($questions as $q) {
        $cleanQ = preg_replace('/[^a-z0-9]/', '', strtolower($q['title']));
        $bestMatch = '';
        foreach ($excelHeaders as $h) {
            $cleanH = preg_replace('/[^a-z0-9]/', '', strtolower($h));
            if ($cleanQ === $cleanH || strpos($cleanQ, $cleanH) !== false || strpos($cleanH, $cleanQ) !== false) {
                $bestMatch = $h;
                break;
            }
            if (preg_match('/^([A-Za-z0-9]+)\./', $q['title'], $mQ) && preg_match('/^([A-Za-z0-9]+)\./', $h, $mH)) {
                if (strtolower($mQ[1]) === strtolower($mH[1])) {
                    $bestMatch = $h;
                    break;
                }
            }
        }

        $mapping[$q['entryId']] = [
            'entryId' => $q['entryId'],
            'questionTitle' => $q['title'],
            'type' => $q['type'],
            'options' => $q['options'],
            'excelColumn' => $bestMatch
        ];
    }

    $mappingInfo = [
        'formUrl' => $formUrl,
        'viewUrl' => $viewUrl,
        'submitUrl' => $submitUrl,
        'pageHistory' => implode(',', $pageHistory),
        'mapping' => $mapping
    ];
    saveJson($mappingPath, $mappingInfo);

    echo json_encode([
        'success' => true,
        'title' => $title,
        'totalQuestions' => count($questions),
        'mapping' => $mappingInfo
    ]);
    exit;
}

// 8. Save Mapping
if ($action === 'save_mapping') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    saveJson($mappingPath, $input);
    echo json_encode(['success' => true, 'message' => 'Pemetaan berhasil disimpan!']);
    exit;
}

// 9. Submit Single Item
if ($action === 'submit_item') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $rowIdx = isset($input['index']) ? (int)$input['index'] : -1;

    $excelRows = loadJson($excelDataPath, []);
    if (!isset($excelRows[$rowIdx])) {
        echo json_encode(['success' => false, 'error' => 'Data index tidak ditemukan']);
        exit;
    }

    $row = $excelRows[$rowIdx];
    $mappingInfo = loadJson($mappingPath, []);
    $settings = loadJson($settingsPath, []);
    $targetFormUrl = isset($mappingInfo['formUrl']) ? $mappingInfo['formUrl'] : (isset($settings['formUrl']) ? $settings['formUrl'] : 'https://forms.gle/d9Z6dsyNxaLQgYvb8');

    $viewUrl = isset($mappingInfo['viewUrl']) ? $mappingInfo['viewUrl'] : $targetFormUrl;
    $submitUrl = isset($mappingInfo['submitUrl']) ? $mappingInfo['submitUrl'] : str_replace('/viewform', '/formResponse', $targetFormUrl);
    $pageHistory = isset($mappingInfo['pageHistory']) ? $mappingInfo['pageHistory'] : '0,1,2,3,4,5,6,7,8,9';
    $mapping = isset($mappingInfo['mapping']) ? $mappingInfo['mapping'] : [];

    global $deviceProfiles;
    $device = $deviceProfiles[array_rand($deviceProfiles)];

    // Fetch fresh fbzx
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $viewUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $device['ua']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $viewHtml = curl_exec($ch);
    curl_close($ch);

    $fbzx = '';
    if (preg_match('/name="fbzx"\s+value="([^"]+)"/', $viewHtml, $m)) {
        $fbzx = $m[1];
    }

    $fields = [];
    $fields['fvv'] = '1';
    $fields['pageHistory'] = $pageHistory;
    if (!empty($fbzx)) $fields['fbzx'] = $fbzx;

    foreach ($mapping as $entryId => $q) {
        $col = isset($q['excelColumn']) ? $q['excelColumn'] : '';
        if (empty($col) || !isset($row[$col])) continue;

        $val = $row[$col];
        if ($val === '' || $val === null) continue;

        $type = isset($q['type']) ? $q['type'] : 0;
        $options = isset($q['options']) ? $q['options'] : [];

        if ($type === 9) { // Date
            $d = parseExcelDate($val);
            $fields["entry.{$entryId}_year"] = $d['year'];
            $fields["entry.{$entryId}_month"] = $d['month'];
            $fields["entry.{$entryId}_day"] = $d['day'];
            $fields["entry.{$entryId}"] = $d['str'];
        } elseif ($type === 4) { // Checkbox
            $choices = array_filter(array_map('trim', explode(',', (string)$val)));
            foreach ($choices as $c) {
                $matched = $c;
                foreach ($options as $opt) {
                    if (strtolower(trim($opt)) === strtolower($c)) {
                        $matched = $opt;
                        break;
                    }
                }
                $fields["entry.{$entryId}"][] = $matched;
            }
        } elseif (!empty($options)) {
            $sVal = trim((string)$val);
            $matched = $sVal;
            foreach ($options as $opt) {
                if (strtolower(trim($opt)) === strtolower($sVal)) {
                    $matched = $opt;
                    break;
                }
            }
            $fields["entry.{$entryId}"] = $matched;
        } else {
            $fields["entry.{$entryId}"] = trim((string)$val);
        }
    }

    $queryParts = [];
    foreach ($fields as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $subV) {
                $queryParts[] = urlencode($k) . '=' . urlencode($subV);
            }
        } else {
            $queryParts[] = urlencode($k) . '=' . urlencode($v);
        }
    }
    $postBody = implode('&', $queryParts);

    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8',
        'Upgrade-Insecure-Requests: 1',
        'Referer: ' . $viewUrl,
        'Origin: https://docs.google.com'
    ];
    if (!empty($device['secChUa'])) {
        $headers[] = 'Sec-Ch-Ua: ' . $device['secChUa'];
        $headers[] = 'Sec-Ch-Ua-Mobile: ' . $device['secChUaMobile'];
        $headers[] = 'Sec-Ch-Ua-Platform: ' . $device['secChUaPlatform'];
    }

    $startTime = microtime(true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $submitUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $device['ua']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $respHtml = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $duration = round(microtime(true) - $startTime, 1) . 's';

    $isSuccess = ($httpCode === 200) && (
        strpos($respHtml, 'usp=form_confirm') !== false ||
        strpos($respHtml, 'Kirim jawaban lain') !== false ||
        strpos($respHtml, 'Tanggapan Anda telah direkam') !== false ||
        strpos($respHtml, 'Jawaban Anda telah direkam') !== false
    );

    $namaUsaha = isset($row['Nama Usaha :']) ? $row['Nama Usaha :'] : "Data Baris " . ($rowIdx + 1);
    $rowNum = isset($row['_rowNum']) ? $row['_rowNum'] : ($rowIdx + 2);

    $progress = loadJson($progressPath, ['submittedIndices' => [], 'history' => []]);

    if ($isSuccess) {
        if (!in_array($rowIdx, $progress['submittedIndices'])) {
            $progress['submittedIndices'][] = $rowIdx;
        }
        $record = [
            'index' => $rowIdx,
            'rowNum' => $rowNum,
            'namaUsaha' => $namaUsaha,
            'device' => $device['name'],
            'timestamp' => date('c'),
            'duration' => $duration,
            'status' => 'SUCCESS'
        ];
        array_unshift($progress['history'], $record);
        saveJson($progressPath, $progress);

        echo json_encode([
            'success' => true,
            'status' => $httpCode,
            'item' => $record
        ]);
    } else {
        $record = [
            'index' => $rowIdx,
            'rowNum' => $rowNum,
            'namaUsaha' => $namaUsaha,
            'device' => $device['name'],
            'timestamp' => date('c'),
            'duration' => $duration,
            'status' => 'FAILED',
            'error' => $curlErr ? $curlErr : "Status $httpCode"
        ];
        array_unshift($progress['history'], $record);
        saveJson($progressPath, $progress);

        echo json_encode([
            'success' => false,
            'status' => $httpCode,
            'error' => $curlErr ? $curlErr : 'Form response tidak valid',
            'item' => $record
        ]);
    }
    exit;
}

// 10. Upload Excel File
if ($action === 'upload') {
    header('Content-Type: application/json');
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File tidak terunggah dengan benar']);
        exit;
    }

    $tmp = $_FILES['file']['tmp_name'];
    $name = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if ($ext === 'xlsx') {
        $zip = new ZipArchive();
        if ($zip->open($tmp) === true) {
            $strings = [];
            $sstXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sstXml) {
                $xml = simplexml_load_string($sstXml);
                foreach ($xml->si as $si) {
                    $strings[] = (string)$si->t ? (string)$si->t : (string)$si->r->t;
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $sheet = simplexml_load_string($sheetXml);
            $zip->close();

            $rows = $sheet->sheetData->row;
            $headerMap = [];
            $headerRow = $rows[0];

            foreach ($headerRow->c as $c) {
                $ref = (string)$c['r'];
                $colLetters = preg_replace('/\d+/', '', $ref);
                $val = (string)$c->v;
                if ((string)$c['t'] === 's') {
                    $val = isset($strings[(int)$val]) ? $strings[(int)$val] : $val;
                }
                $headerMap[$colLetters] = $val;
            }

            $allRows = [];
            for ($r = 1; $r < count($rows); $r++) {
                $rowXml = $rows[$r];
                $rowObj = ['_rowNum' => $r + 1];
                foreach ($rowXml->c as $c) {
                    $ref = (string)$c['r'];
                    $colLetters = preg_replace('/\d+/', '', $ref);
                    $hName = isset($headerMap[$colLetters]) ? $headerMap[$colLetters] : "Col_$colLetters";
                    $val = (string)$c->v;
                    if ((string)$c['t'] === 's') {
                        $val = isset($strings[(int)$val]) ? $strings[(int)$val] : $val;
                    }
                    $rowObj[$hName] = $val;
                }
                $allRows[] = $rowObj;
            }

            saveJson($excelDataPath, $allRows);

            // Update settings with new file metadata
            $settings = loadJson($settingsPath, []);
            $settings['activeFileName'] = $name;
            $settings['fileLastModified'] = date('d/m/Y H:i:s') . ' WIB';
            if (isset($_FILES['file']['size'])) {
                $settings['fileSize'] = $_FILES['file']['size'];
            }
            saveJson($settingsPath, $settings);

            echo json_encode([
                'success' => true,
                'totalRows' => count($allRows),
                'activeFileName' => $name,
                'fileLastModified' => $settings['fileLastModified'],
                'message' => "Berhasil memuat " . count($allRows) . " data dari $name!"
            ]);
            exit;
        } else {
            echo json_encode(['error' => 'Gagal membuka file XLSX']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Format harus .xlsx']);
        exit;
    }
}

// 11. Export Report CSV
if ($action === 'export_report') {
    $progress = loadJson($progressPath, ['history' => []]);
    $history = isset($progress['history']) ? $progress['history'] : [];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Injeksi_' . date('Y-m-d') . '.csv"');

    echo "\xEF\xBB\xBF";
    echo "No,Baris Excel,Nama Usaha,Tanggal Submit,Jam Submit,Durasi,Status,Perangkat\r\n";
    foreach ($history as $idx => $h) {
        $ts = !empty($h['timestamp']) ? strtotime($h['timestamp']) : time();
        $tgl = date('d/m/Y', $ts);
        $jam = date('H:i:s', $ts);
        $nama = str_replace('"', '""', $h['namaUsaha']);
        $dev = isset($h['device']) ? str_replace('"', '""', $h['device']) : '-';
        $dur = isset($h['duration']) ? $h['duration'] : '-';
        $st = $h['status'];
        echo ($idx + 1) . ",{$h['rowNum']},\"{$nama}\",{$tgl},{$jam},\"{$dur}\",{$st},\"{$dev}\"\r\n";
    }
    exit;
}

// 12. Reset
if ($action === 'reset') {
    header('Content-Type: application/json');
    saveJson($progressPath, ['submittedIndices' => [], 'history' => []]);
    saveJson($jobStatePath, ['isRunning' => false]);
    echo json_encode(['success' => true, 'message' => 'Progress berhasil direset']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'online']);
exit;
