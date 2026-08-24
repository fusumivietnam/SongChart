param(
    [string]$Domain = 'docker.songchart.test'
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

function Write-Step([string]$Message) {
    Write-Host "[SongChart Docker-First Setup] $Message" -ForegroundColor Cyan
}

$Root = Split-Path -Parent $PSScriptRoot
$EnvExample = Join-Path $Root '.env.docker.example'
$EnvFile = Join-Path $Root '.env.docker'
$CertDir = Join-Path $Root '.certs'
$CertFile = Join-Path $CertDir "$Domain.pem"
$KeyFile = Join-Path $CertDir "$Domain-key.pem"
$HostsFile = "$env:SystemRoot\System32\drivers\etc\hosts"

Push-Location $Root
try {
    if ($null -eq (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker CLI is not available.'
    }

    & docker compose version
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker Compose v2 is required.'
    }

    if ($null -eq (Get-Command mkcert -ErrorAction SilentlyContinue)) {
        throw 'mkcert is not installed. Install it with Chocolatey or Scoop, then rerun this script.'
    }

    if (-not (Test-Path -LiteralPath $EnvFile)) {
        Copy-Item -LiteralPath $EnvExample -Destination $EnvFile
        Write-Step 'Created .env.docker from .env.docker.example.'
    }

    $envText = Get-Content -LiteralPath $EnvFile -Raw
    if ($envText -match '(?m)^APP_KEY=\s*$') {
        $bytes = New-Object byte[] 32
        $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
        try {
            $rng.GetBytes($bytes)
        } finally {
            $rng.Dispose()
        }
        $key = 'base64:' + [Convert]::ToBase64String($bytes)
        $envText = [regex]::Replace($envText, '(?m)^APP_KEY=\s*$', "APP_KEY=$key")
        Set-Content -LiteralPath $EnvFile -Value $envText -Encoding UTF8
        Write-Step 'Generated a Docker-local APP_KEY.'
    }

    New-Item -ItemType Directory -Force -Path $CertDir | Out-Null

    if (-not (Test-Path -LiteralPath $CertFile) -or -not (Test-Path -LiteralPath $KeyFile)) {
        Write-Step 'Installing/trusting the mkcert local CA...'
        & mkcert -install
        if ($LASTEXITCODE -ne 0) {
            throw 'mkcert -install failed.'
        }

        Write-Step "Generating trusted certificate for $Domain..."
        & mkcert -cert-file $CertFile -key-file $KeyFile $Domain localhost 127.0.0.1 ::1
        if ($LASTEXITCODE -ne 0) {
            throw 'mkcert certificate generation failed.'
        }
    }

    $hosts = Get-Content -LiteralPath $HostsFile -ErrorAction Stop
    $hostPattern = '^\s*127\.0\.0\.1\s+' + [regex]::Escape($Domain) + '(\s|$)'
    if (-not ($hosts | Where-Object { $_ -match $hostPattern })) {
        $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
        if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
            throw "Hosts entry is missing. Re-run this setup script in an Administrator PowerShell so it can add 127.0.0.1 $Domain."
        }

        Add-Content -LiteralPath $HostsFile -Value "`r`n127.0.0.1`t$Domain"
        & ipconfig /flushdns | Out-Null
        Write-Step "Added hosts entry: 127.0.0.1 $Domain"
    }

    Write-Step 'Ensuring Laravel runtime directories exist...'
    @(
        (Join-Path $Root 'bootstrap/cache'),
        (Join-Path $Root 'storage/framework/cache/data'),
        (Join-Path $Root 'storage/framework/sessions'),
        (Join-Path $Root 'storage/framework/views'),
        (Join-Path $Root 'storage/logs')
    ) | ForEach-Object {
        New-Item -ItemType Directory -Force -Path $_ | Out-Null
    }

    Write-Step 'Building Docker development image...'
    & docker compose -f compose.dev.yml build app
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker dev image build failed.'
    }

    Write-Step 'Starting PostgreSQL and Redis...'
    & docker compose -f compose.dev.yml up -d postgres redis
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker development infrastructure failed to start.'
    }

    Write-Step 'Installing locked PHP dependencies into Docker dev vendor volume...'
    & docker compose -f compose.dev.yml run --rm app composer install --no-interaction --prefer-dist --no-progress
    if ($LASTEXITCODE -ne 0) {
        throw 'Composer install failed in Docker dev.'
    }

    Write-Step 'Installing locked frontend dependencies into Docker dev node_modules volume...'
    & docker compose -f compose.dev.yml run --rm app npm ci --no-audit --no-fund
    if ($LASTEXITCODE -ne 0) {
        throw 'npm ci failed in Docker dev.'
    }

    Write-Step 'Building frontend assets...'
    & docker compose -f compose.dev.yml run --rm app npm run build
    if ($LASTEXITCODE -ne 0) {
        throw 'Frontend build failed in Docker dev.'
    }

    Write-Step 'Running migrations...'
    & docker compose -f compose.dev.yml run --rm app php artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker dev migrations failed.'
    }

    Write-Step 'Ensuring provider registry exists...'
    & docker compose -f compose.dev.yml run --rm app php artisan db:seed --class=Database\Seeders\ProviderRegistrySeeder --force
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker dev provider registry seeding failed.'
    }

    Write-Step 'Starting application, queue worker, and HTTPS proxy...'
    & docker compose -f compose.dev.yml up -d app queue caddy
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker development web stack failed to start.'
    }

    Write-Host ''
    Write-Host "SongChart primary Docker development environment is ready:" -ForegroundColor Green
    Write-Host "  HTTPS: https://$Domain`:8443"
    Write-Host "  HTTP redirect: http://$Domain`:8080"
    Write-Host ''
    & docker compose -f compose.dev.yml ps
} finally {
    Pop-Location
}
