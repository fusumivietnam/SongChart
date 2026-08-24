<?php

declare(strict_types=1);

namespace App\Enums;

enum Capability: string
{
    case AccessAdmin = 'access-admin';
    case ManageExtensions = 'manage-extensions';
    case ManageProviders = 'manage-providers';
    case ManageCatalog = 'manage-catalog';
    case ManageIdentityConflicts = 'manage-identity-conflicts';
    case ViewOperations = 'view-operations';
    case ViewAudit = 'view-audit';
    case ViewHorizon = 'viewHorizon';
    case ViewPulse = 'viewPulse';
    case ManageUserRoles = 'manage-user-roles';
    case ManageUserActivation = 'manage-user-activation';
}
