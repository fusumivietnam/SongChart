param(
    [switch]$SkipBuild,
    [switch]$SkipMigrate
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Root = Split-Path -Parent $PSScriptRoot
$Compose = Join-Path $Root 'compose.dev.yml'
$EnvFile = Join-Path $Root '.env.docker'
$ProviderSeeder = Join-Path $Root 'database/seeders/ProviderRegistrySeeder.php'
$Domain = 'docker.songchart.test'
$HttpsUrl = "https://$Domain`:8443"
$ExpectedDbUser = 'songchart_docker'
$ExpectedDbPassword = 'songchart_docker_only'
$HttpsResolve = "$Domain`:8443`:127.0.0.1"

function Step([string]$Message) { Write-Host "[SongChart dev ready] $Message" -ForegroundColor Cyan }
function Run-Compose([string[]]$ComposeArgs) {
    if ($ComposeArgs.Count -eq 0) { throw 'Run-Compose requires a Docker Compose command.' }
    & docker compose -f $Compose @ComposeArgs
    if ($LASTEXITCODE -ne 0) { throw "Docker development command failed: $($ComposeArgs -join ' ')" }
}

function Show-DockerDiagnostics {
    Write-Host '[SongChart dev ready] Docker service status:' -ForegroundColor Yellow
    & docker compose -f $Compose ps
    Write-Host '[SongChart dev ready] Recent Caddy logs:' -ForegroundColor Yellow
    & docker compose -f $Compose logs --no-color --tail 120 caddy
    Write-Host '[SongChart dev ready] Recent app logs:' -ForegroundColor Yellow
    & docker compose -f $Compose logs --no-color --tail 120 app
}

function Test-ServiceRunning([string]$Service) {
    $id = (& docker compose -f $Compose ps -q $Service 2>$null | Select-Object -First 1)
    if ([string]::IsNullOrWhiteSpace($id)) { return $false }
    $state = (& docker inspect -f '{{.State.Status}}' $id 2>$null | Select-Object -First 1)
    return $state -eq 'running'
}

function Wait-Https([string]$Url, [string]$FailureMessage, [int]$Attempts = 12) {
    for ($Attempt = 1; $Attempt -le $Attempts; $Attempt++) {
        # Compose publishes HTTPS on 127.0.0.1:8443. --resolve preserves the governed
        # TLS hostname/SNI while avoiding stale or externally overridden Windows DNS.
        & curl.exe --fail --silent --show-error --ssl-no-revoke --resolve $HttpsResolve --connect-timeout 2 --max-time 5 $Url | Out-Null
        if ($LASTEXITCODE -eq 0) { return }

        if (($Attempt % 3) -eq 0) {
            if (-not (Test-ServiceRunning 'app') -or -not (Test-ServiceRunning 'caddy')) {
                Show-DockerDiagnostics
                throw "${FailureMessage}; app or caddy is not running: $Url"
            }
        }

        if ($Attempt -lt $Attempts) {
            Write-Host "[SongChart dev ready] HTTPS not ready yet ($Attempt/$Attempts): $Url" -ForegroundColor DarkYellow
            Start-Sleep -Seconds 2
        }
    }

    Show-DockerDiagnostics
    throw "${FailureMessage}: $Url"
}

Push-Location $Root
try {
    if ($null -eq (Get-Command docker -ErrorAction SilentlyContinue)) { throw 'Docker CLI is required.' }
    & docker compose version | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'Docker Compose v2 is required.' }
    if (-not (Test-Path -LiteralPath $EnvFile)) { throw '.env.docker is missing. Run: .\songchart.bat dev setup' }

    if (-not $SkipBuild) {
        Step 'Building/updating the Docker development app image...'
        Run-Compose @('build','app')
    }

    Step 'Starting PostgreSQL and Redis...'
    Run-Compose @('up','-d','postgres','redis')

    Step 'Reconciling the persistent PostgreSQL role password with compose.dev.yml...'
    # Local socket authentication inside the official PostgreSQL container remains the recovery path
    # when an existing named volume was initialized with an older host password.
    Run-Compose @('exec','-T','postgres','psql','-v','ON_ERROR_STOP=1','-U',$ExpectedDbUser,'-d','postgres','-c',"ALTER ROLE songchart_docker WITH PASSWORD '$ExpectedDbPassword';")

    Step 'Ensuring locked PHP dependencies exist in the Docker vendor volume...'
    & docker compose -f $Compose run --rm app sh -lc 'test -f vendor/autoload.php'
    if ($LASTEXITCODE -ne 0) {
        Run-Compose @('run','--rm','app','composer','install','--no-interaction','--prefer-dist','--no-progress')
    }

    Step 'Clearing Laravel runtime caches with current Docker environment...'
    Run-Compose @('run','--rm','app','php','artisan','optimize:clear')

    if (-not $SkipMigrate) {
        Step 'Applying development database migrations...'
        Run-Compose @('run','--rm','app','php','artisan','migrate','--force')
        Step 'Ensuring provider registry seed data exists...'
        if (-not (Test-Path -LiteralPath $ProviderSeeder)) { throw 'ProviderRegistrySeeder.php is missing from database/seeders.' }
        Run-Compose @('run','--rm','app','php','artisan','db:seed','--class=Database\Seeders\ProviderRegistrySeeder','--force')
    }

    Step 'Recreating app/queue/Caddy so long-running processes receive the reconciled environment...'
    Run-Compose @('up','-d','--force-recreate','app','queue','caddy')
    Run-Compose @('ps')

    Step 'Waiting for Laravel health endpoint through trusted HTTPS...'
    Wait-Https "$HttpsUrl/up" 'HTTPS health smoke failed'

    Step 'Checking the real home route so database-backed session/auth failures are detected...'
    Wait-Https "$HttpsUrl/" 'Live application smoke failed' 5

    Write-Host "[PASS] Docker development runtime is live: $HttpsUrl" -ForegroundColor Green
} finally {
    Pop-Location
}
