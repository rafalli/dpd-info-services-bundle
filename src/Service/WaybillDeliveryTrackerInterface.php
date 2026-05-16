<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Service;

interface WaybillDeliveryTrackerInterface
{
    public function isDelivered(string $waybill): bool;

    public function getDeliveryDate(string $waybill): ?\DateTimeImmutable;
}
