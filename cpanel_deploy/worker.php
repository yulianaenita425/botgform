<?php
// Server-Side Autonomous Background Worker
// Runs in background on Linux/cPanel regardless of browser state
ignore_user_abort(true);
set_time_limit(0);

$baseDir = __DIR__;
$excelDataPath = $baseDir . '/excel_data.json';
$progressPath = $baseDir . '/progress.json';
$settingsPath = $baseDir . '/settings.json';
$mappingPath = $baseDir . '/current_mapping.json';
$jobStatePath = $baseDir . '/job_state.json';

require_once $baseDir . '/api.php';

$maxRuntime = time() + 3600; // run for up to 1 hour per invocation

while (time() < $maxRuntime) {
    if (!file_exists($jobStatePath)) break;
    $job = loadJson($jobStatePath, ['isRunning' => false]);
    if (empty($job['isRunning'])) break;

    $job = checkAndProcessServerJobQueue($jobStatePath, $excelDataPath, $mappingPath, $settingsPath, $progressPath);
    if (empty($job['isRunning'])) break;

    sleep(2);
}
