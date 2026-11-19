param(
    [string]$Action = "10"
)

if ($Action -eq "status") {
    node "$PSScriptRoot\injector.js" --status
} elseif ($Action -eq "all") {
    node "$PSScriptRoot\injector.js" --delay-min 3 --delay-max 6
} else {
    node "$PSScriptRoot\injector.js" --batch $Action --delay-min 3 --delay-max 6
}
