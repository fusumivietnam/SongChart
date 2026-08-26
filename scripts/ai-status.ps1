$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
$Root = Split-Path -Parent $PSScriptRoot
Push-Location $Root
try {
    $branchRaw = & git branch --show-current 2>$null
    $branch = if ($null -eq $branchRaw) { 'unknown' } else { ([string]$branchRaw).Trim() }
    if ([string]::IsNullOrWhiteSpace($branch)) { $branch = 'unknown' }
    $headRaw = & git rev-parse --short HEAD 2>$null
    $head = if ($null -eq $headRaw) { 'unknown' } else { ([string]$headRaw).Trim() }
    if ([string]::IsNullOrWhiteSpace($head)) { $head = 'unknown' }
    $changes = @(& git status --porcelain --untracked-files=all 2>$null)
    $modified = $changes.Count
    $upstreamRaw = & git rev-parse --abbrev-ref --symbolic-full-name '@{u}' 2>$null
    $upstream = if ($null -eq $upstreamRaw) { '' } else { ([string]$upstreamRaw).Trim() }
    $ahead = 0; $behind = 0
    if (-not [string]::IsNullOrWhiteSpace($upstream)) {
        $counts = ((& git rev-list --left-right --count "HEAD...$upstream" 2>$null) -split '\s+')
        if ($counts.Count -ge 2) { $ahead = [int]$counts[0]; $behind = [int]$counts[1] }
    }

    $candidatePath = Join-Path $Root 'candidate-verification.json'
    $stage = 'unknown'; $candidate = 'unknown'; $closureReady = 'unknown'; $verifiedAt = 'never'
    $passed = 0; $failed = 0; $notRun = 0
    if (Test-Path -LiteralPath $candidatePath) {
        $verification = Get-Content -LiteralPath $candidatePath -Raw | ConvertFrom-Json
        $stage = [string]$verification.stage
        $candidate = [string]$verification.candidate
        $closureReady = ([string]$verification.closure_ready).ToLowerInvariant()
        if ($null -ne $verification.verified_at) { $verifiedAt = [string]$verification.verified_at }
        foreach ($property in $verification.gates.PSObject.Properties) {
            switch ([string]$property.Value) {
                'passed' { $passed++ }
                'failed' { $failed++ }
                'not_run' { $notRun++ }
            }
        }
    }

    $checkpointPath = Join-Path $Root 'docs/project/DEVELOPMENT_STATE.md'
    $checkpointState = 'MISSING'; $checkpointDetail = 'DEVELOPMENT_STATE.md is missing'
    if (Test-Path -LiteralPath $checkpointPath) {
        $checkpointText = Get-Content -LiteralPath $checkpointPath -Raw
        $match = [regex]::Match($checkpointText, '(?ms)^## Current stage\s+.*?`(?<stage>[0-9]+(?:\.[0-9]+)*)')
        if ($match.Success -and $match.Groups['stage'].Value -eq $stage) {
            $checkpointState = 'SYNCED'; $checkpointDetail = 'operational checkpoint matches candidate stage'
        } else {
            $checkpointStage = if ($match.Success) { $match.Groups['stage'].Value } else { 'unknown' }
            $checkpointState = 'DRIFT'; $checkpointDetail = "checkpoint stage=$checkpointStage, candidate stage=$stage"
        }
    }

    $contextPath = Join-Path $Root 'docs/project/generated/project-context.json'
    $contextState = 'MISSING'
    $contextDetail = 'generated context file is missing'
    $contextSources = @(
        'composer.json','composer.lock','compose.dev.yml','compose.verify.yml','scripts/songchart.ps1',
        'docs/project/stack/runtime-environments.json','docs/project/domain/schema-ownership.json',
        'docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md','docs/project/engineering/ai-development-contract.json',
        'docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md','docs/project/engineering/verification-command-surface.json',
        'docs/project/engineering/verification-topology.json','docs/project/stack/release-pipeline-contract.json',
        'database/migrations','database/seeders'
    )
    if (Test-Path -LiteralPath $contextPath) {
        $dirtyArgs = @('status','--porcelain','--untracked-files=all','--') + $contextSources
        $sourceDirty = @(& git @dirtyArgs 2>$null)
        $contextDirty = @(& git status --porcelain --untracked-files=all -- 'docs/project/generated/project-context.json' 2>$null)
        $sourceLogArgs = @('log','-1','--format=%H','--') + $contextSources
        $sourceCommitRaw = & git @sourceLogArgs 2>$null
        $contextCommitRaw = & git log -1 --format=%H -- 'docs/project/generated/project-context.json' 2>$null
        $sourceCommit = if ($null -eq $sourceCommitRaw) { '' } else { ([string]$sourceCommitRaw).Trim() }
        $contextCommit = if ($null -eq $contextCommitRaw) { '' } else { ([string]$contextCommitRaw).Trim() }
        if ($sourceDirty.Count -gt 0 -or $contextDirty.Count -gt 0) {
            $contextState = 'STALE'; $contextDetail = 'registered context input or generated context has uncommitted changes'
        } elseif (-not [string]::IsNullOrWhiteSpace($sourceCommit) -and -not [string]::IsNullOrWhiteSpace($contextCommit)) {
            & git merge-base --is-ancestor $sourceCommit $contextCommit 2>$null
            if ($LASTEXITCODE -eq 0) {
                $contextState = 'FRESH'; $contextDetail = 'committed generated context covers latest registered source input'
            } else {
                $contextState = 'STALE'; $contextDetail = 'registered source input is newer than committed generated context'
            }
        }
    }

    $dockerAvailable = $false
    if ($null -ne (Get-Command docker -ErrorAction SilentlyContinue)) {
        & docker version *> $null
        $dockerAvailable = ($LASTEXITCODE -eq 0)
    }

    $devState = 'STOPPED'; $devUrl = 'https://docker.songchart.test:8443'
    if ($dockerAvailable -and (Test-Path -LiteralPath (Join-Path $Root '.env.docker'))) {
        $services = @(& docker compose -p songchart-dev -f (Join-Path $Root 'compose.dev.yml') ps --status running --services 2>$null)
        if ($services -contains 'app') { $devState = 'RUNNING' }
    }

    $demoState = 'UNCONFIGURED'; $demoUrl = 'http://127.0.0.1:8001'
    if (Test-Path -LiteralPath (Join-Path $Root '.env.demo')) {
        $demoState = 'STOPPED'
        if ($dockerAvailable) {
            $env:SONGCHART_DEMO_APP_URL = $demoUrl
            $env:SONGCHART_DEMO_PORT = '8001'
            $services = @(& docker compose -p songchart-demo -f (Join-Path $Root 'compose.demo.yml') ps --status running --services 2>$null)
            if ($services -contains 'app') { $demoState = 'RUNNING' }
        }
    }

    $codexVersion = 'not installed'; $geminiVersion = 'not installed'
    if ($null -ne (Get-Command codex -ErrorAction SilentlyContinue)) { $codexVersion = (& codex --version 2>$null | Select-Object -First 1) }
    if ($null -ne (Get-Command gemini -ErrorAction SilentlyContinue)) { $geminiVersion = (& gemini --version 2>$null | Select-Object -First 1) }

    $geminiAuth = 'not configured'
    $projectSettings = Join-Path $Root '.gemini/settings.json'
    $userSettings = if ($HOME) { Join-Path $HOME '.gemini/settings.json' } else { $null }
    $settingsPath = if (Test-Path -LiteralPath $projectSettings) { $projectSettings } elseif ($userSettings -and (Test-Path -LiteralPath $userSettings)) { $userSettings } else { $null }
    if ($settingsPath) {
        try {
            $settings = Get-Content -LiteralPath $settingsPath -Raw | ConvertFrom-Json
            $selectedType = [string]$settings.security.auth.selectedType
            switch ($selectedType) {
                'oauth-personal' { $geminiAuth = 'oauth-personal (Google account; AI Pro/Ultra eligible)' }
                'gemini-api-key' { $geminiAuth = 'gemini-api-key (separate API quota/billing)' }
                'USE_VERTEX_AI' { $geminiAuth = 'vertex-ai' }
                default { if (-not [string]::IsNullOrWhiteSpace($selectedType)) { $geminiAuth = $selectedType } else { $geminiAuth = 'not selected' } }
            }
        } catch {
            $geminiAuth = 'invalid settings.json'
        }
    } elseif (-not [string]::IsNullOrWhiteSpace($env:GEMINI_API_KEY)) {
        $geminiAuth = 'gemini-api-key via environment (separate API quota/billing)'
    }

    $next = 'Continue current stage with focused verification.'
    if ($modified -gt 0) { $next = 'Review/commit the current working tree before AI handoff.' }
    elseif ($checkpointState -ne 'SYNCED') { $next = 'Reconcile docs/project/DEVELOPMENT_STATE.md with the current candidate stage.' }
    elseif ($contextState -ne 'FRESH') { $next = 'Refresh repository context: songchart.bat context --refresh-source, then review/commit generated authority.' }
    elseif ($closureReady -eq 'true') { $next = 'Candidate evidence is closure-ready; proceed only through the governed closure workflow.' }

    Write-Host 'SongChart AI Session Status'
    Write-Host ''
    Write-Host ("{0,-14} {1}" -f 'Environment:', 'windows-native')
    Write-Host ("{0,-14} {1} / {2}" -f 'Stage:', $stage, $candidate)
    Write-Host ("{0,-14} {1} @ {2}" -f 'Branch:', $branch, $head)
    Write-Host ("{0,-14} modified={1} ahead={2} behind={3} upstream={4}" -f 'Git:', $modified, $ahead, $behind, $(if ($upstream) {$upstream} else {'none'}))
    Write-Host ("{0,-14} {1} - {2}" -f 'Checkpoint:', $checkpointState, $checkpointDetail)
    Write-Host ("{0,-14} {1} - {2}" -f 'Context:', $contextState, $contextDetail)
    Write-Host ("{0,-14} {1} - {2}" -f 'Dev:', $devState, $devUrl)
    Write-Host ("{0,-14} {1} - {2}" -f 'Demo:', $demoState, $demoUrl)
    Write-Host ("{0,-14} closure_ready={1} verified_at={2} gates(passed={3} failed={4} not_run={5})" -f 'Verification:', $closureReady, $verifiedAt, $passed, $failed, $notRun)
    Write-Host ("{0,-14} {1}" -f 'Codex:', $codexVersion)
    Write-Host ("{0,-14} {1}" -f 'Gemini:', $geminiVersion)
    Write-Host ("{0,-14} {1}" -f 'Gemini auth:', $geminiAuth)
    Write-Host ''
    Write-Host "Next: $next"
} finally {
    Pop-Location
}
