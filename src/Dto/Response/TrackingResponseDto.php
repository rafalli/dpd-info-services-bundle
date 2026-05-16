<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Dto\Response;

final class TrackingResponseDto
{
    /**
     * @param DpdEventDto[] $events
     * @param string|null $confirmId
     */
    public function __construct(
        public array $events,
        public ?string $confirmId = null
    ) {}

    public function requiresConfirmation(): bool
    {
        return $this->confirmId !== null && count($this->events) > 0;
    }
}
