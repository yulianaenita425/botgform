param(
    [string]$FilePath = "d:\botgform\Data_Kuesioner_Penelitian_Lengkap.xlsx"
)

Add-Type -AssemblyName System.IO.Compression.FileSystem

if (-not (Test-Path $FilePath)) {
    Write-Error "File not found: $FilePath"
    exit 1
}

$zip = [System.IO.Compression.ZipFile]::OpenRead($FilePath)

# Load shared strings
$strings = @()
$sstEntry = $zip.GetEntry('xl/sharedStrings.xml')
if ($sstEntry) {
    $sstStream = $sstEntry.Open()
    $sstReader = New-Object System.IO.StreamReader($sstStream)
    [xml]$sstXml = $sstReader.ReadToEnd()
    $sstStream.Close()

    foreach ($si in $sstXml.sst.si) {
        $strings += [string]$si.InnerText
    }
}

# Load sheet
$sheetEntry = $zip.GetEntry('xl/worksheets/sheet1.xml')
$sheetStream = $sheetEntry.Open()
$sheetReader = New-Object System.IO.StreamReader($sheetStream)
[xml]$sheetXml = $sheetReader.ReadToEnd()
$sheetStream.Close()
$zip.Dispose()

$rowsXml = $sheetXml.worksheet.sheetData.row

function Get-ColIndex($cellRef) {
    $letters = $cellRef -replace '\d+', ''
    $idx = 0
    foreach ($char in [char[]]$letters) {
        $idx = $idx * 26 + ([int]$char - [int][char]'A' + 1)
    }
    return $idx - 1
}

$allRows = @()
$headerMap = @{}

# Parse row 1 (headers)
$headerRow = $rowsXml[0]
foreach ($c in $headerRow.c) {
    $colIdx = Get-ColIndex $c.r
    $val = ""
    if ($c.t -eq 's') {
        $val = $strings[[int]$c.v]
    } else {
        $val = $c.v
    }
    $headerMap[$colIdx] = $val
}

# Parse data rows (row 2 onwards)
for ($r = 1; $r -lt $rowsXml.Count; $r++) {
    $rowXml = $rowsXml[$r]
    $rowObj = @{}
    $rowObj['_rowNum'] = $r + 1
    
    foreach ($c in $rowXml.c) {
        $colIdx = Get-ColIndex $c.r
        $headerName = [string]$headerMap[$colIdx]
        if (-not $headerName) {
            $headerName = "Col_$colIdx"
        }
        $val = ""
        if ($c.t -eq 's') {
            $val = $strings[[int]$c.v]
        } else {
            $val = $c.v
        }
        $rowObj[$headerName] = $val
    }
    $allRows += [PSCustomObject]$rowObj
}

$json = $allRows | ConvertTo-Json -Depth 5
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText('d:\botgform\excel_data.json', $json, $utf8NoBom)
Write-Output "Successfully extracted $($allRows.Count) rows to excel_data.json"
