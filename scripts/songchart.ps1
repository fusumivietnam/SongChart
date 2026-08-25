param(
    [Parameter(Position=0)] [string]$Command = 'help',
    [Parameter(Position=1)] [string]$Subcommand = '',
    [Parameter(ValueFromRemainingArguments=$true)] [string[]]$Arguments
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
$Root = Split-Path -Parent $PSScriptRoot
$DevCompose = Join-Path $Root 'compose.dev.yml'
$VerifyCompose = Join-Path $Root 'compose.verify.yml'
$DevProject = 'songchart-dev'
$CanonicalProject = 'songchart-verify'

function Require-Docker {
    if ($null -eq (Get-Command docker -ErrorAction SilentlyContinue)) { throw 'Docker CLI is required. Start Docker Desktop with WSL2 integration.' }
    & docker compose version | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'Docker Compose v2 is required.' }
}
function Dev {
    param([Parameter(Mandatory=$true)] [AllowEmptyCollection()] [string[]]$ComposeArgs)
    $ComposeArgs = @($ComposeArgs | Where-Object { -not [string]::IsNullOrWhiteSpace($_) })
    if ($ComposeArgs.Count -eq 0) { throw 'Docker development command requires at least one Compose argument.' }
    & docker compose -p $DevProject -f $DevCompose @ComposeArgs
    if ($LASTEXITCODE -ne 0) { throw "Docker development command failed: $($ComposeArgs -join ' ')" }
}
function VerifyCompose {
    param([Parameter(Mandatory=$true)] [AllowEmptyCollection()] [string[]]$ComposeArgs)
    $ComposeArgs = @($ComposeArgs | Where-Object { -not [string]::IsNullOrWhiteSpace($_) })
    if ($ComposeArgs.Count -eq 0) { throw 'Docker verification command requires at least one Compose argument.' }
    & docker compose -p $CanonicalProject -f $VerifyCompose @ComposeArgs
    if ($LASTEXITCODE -ne 0) { throw "Docker verification command failed: $($ComposeArgs -join ' ')" }
}
function Ensure-DevConfig {
    if (-not (Test-Path -LiteralPath (Join-Path $Root '.env.docker'))) { throw '.env.docker is missing. Run: songchart dev setup' }
}
function Show-Help {
@'
SongChart Docker-first development CLI

  songchart dev setup          Bootstrap env/cert/dependencies/migrations and start dev stack
  songchart dev up             Start app, queue, PostgreSQL, Redis and Caddy
  songchart dev down           Stop dev stack without deleting persistent volumes
  songchart dev status         Show dev containers
  songchart dev logs [service] Follow dev logs
  songchart dev shell          Open a shell in the app container
  songchart dev ready          Repair/bootstrap dev DB, restart runtime, and smoke-test HTTPS
  songchart dev test [test]    Run one focused Docker test (Unit/Architecture skip DB bootstrap)
  songchart dev cycle [test]   ready -> focused Docker test -> live smoke (fast dev loop)
  songchart artisan <args...>  Run Artisan inside Docker dev
  songchart composer <args...> Run Composer inside Docker dev
  songchart npm <args...>      Run npm inside Docker dev
  songchart test               Run isolated Docker stage closure (quality + PostgreSQL + build)
  songchart context [--json]  Read generated repository + PostgreSQL runtime facts
  songchart verify             Run canonical Docker verification and candidate evidence

Primary runtime requires Docker Desktop/WSL2. Host PHP/Composer/Node/PostgreSQL/Redis are not used.
'@ | Write-Host
}

Push-Location $Root
try {
    if ($Command -eq 'help' -or $Command -eq '--help' -or $Command -eq '-h') { Show-Help; exit 0 }
    Require-Docker

    switch ($Command.ToLowerInvariant()) {
        'dev' {
            switch ($Subcommand.ToLowerInvariant()) {
                'setup' { & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'setup-docker-dev.ps1') @Arguments; if ($LASTEXITCODE -ne 0) { throw 'Docker development setup failed.' } }
                'up' { Ensure-DevConfig; Dev -ComposeArgs @('up','-d','postgres','redis','app','queue','caddy'); Dev -ComposeArgs @('ps'); Write-Host 'SongChart: https://docker.songchart.test:8443' -ForegroundColor Green }
                'down' { Dev -ComposeArgs @('down','--remove-orphans') }
                'status' { Dev -ComposeArgs @('ps') }
                'logs' { Ensure-DevConfig; $logArgs=@('logs','--follow','--tail','200'); if ($Arguments.Count -gt 0) { $logArgs += $Arguments }; Dev -ComposeArgs $logArgs }
                'shell' { Ensure-DevConfig; Dev -ComposeArgs @('exec','app','bash') }
                'ready' { Ensure-DevConfig; & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'prepare-docker-dev.ps1') @Arguments; if ($LASTEXITCODE -ne 0) { throw 'Docker development readiness failed.' } }
                'test' {
                    Ensure-DevConfig
                    $focused = if ($Arguments.Count -gt 0) { $Arguments[0] } else { 'tests/Feature/EntityDetailSystemTest.php' }
                    $project = 'songchart-focused'
                    try {
                        & docker compose -p $project -f $VerifyCompose build verify
                        if ($LASTEXITCODE -ne 0) { throw 'Focused verification image build failed.' }
                        $isDatabaseTest = -not ($focused -like 'tests/Unit/*' -or $focused -like 'tests/Architecture/*')
                        $testCommand = if ($isDatabaseTest) {
                            "composer install --no-interaction --prefer-dist --no-progress && php scripts/run-database-tests.php postgres --prepare-schema $focused"
                        } else {
                            "composer install --no-interaction --prefer-dist --no-progress && php artisan test $focused"
                        }
                        & docker compose -p $project -f $VerifyCompose run --rm verify bash -lc $testCommand
                        if ($LASTEXITCODE -ne 0) { throw "Focused Docker test failed: $focused" }
                    } finally {
                        & docker compose -p $project -f $VerifyCompose down --remove-orphans
                    }
                }
                'cycle' {
                    Ensure-DevConfig
                    & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'prepare-docker-dev.ps1') -SkipBuild
                    if ($LASTEXITCODE -ne 0) { throw 'Docker development readiness failed.' }
                    $focused = if ($Arguments.Count -gt 0) { $Arguments[0] } else { 'tests/Feature/EntityDetailSystemTest.php' }
                    & powershell -NoProfile -ExecutionPolicy Bypass -File $PSCommandPath dev test $focused
                    if ($LASTEXITCODE -ne 0) { throw "Focused Docker test failed: $focused" }
                    & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'prepare-docker-dev.ps1') -SkipBuild -SkipMigrate
                    if ($LASTEXITCODE -ne 0) { throw 'Post-test live smoke failed.' }
                    Write-Host '[PASS] Fast Docker development cycle passed. Canonical verify was intentionally NOT run.' -ForegroundColor Green
                    Write-Host '[NEXT WHEN CLOSING A STAGE] .\songchart.bat verify' -ForegroundColor DarkGray
                }
                default { throw 'Usage: songchart dev [setup|up|down|status|logs|shell|ready|test|cycle]' }
            }
        }
        'artisan' { Ensure-DevConfig; $a=@('exec','app','php','artisan'); $a += @($Subcommand); $a += $Arguments; Dev -ComposeArgs $a }
        'composer' { Ensure-DevConfig; $a=@('exec','app','composer'); $a += @($Subcommand); $a += $Arguments; Dev -ComposeArgs $a }
        'npm' { Ensure-DevConfig; $a=@('exec','app','npm'); $a += @($Subcommand); $a += $Arguments; Dev -ComposeArgs $a }
        'context' {
            Ensure-DevConfig
            $contextArgs = @()
            if (-not [string]::IsNullOrWhiteSpace($Subcommand)) { $contextArgs += $Subcommand }
            if ($null -ne $Arguments) {
                $contextArgs += @($Arguments | Where-Object { -not [string]::IsNullOrWhiteSpace($_) })
            }
            if ($contextArgs -contains '--refresh-source') {
                $contextArgs = @($contextArgs | Where-Object { $_ -ne '--refresh-source' })
                $contextArgs += '--write-source'
            }
            $a = @('run','--rm','app','php','scripts/project-context.php')
            if ($contextArgs.Count -gt 0) { $a += $contextArgs }
            Dev -ComposeArgs $a
        }        'test' {
            try {
                VerifyCompose -ComposeArgs @('down','--remove-orphans')
                VerifyCompose -ComposeArgs @('build','verify')
                VerifyCompose -ComposeArgs @('run','--rm','verify','bash','scripts/docker-stage-verify.sh')
            } finally {
                & docker compose -f $VerifyCompose down --remove-orphans
            }
        }
        'verify' { $verifyArgs=@(); if ($Subcommand -ne '') { $verifyArgs += $Subcommand }; $verifyArgs += $Arguments; Write-Host "[SongChart verify] Canonical Compose project: $CanonicalProject" -ForegroundColor DarkGray; & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'verify-canonical.ps1') @verifyArgs; if ($LASTEXITCODE -ne 0) { throw 'Canonical verification failed.' } }
        default { Show-Help; throw "Unknown command: $Command" }
    }
} finally { Pop-Location }
