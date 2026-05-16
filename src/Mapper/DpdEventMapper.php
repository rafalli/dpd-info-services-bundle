<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Mapper;

use Rafalli\DpdInfoServicesBundle\Dto\Response\DpdEventDto;
use Rafalli\DpdInfoServicesBundle\Enum\EventCode;

final class DpdEventMapper
{
    private const WAYBILL_CHANGE_CODES = ['230402', '230403', '230408'];

    public function map(object $event): DpdEventDto
    {
        $businessCode = $event->businessCode ?? '';
        $newWaybill = $this->extractNewWaybill($event, $businessCode);

        try {
            $eventTime = new \DateTimeImmutable($event->eventTime ?? 'now');
        } catch (\Exception $e) {
            $eventTime = new \DateTimeImmutable('now');
        }

        return new DpdEventDto(
            waybill: $event->waybill ?? '',
            eventCode: EventCode::tryFrom($businessCode) ?? EventCode::UNKNOWN,
            eventTime: $eventTime,
            description: $event->description ?? '',
            newWaybill: $newWaybill,
            depot: $event->depot ?? '',
            country: $event->country ?? 'PL'
        );
    }

    private function extractNewWaybill(object $event, string $businessCode): ?string
    {
        if (!in_array($businessCode, self::WAYBILL_CHANGE_CODES, true) || !isset($event->eventDataList)) {
            return null;
        }

        $dataList = is_array($event->eventDataList) ? $event->eventDataList : [$event->eventDataList];

        foreach ($dataList as $dataItem) {
            if (!empty($dataItem->value) && is_string($dataItem->value) && strlen($dataItem->value) > 5) {
                return $dataItem->value;
            }
        }

        return null;
    }
}
