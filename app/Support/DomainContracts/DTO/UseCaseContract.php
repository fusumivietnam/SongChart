<?php

declare(strict_types=1);

namespace App\Support\DomainContracts\DTO;

final readonly class UseCaseContract
{
    /**
     * @param  list<string>  $actors
     * @param  list<string>  $middleware
     * @param  list<EntityDataSurface>  $dataSurfaces
     * @param  list<SupportDataSurface>  $supportSurfaces
     * @param  array<string, string>  $routeParameters
     */
    public function __construct(
        public string $key,
        public string $method,
        public string $routeName,
        public string $uri,
        public array $middleware,
        public string $implementation,
        public array $actors,
        public array $dataSurfaces,
        public array $supportSurfaces,
        public array $routeParameters,
        public string $outputType,
        public bool $readOnly,
    ) {}
}
