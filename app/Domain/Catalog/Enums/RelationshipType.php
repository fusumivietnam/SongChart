<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

enum RelationshipType: string
{
    case PerformedBy = 'performed_by';
    case HasMember = 'has_member';
    case MemberOf = 'member_of';
    case RecordingOf = 'recording_of';
    case WrittenBy = 'written_by';
    case VersionOf = 'version_of';
    case PartOf = 'part_of';
    case RelatedTo = 'related_to';
}
