<?php

declare(strict_types=1);

it('keeps external AI design guidance subordinate to SongChart UI authority', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = json_decode(
        (string) file_get_contents($root.'/docs/ui/ai-design-harness.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $designAuthority = (string) file_get_contents($root.'/docs/ui/DESIGN_AUTHORITY.md');
    $skill = (string) file_get_contents($root.'/'.$contract['project_skill']);

    expect($contract['status'])->toBe('advisory')
        ->and($contract['owner'])->toBe('docs/ui/DESIGN_AUTHORITY.md')
        ->and($contract['provider']['repository'])->toBe('pbakaus/impeccable')
        ->and($contract['provider']['git_submodule'])->toBeFalse()
        ->and($contract['policy']['external_guidance_is_release_authoritative'])->toBeFalse()
        ->and($contract['policy']['external_detector_output_is_canonical_evidence'])->toBeFalse()
        ->and($contract['policy']['may_override_songchart_design_contract'])->toBeFalse()
        ->and($contract['policy']['may_change_domain_or_provider_semantics'])->toBeFalse()
        ->and($contract['policy']['may_fabricate_metrics_or_ranking'])->toBeFalse()
        ->and($contract['policy']['must_use_songchart_verification'])->toBeTrue()
        ->and($designAuthority)->toContain('docs/ui/AI_DESIGN_HARNESS.md')
        ->and($skill)->toContain('SongChart authority always wins')
        ->and($skill)->toContain('Never fabricate popularity, recommendations, ranking or metrics.')
        ->and(is_file($root.'/PRODUCT.md'))->toBeFalse()
        ->and(is_file($root.'/DESIGN.md'))->toBeFalse();
});
