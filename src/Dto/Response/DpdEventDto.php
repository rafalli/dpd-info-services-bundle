<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Dto\Response;

use Rafalli\DpdInfoServicesBundle\Enum\EventCode;

final class DpdEventDto
{
    public function __construct(
        public string $waybill,
        public EventCode $eventCode,
        public \DateTimeImmutable $eventTime,
        public string $description,
        public ?string $newWaybill = null,
        public string $depot = '',
        public string $country = 'PL'
    ) {}
}
