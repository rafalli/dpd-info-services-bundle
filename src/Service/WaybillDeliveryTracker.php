<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Service;

final class WaybillDeliveryTracker implements WaybillDeliveryTrackerInterface
{
    public function __construct(
        private readonly DpdInfoServiceClientInterface $dpdClient
    ) {}

    public function isDelivered(string $waybill): bool
    {
        return $this->getDeliveryDate($waybill) !== null;
    }

    public function getDeliveryDate(string $waybill): ?\DateTimeImmutable
    {
        $history = $this->dpdClient->getWaybillHistory($waybill);

        if ($history === []) {
            return null;
        }

        usort($history, static fn($a, $b) => $b->eventTime <=> $a->eventTime);

        foreach ($history as $event) {
            if ($event->eventCode->isDelivered()) {
                return $event->eventTime;
            }
        }

        return null;
    }
}
