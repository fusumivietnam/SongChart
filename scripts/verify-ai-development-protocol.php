<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$contractPath = $root.'/docs/project/engineering/ai-development-contract.json';
$protocolPath = $root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md';

if (! is_file($contractPath) || ! is_file($protocolPath)) {
    $errors[] = 'AI development protocol authority files are missing.';
} else {
    $contract = json_decode((string) file_get_contents($contractPath), true, 512, JSON_THROW_ON_ERROR);
    $protocol = (string) file_get_contents($protocolPath);

    foreach ([
        './songchart impact <planned-path',
        './songchart impact --diff',
        './songchart reconcile',
        './songchart audit',
        'composer stage:verify',
        'composer canonical:verify',
        'composer release:package',
        'Do not add another verifier',
        'one writer per overlapping source surface',
        'Runtime/generated artifact ownership',
        'mutation envelope',
        'Post-closure seal',
        'non-interactive',
    ] as $needle) {
        if (! str_contains($protocol, $needle)) {
            $errors[] = "AI development protocol is missing required workflow invariant [{$needle}].";
        }
    }

    if (($contract['documentation_rule'] ?? null) === null) {
        $errors[] = 'AI development contract must define the thin-bootstrap documentation rule.';
    }

    $expectedPhases = [
        'research_orient',
        'preflight_authority_consistency',
        'planned_impact',
        'implement',
        'post_diff_impact',
        'reconcile_generated_authority',
        'mutation_boundary_check',
        'collect_all_audit',
        'focused_verification',
        'candidate',
        'canonical',
        'post_closure_seal',
        'delivery',
    ];
    if (($contract['workflow']['ordered_phases'] ?? null) !== $expectedPhases) {
        $errors[] = 'AI development contract workflow phases drifted from the governed golden path.';
    }

    if (($contract['workflow']['mutation_envelopes'] ?? null) !== ['read-only', 'generated-only', 'runtime-only', 'closure']) {
        $errors[] = 'AI development contract must define the four governed command mutation envelopes.';
    }
}

foreach (['AGENTS.md', 'CLAUDE.md', 'GEMINI.md'] as $bootstrap) {
    $source = (string) file_get_contents($root.'/'.$bootstrap);
    $lines = preg_split('/\R/', trim($source)) ?: [];

    if (count($lines) > 30) {
        $errors[] = "{$bootstrap} must remain a thin bootstrap (maximum 30 lines).";
    }
    foreach ([
        'PROJECT_AUTHORITY.md',
        'docs/project/generated/development-state.json',
        'AI_DEVELOPMENT_PROTOCOL.md',
        './songchart ai status --json',
        'PREPARE → CHECK → canonical CLOSE → Ready',
    ] as $needle) {
        if (! str_contains($source, $needle)) {
            $errors[] = "{$bootstrap} is missing bootstrap pointer [{$needle}].";
        }
    }

    foreach (['Stage 06', 'Stage 07', 'Stage 08', 'Stage 09', 'Stage 10', 'Stage 16.8.2'] as $duplicatedHistoricalRule) {
        if (str_contains($source, $duplicatedHistoricalRule)) {
            $errors[] = "{$bootstrap} duplicates historical implementation rules [{$duplicatedHistoricalRule}].";
        }
    }
}

if (! is_file($root.'/scripts/ai-status.sh')) {
    $errors[] = 'AI session bootstrap is missing [scripts/ai-status.sh].';
}

$songchart = (string) file_get_contents($root.'/songchart');
foreach ([
    'ai status' => 'scripts/ai-status.sh',
    'ai doctor' => 'scripts/ai-doctor.sh',
    'impact)' => 'scripts/resolve-repository-impact.php',
    'reconcile)' => 'reconcile_authority',
    'audit)' => 'scripts/run-workflow-audit.php',
] as $surface => $implementation) {
    if (! str_contains($songchart, $surface) || ! str_contains($songchart, $implementation)) {
        $errors[] = "Linux songchart CLI must expose [{$surface}] through [{$implementation}].";
    }
}

$controlPlanePath = $root.'/docs/project/engineering/ai-control-plane-contract.json';
if (! is_file($controlPlanePath)) {
    $errors[] = 'AI control-plane authority is missing.';
} else {
    $controlPlane = json_decode((string) file_get_contents($controlPlanePath), true, 512, JSON_THROW_ON_ERROR);
    foreach ([
        'orientation',
        'new_surface_admission',
        'hand_written_fanout_budget',
        'code_placement',
        'skill_registry',
        'intent_routes',
        'dependency_updates',
        'repository_hygiene',
        'projection_policy',
    ] as $requiredSection) {
        if (! is_array($controlPlane[$requiredSection] ?? null)) {
            $errors[] = "AI control-plane contract is missing section [{$requiredSection}].";
        }
    }

    if (($controlPlane['skill_registry']['canonical_root'] ?? null) !== '.agents/skills') {
        $errors[] = 'AI control-plane skill registry must keep .agents/skills as canonical project-skill source.';
    }

    if (($controlPlane['new_surface_admission']['default_decision'] ?? null) !== 'extend_existing') {
        $errors[] = 'AI control-plane new-surface default must remain extend_existing.';
    }

    if (($controlPlane['dependency_updates']['automation'] ?? null) !== 'github-dependabot') {
        $errors[] = 'AI control-plane dependency updater must remain GitHub Dependabot unless a replacement is explicitly admitted.';
    }
}

foreach (['scripts/ai-brief.php', 'scripts/sync-agent-skills.php'] as $controlPlaneScript) {
    if (! is_file($root.'/'.$controlPlaneScript)) {
        $errors[] = "AI control-plane implementation is missing [{$controlPlaneScript}].";
    }
}

$governanceSkill = $root.'/.agents/skills/governance/SKILL.md';
if (! is_file($governanceSkill)) {
    $errors[] = 'Canonical SongChart governance skill is missing.';
} else {
    $canonicalGovernanceSkill = (string) file_get_contents($governanceSkill);
    foreach (['.claude/skills/governance/SKILL.md', '.github/skills/governance/SKILL.md'] as $projectionPath) {
        $absoluteProjection = $root.'/'.$projectionPath;
        if (! is_file($absoluteProjection)) {
            $errors[] = "Governance skill projection is missing [{$projectionPath}].";
        } elseif ((string) file_get_contents($absoluteProjection) !== $canonicalGovernanceSkill) {
            $errors[] = "Governance skill projection drifted from canonical source [{$projectionPath}].";
        }
    }
}

$dependabotPath = $root.'/.github/dependabot.yml';
if (! is_file($dependabotPath)) {
    $errors[] = 'Governed dependency update proposal configuration [.github/dependabot.yml] is missing.';
} else {
    $dependabot = (string) file_get_contents($dependabotPath);
    foreach (['package-ecosystem: composer', 'package-ecosystem: npm', 'package-ecosystem: github-actions', 'package-ecosystem: docker'] as $ecosystem) {
        if (! str_contains($dependabot, $ecosystem)) {
            $errors[] = "Dependabot configuration is missing governed ecosystem [{$ecosystem}].";
        }
    }
}

foreach (['songchart.bat', 'scripts/songchart.ps1', 'scripts/ai-status.ps1'] as $retiredWindowsEntrypoint) {
    if (is_file($root.'/'.$retiredWindowsEntrypoint)) {
        $errors[] = "Retired native Windows AI/development entrypoint must be removed [{$retiredWindowsEntrypoint}].";
    }
}

$gitignore = (string) file_get_contents($root.'/.gitignore');
if (! str_contains($gitignore, '/.gemini/')) {
    $errors[] = 'Repository .gitignore must exclude local Gemini CLI state [.gemini/].';
}

$candidate = json_decode((string) file_get_contents($root.'/candidate-verification.json'), true, 512, JSON_THROW_ON_ERROR);
$stagePlanPath = $root.'/docs/project/engineering/stage-plan.json';
$derivedStatePath = $root.'/docs/project/generated/development-state.json';
$compatibilityPointerPath = $root.'/docs/project/DEVELOPMENT_STATE.md';

if (! is_file($stagePlanPath) || ! is_file($derivedStatePath) || ! is_file($compatibilityPointerPath)) {
    $errors[] = 'Derived project-state authority files are missing.';
} else {
    $stagePlan = json_decode((string) file_get_contents($stagePlanPath), true, 512, JSON_THROW_ON_ERROR);
    $derivedState = json_decode((string) file_get_contents($derivedStatePath), true, 512, JSON_THROW_ON_ERROR);
    $compatibilityPointer = (string) file_get_contents($compatibilityPointerPath);
    $candidateStage = isset($candidate['stage']) ? (string) $candidate['stage'] : null;
    $derivedStage = $derivedState['current_stage']['id'] ?? null;

    if (($derivedState['generated_from_repository'] ?? false) !== true) {
        $errors[] = 'Generated development state must declare generated_from_repository=true.';
    }
    if (! is_string($derivedStage) || $derivedStage === '') {
        $errors[] = 'Generated development state current_stage.id is missing.';
    }
    if (is_string($derivedStage) && $candidateStage !== null && $derivedStage !== $candidateStage) {
        $errors[] = "Generated development state stage [{$derivedStage}] does not match candidate stage [{$candidateStage}].";
    }
    if (($stagePlan['current_stage']['id'] ?? null) !== $derivedStage) {
        $errors[] = 'Generated development state must project the stage-plan current stage exactly.';
    }
    if (! str_contains($compatibilityPointer, 'compatibility pointer')
        || ! str_contains($compatibilityPointer, 'docs/project/generated/development-state.json')) {
        $errors[] = 'DEVELOPMENT_STATE.md must remain a compatibility pointer to generated repository state.';
    }

    $workLease = $derivedState['work_lease_policy'] ?? null;
    if (! is_array($workLease)
        || ($workLease['authority'] ?? null) !== 'live Git branch + GitHub pull request'
        || ! str_contains(strtolower((string) ($workLease['rule'] ?? '')), 'resum')) {
        $errors[] = 'Generated development state must preserve the live Git/GitHub work-lease policy.';
    }

    $taskContract = $derivedState['current_stage']['task_contract'] ?? null;
    if (! is_string($taskContract) || $taskContract === '' || ! is_file($root.'/'.$taskContract)) {
        $errors[] = 'Generated development state must resolve an existing current-stage task contract.';
    }
}

$statusSource = is_file($root.'/scripts/ai-status.sh') ? (string) file_get_contents($root.'/scripts/ai-status.sh') : '';
$projectStateSource = is_file($root.'/scripts/project-state.php') ? (string) file_get_contents($root.'/scripts/project-state.php') : '';
foreach (['project-state.php', '--json', 'resume that exact work lease'] as $needle) {
    if (! str_contains($statusSource, $needle)) {
        $errors[] = "AI session status must expose derived/live work-lease semantics [{$needle}].";
    }
}
foreach (["'source' => 'git-and-github-runtime'", "'head_sha' =>", "'pr_number' =>", "'resume_rule' =>"] as $needle) {
    if (! str_contains($projectStateSource, $needle)) {
        $errors[] = "Project-state compiler is missing live work-lease field [{$needle}].";
    }
}

$skillsRoot = $root.'/.agents/skills';
if (! is_dir($skillsRoot)) {
    $errors[] = 'Repository-local AI skills directory [.agents/skills] is missing.';
} else {
    $skillDirectories = glob($skillsRoot.'/*', GLOB_ONLYDIR) ?: [];
    sort($skillDirectories);

    foreach ($skillDirectories as $skillDirectory) {
        $expectedName = basename($skillDirectory);
        $skillPath = $skillDirectory.'/SKILL.md';
        if (! is_file($skillPath)) {
            $errors[] = "AI skill [{$expectedName}] is missing SKILL.md.";

            continue;
        }

        $source = (string) file_get_contents($skillPath);
        if (! str_starts_with($source, "---\n")) {
            $errors[] = "AI skill [{$expectedName}] must start with YAML frontmatter at byte 0.";

            continue;
        }

        if (preg_match('/\A---\n(?<frontmatter>.*?)\n---(?:\n|\z)/s', $source, $matches) !== 1) {
            $errors[] = "AI skill [{$expectedName}] has invalid YAML frontmatter delimiters.";

            continue;
        }

        $frontmatter = (string) ($matches['frontmatter'] ?? '');
        $fields = [];
        $nestedParent = null;

        foreach (preg_split('/\R/', $frontmatter) ?: [] as $line) {
            if (trim($line) === '' || str_starts_with(ltrim($line), '#')) {
                continue;
            }

            if (preg_match('/^(?<key>[A-Za-z0-9_-]+):\s*(?<value>.*)$/', $line, $fieldMatch) === 1) {
                $key = (string) $fieldMatch['key'];
                $value = trim((string) $fieldMatch['value'], " \t\n\r\0\x0B\"'");

                $fields[$key] = $value;
                $nestedParent = $value === '' ? $key : null;

                continue;
            }

            if (preg_match('/^  (?<key>[A-Za-z0-9_-]+):\s*(?<value>.*)$/', $line, $nestedMatch) === 1
                && $nestedParent === 'metadata') {
                continue;
            }

            $errors[] = "AI skill [{$expectedName}] frontmatter contains unsupported YAML structure [{$line}].";

            continue 2;
        }
        $allowed = ['name', 'description', 'license', 'allowed-tools', 'metadata'];
        foreach (array_keys($fields) as $key) {
            if (! in_array($key, $allowed, true)) {
                $errors[] = "AI skill [{$expectedName}] frontmatter uses unsupported key [{$key}].";
            }
        }

        $name = $fields['name'] ?? '';
        $description = $fields['description'] ?? '';
        if ($name === '') {
            $errors[] = "AI skill [{$expectedName}] frontmatter is missing name.";
        } elseif ($name !== $expectedName) {
            $errors[] = "AI skill directory [{$expectedName}] must match frontmatter name [{$name}].";
        } elseif (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $name) !== 1 || strlen($name) > 64) {
            $errors[] = "AI skill [{$expectedName}] name must be lowercase hyphen-case and at most 64 characters.";
        }

        if ($description === '') {
            $errors[] = "AI skill [{$expectedName}] frontmatter is missing a non-empty description.";
        }

        $body = preg_replace('/\A---\n.*?\n---(?:\n|\z)/s', '', $source, 1);
        if (trim((string) $body) === '') {
            $errors[] = "AI skill [{$expectedName}] must contain an actionable Markdown body.";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "AI development protocol verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "AI development protocol verification passed.\n");
