<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Service;

use Rafalli\DpdInfoServicesBundle\Dto\Request\RetrievalContextDto;
use Rafalli\DpdInfoServicesBundle\Dto\Response\DpdEventDto;
use Rafalli\DpdInfoServicesBundle\Dto\Response\TrackingResponseDto;

interface DpdInfoServiceClientInterface
{
    public function fetchNewEvents(RetrievalContextDto $context): TrackingResponseDto;

    public function markAsProcessed(string $confirmId): void;

    /**
     * @return DpdEventDto[]
     */
    public function getWaybillHistory(string $waybill): array;
}
