<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Service;

use Rafalli\DpdInfoServicesBundle\Dto\Request\RetrievalContextDto;
use Rafalli\DpdInfoServicesBundle\Dto\Response\TrackingResponseDto;
use Rafalli\DpdInfoServicesBundle\Exception\DpdApiException;
use Rafalli\DpdInfoServicesBundle\Mapper\DpdEventMapper;
use SoapClient;
use SoapFault;

final class DpdInfoServiceClient implements DpdInfoServiceClientInterface
{
    /** @var SoapClient|object|null */
    private ?object $client = null;

    public function __construct(
        private readonly string $wsdlUrl,
        private readonly string $channel,
        private readonly string $username,
        private readonly string $password,
        private readonly DpdEventMapper $eventMapper,
        ?object $client = null
    ) {
        $this->client = $client;
    }

    public function fetchNewEvents(RetrievalContextDto $context): TrackingResponseDto
    {
        try {
            $response = $this->getSoapClient()->getEventsForCustomerV4([
                'authDataV1' => $this->getAuthData(),
                'limit' => $context->limit,
                'language' => $context->language,
            ]);

            $result = $response->return ?? null;
            if (!$result || !isset($result->eventsList)) {
                return new TrackingResponseDto(events: []);
            }

            $events = is_array($result->eventsList) ? $result->eventsList : [$result->eventsList];

            $dtos = array_map($this->eventMapper->map(...), $events);

            return new TrackingResponseDto(
                events: $dtos,
                confirmId: $result->confirmId ?? null
            );
        } catch (SoapFault $e) {
            throw new DpdApiException("Failed to fetch events: " . $e->getMessage(), 0, $e);
        }
    }

    public function getWaybillHistory(string $waybill): array
    {
        try {
            $response = $this->getSoapClient()->getEventsForWaybillV1([
                'authDataV1' => $this->getAuthData(),
                'waybill' => $waybill,
                'eventsSelectType' => 'ALL',
                'language' => 'PL',
            ]);

            $events = $response->return->eventsList ?? null;

            if ($events === null) {
                return [];
            }

            $eventsArray = is_array($events) ? $events : [$events];

            return array_map($this->eventMapper->map(...), $eventsArray);
        } catch (SoapFault $e) {
            throw new DpdApiException(sprintf("Failed to fetch history for waybill %s: %s", $waybill, $e->getMessage()));
        }
    }

    public function markAsProcessed(string $confirmId): void
    {
        try {
            $this->getSoapClient()->markEventsAsProcessedV1([
                'authDataV1' => $this->getAuthData(),
                'confirmId' => $confirmId,
            ]);
        } catch (SoapFault $e) {
            throw new DpdApiException(sprintf("Failed to confirm session %s: %s", $confirmId, $e->getMessage()));
        }
    }

    /**
     * @return array{channel: string, login: string, password: string}
     */
    private function getAuthData(): array
    {
        return [
            'channel' => $this->channel,
            'login' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * @return \SoapClient
     */
    private function getSoapClient(): object
    {
        if (null === $this->client) {
            $this->client = new SoapClient($this->wsdlUrl, [
                'trace' => 1,
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_BOTH,
            ]);
        }
        /** @var \SoapClient $client */
        $client = $this->client;

        return $client;
    }
}
