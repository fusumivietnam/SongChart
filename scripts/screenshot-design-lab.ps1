param(
    [string]$BaseUrl = "http://songchart.test",
    [string]$OutputDirectory = "storage/app/design-lab-screenshots"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path "vendor/autoload.php")) {
    throw "vendor/autoload.php is missing. Run scripts/setup-laragon.bat first."
}

if (-not (Test-Path "public/build/manifest.json")) {
    throw "Vite assets are not built. Run npm run build first."
}

$browser = Get-Command chromium -ErrorAction SilentlyContinue
if (-not $browser) {
    $browser = Get-Command chrome -ErrorAction SilentlyContinue
}
if (-not $browser) {
    $browser = Get-Command msedge -ErrorAction SilentlyContinue
}
if (-not $browser) {
    throw "Chromium, Chrome or Edge was not found in PATH."
}

New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null

$concepts = @(
    "01-editorial-library",
    "02-search-first",
    "03-artwork-gallery",
    "04-knowledge-graph",
    "05-calm-minimal",
    "06-music-magazine",
    "07-cinematic-dark",
    "08-utility-discovery",
    "09-community-shelves",
    "10-provider-first"
)

foreach ($concept in $concepts) {
    $url = "$BaseUrl/design-lab/$concept"
    $output = Join-Path $OutputDirectory "$concept.png"

    & $browser.Source `
        --headless `
        --disable-gpu `
        --hide-scrollbars `
        --window-size=1440,1100 `
        --screenshot="$output" `
        $url

    if (-not (Test-Path $output)) {
        throw "Screenshot failed for $concept"
    }

    Write-Host "[OK] $output"
}

Write-Host "Screenshots completed: $OutputDirectory"
