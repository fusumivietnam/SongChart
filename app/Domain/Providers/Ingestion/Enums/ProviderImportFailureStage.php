<?php

declare(strict_types=1);

namespace App\Domain\Providers\Ingestion\Enums;

enum ProviderImportFailureStage: string
{
    case Request = 'request';
    case Payload = 'payload';
    case Normalization = 'normalization';
    case Validation = 'validation';
    case Matching = 'matching';
    case Mutation = 'mutation';
}
