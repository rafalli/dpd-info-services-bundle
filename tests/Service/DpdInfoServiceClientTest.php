<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rafalli\DpdInfoServicesBundle\Dto\Request\RetrievalContextDto;
use Rafalli\DpdInfoServicesBundle\Dto\Response\DpdEventDto;
use Rafalli\DpdInfoServicesBundle\Dto\Response\TrackingResponseDto;
use Rafalli\DpdInfoServicesBundle\Enum\EventCode;
use Rafalli\DpdInfoServicesBundle\Exception\DpdApiException;
use Rafalli\DpdInfoServicesBundle\Mapper\DpdEventMapper;
use Rafalli\DpdInfoServicesBundle\Service\DpdInfoServiceClient;
use SoapFault;
use stdClass;

interface SoapClientInterface
{
    /** @param array<string, mixed> $parameters */
    public function getEventsForCustomerV4(array $parameters): stdClass;

    /** @param array<string, mixed> $parameters */
    public function markEventsAsProcessedV1(array $parameters): void;
}

final class DpdInfoServiceClientTest extends TestCase
{
    private DpdInfoServiceClient $service;
    private SoapClientInterface&MockObject $soapClientMock;

    protected function setUp(): void
    {
        $this->soapClientMock = $this->createMock(SoapClientInterface::class);

        $this->service = new DpdInfoServiceClient(
            wsdlUrl: 'https://test.wsdl',
            channel: '1234',
            username: 'test_user',
            password: 'test_password',
            eventMapper: new DpdEventMapper(),
            client: $this->soapClientMock
        );
    }

    public function testFetchNewEventsSuccessfullyMapsData(): void
    {
        $eventData1 = new stdClass();
        $eventData1->value = 'parcel_3';

        $event1 = new stdClass();
        $event1->waybill = '21112014111444';
        $event1->businessCode = '230403';
        $event1->eventTime = '2014-11-26T11:39:39';
        $event1->description = 'Zwrot przesyłki';
        $event1->eventDataList = [$eventData1];

        $event2 = new stdClass();
        $event2->waybill = '2111201411127K';
        $event2->businessCode = '190101';
        $event2->eventTime = '2014-11-25T13:49:00';
        $event2->description = 'Doręczono';

        $mockResponse = new stdClass();
        $mockResponse->return = new stdClass();
        $mockResponse->return->eventsList = [$event1, $event2];
        $mockResponse->return->confirmId = 'CONFIRM_999888';

        $this->soapClientMock
            ->expects($this->once())
            ->method('getEventsForCustomerV4')
            ->willReturn($mockResponse);

        $context = new RetrievalContextDto(limit: 50);
        $responseDto = $this->service->fetchNewEvents($context);

        $this->assertInstanceOf(TrackingResponseDto::class, $responseDto);
        $this->assertSame('CONFIRM_999888', $responseDto->confirmId);
        $this->assertCount(2, $responseDto->events);
        $this->assertContainsOnlyInstancesOf(DpdEventDto::class, $responseDto->events);

        $dto1 = $responseDto->events[0];
        $this->assertSame('21112014111444', $dto1->waybill);
        $this->assertSame(EventCode::RETURN_DECISION, $dto1->eventCode);
        $this->assertSame('2014-11-26 11:39:39', $dto1->eventTime->format('Y-m-d H:i:s'));
        $this->assertSame('parcel_3', $dto1->newWaybill);

        $dto2 = $responseDto->events[1];
        $this->assertSame('2111201411127K', $dto2->waybill);
        $this->assertSame(EventCode::DELIVERED, $dto2->eventCode);
        $this->assertNull($dto2->newWaybill);
        $this->assertTrue($dto2->eventCode->isTerminal());
    }

    public function testFetchNewEventsHandlesEmptyResponse(): void
    {
        $mockResponse = new stdClass();
        $mockResponse->return = new stdClass();

        $this->soapClientMock
            ->method('getEventsForCustomerV4')
            ->willReturn($mockResponse);

        $responseDto = $this->service->fetchNewEvents(new RetrievalContextDto());

        $this->assertInstanceOf(TrackingResponseDto::class, $responseDto);
        $this->assertEmpty($responseDto->events);
        $this->assertNull($responseDto->confirmId);
    }

    public function testFetchNewEventsThrowsDpdApiExceptionOnSoapFault(): void
    {
        $this->soapClientMock
            ->method('getEventsForCustomerV4')
            ->willThrowException(new SoapFault('Server', 'Service Unavailable'));

        $this->expectException(DpdApiException::class);
        $this->expectExceptionMessage('Failed to fetch events: Service Unavailable');

        $this->service->fetchNewEvents(new RetrievalContextDto());
    }

    public function testMarkAsProcessedCallsSoapClientCorrectly(): void
    {
        $confirmId = 'CONFIRM_12345';

        $this->soapClientMock
            ->expects($this->once())
            ->method('markEventsAsProcessedV1')
            ->with(
                $this->callback(function (array $args) use ($confirmId) {
                    return isset($args['confirmId'])
                        && $args['confirmId'] === $confirmId
                        && isset($args['authDataV1']['channel'])
                        && $args['authDataV1']['channel'] === '1234';
                })
            );

        $this->service->markAsProcessed($confirmId);
    }
}
