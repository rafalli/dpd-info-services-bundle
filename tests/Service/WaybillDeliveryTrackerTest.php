<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rafalli\DpdInfoServicesBundle\Dto\Response\DpdEventDto;
use Rafalli\DpdInfoServicesBundle\Enum\EventCode;
use Rafalli\DpdInfoServicesBundle\Service\DpdInfoServiceClientInterface;
use Rafalli\DpdInfoServicesBundle\Service\WaybillDeliveryTracker;

final class WaybillDeliveryTrackerTest extends TestCase
{
    private DpdInfoServiceClientInterface&MockObject $dpdClientMock;
    private WaybillDeliveryTracker $tracker;

    protected function setUp(): void
    {
        $this->dpdClientMock = $this->createMock(DpdInfoServiceClientInterface::class);
        $this->tracker = new WaybillDeliveryTracker($this->dpdClientMock);
    }

    public function testReturnsNullAndFalseWhenHistoryIsEmpty(): void
    {
        $waybill = '0000000000000U';

        $this->dpdClientMock
            ->method('getWaybillHistory')
            ->with($waybill)
            ->willReturn([]);

        $this->assertFalse($this->tracker->isDelivered($waybill));
        $this->assertNull($this->tracker->getDeliveryDate($waybill));
    }

    public function testReturnsNullAndFalseWhenNotDelivered(): void
    {
        $waybill = '0000000000000U';

        $history = [
            $this->createEventDto(EventCode::PICKED_UP, '2023-10-01 10:00:00'),
            $this->createEventDto(EventCode::REDIRECTED, '2023-10-02 12:00:00'),
        ];

        $this->dpdClientMock
            ->method('getWaybillHistory')
            ->willReturn($history);

        $this->assertFalse($this->tracker->isDelivered($waybill));
        $this->assertNull($this->tracker->getDeliveryDate($waybill));
    }

    public function testReturnsCorrectDateForStandardDelivery(): void
    {
        $waybill = '0000000000000U';

        $history = [
            $this->createEventDto(EventCode::PICKED_UP, '2023-10-01 10:00:00'),
            $this->createEventDto(EventCode::DELIVERED, '2023-10-03 14:30:00'),
        ];

        $this->dpdClientMock
            ->method('getWaybillHistory')
            ->willReturn($history);

        $this->assertTrue($this->tracker->isDelivered($waybill));
        $this->assertSame('2023-10-03 14:30:00', $this->tracker->getDeliveryDate($waybill)?->format('Y-m-d H:i:s'));
    }

    public function testReturnsCorrectDateForCodDelivery(): void
    {
        $waybill = '0000000000000U';

        $history = [
            $this->createEventDto(EventCode::DELIVERED_COD, '2023-10-04 09:15:00'),
        ];

        $this->dpdClientMock
            ->method('getWaybillHistory')
            ->willReturn($history);

        $this->assertTrue($this->tracker->isDelivered($waybill));
        $this->assertSame('2023-10-04 09:15:00', $this->tracker->getDeliveryDate($waybill)?->format('Y-m-d H:i:s'));
    }

    public function testReturnsNewestDeliveryDateIfMultipleDeliveryEventsExistOutOfOrder(): void
    {
        $waybill = '0000000000000U';

        $history = [
            $this->createEventDto(EventCode::DELIVERED, '2023-10-05 10:00:00'), // Starsze doręczenie
            $this->createEventDto(EventCode::PICKED_UP, '2023-10-01 10:00:00'),
            $this->createEventDto(EventCode::DELIVERED, '2023-10-05 12:00:00'), // Najnowsze doręczenie (powinno zostać wybrane)
            $this->createEventDto(EventCode::REDIRECTED, '2023-10-02 12:00:00'),
        ];

        $this->dpdClientMock
            ->method('getWaybillHistory')
            ->willReturn($history);

        $this->assertTrue($this->tracker->isDelivered($waybill));
        $this->assertSame('2023-10-05 12:00:00', $this->tracker->getDeliveryDate($waybill)?->format('Y-m-d H:i:s'));
    }

    private function createEventDto(EventCode $code, string $timeString): DpdEventDto
    {
        return new DpdEventDto(
            waybill: 'TEST_WAYBILL',
            eventCode: $code,
            eventTime: new \DateTimeImmutable($timeString),
            description: 'Test Event',
            newWaybill: null,
            depot: 'TEST_DEPOT',
            country: 'PL'
        );
    }
}
