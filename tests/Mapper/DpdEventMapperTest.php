<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Tests\Mapper;

use PHPUnit\Framework\TestCase;
use Rafalli\DpdInfoServicesBundle\Enum\EventCode;
use Rafalli\DpdInfoServicesBundle\Mapper\DpdEventMapper;

final class DpdEventMapperTest extends TestCase
{
    public function testMapCreatesDtoWithNewWaybillCorrectly(): void
    {
        // Arrange
        $mapper = new DpdEventMapper();

        $soapEventData = new \stdClass();
        $soapEventData->value = '0000110923567L';

        $soapEvent = new \stdClass();
        $soapEvent->businessCode = '230403';
        $soapEvent->waybill = '0001285761310U';
        $soapEvent->eventTime = '2021-01-08T11:18:52.122';
        $soapEvent->description = 'Zwrot przesyłki';
        $soapEvent->depot = '1322';
        $soapEvent->country = 'PL';
        $soapEvent->eventDataList = [$soapEventData];

        // Act
        $dto = $mapper->map($soapEvent);

        // Assert
        $this->assertSame('0001285761310U', $dto->waybill);
        $this->assertSame(EventCode::RETURN_DECISION, $dto->eventCode);
        $this->assertSame('0000110923567L', $dto->newWaybill);
        $this->assertSame('2021-01-08 11:18:52', $dto->eventTime->format('Y-m-d H:i:s'));
    }
}
