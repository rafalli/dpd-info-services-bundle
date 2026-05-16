# DPD InfoServices Symfony Bundle

![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)
![Symfony Version](https://img.shields.io/badge/Symfony-6.4%2B-black)

## Installation

Install the package via Composer:

```bash
composer require rafalli/dpd-info-services-bundle
```

## Configuration

1. Create a configuration file `rafalli_dpd_info_services.yaml` in your Symfony project:

```yaml
rafalli_dpd_info_services:
    wsdl_url: '%env(DPD_WSDL_URL)%'
    channel: '%env(int:DPD_CHANNEL)%'
    username: '%env(DPD_USERNAME)%'
    password: '%env(DPD_PASSWORD)%'
```
2. Add your DPD credentials to your `.env` file:

```
DPD_WSDL_URL="https://dpdinfoservices.dpd.com.pl/DPDInfoServicesObjEventsService/DPDInfoServicesObjEvents?wsdl"
DPD_CHANNEL=12345
DPD_USERNAME=your_login
DPD_PASSWORD=your_password    
```

## Usage

### Basic Interaction

Inject the `DpdInfoServiceClientInterface` and use the following pattern to handle tracking updates:

```php

use Rafalli\DpdInfoServicesBundle\Dto\Request\RetrievalContextDto;
use Rafalli\DpdInfoServicesBundle\Dto\Response\DpdEventDto;
use Rafalli\DpdInfoServicesBundle\Exception\DpdApiException;
use Rafalli\DpdInfoServicesBundle\Service\DpdInfoServiceClientInterface;

// Inject the interface via constructor
public function __construct(
    private readonly DpdInfoServiceClientInterface $dpdClient
) {}

// Fetch and process events
try {
    $response = $this->dpdClient->fetchNewEvents(new RetrievalContextDto(limit: 100));

    foreach ($response->events as $event) {
        // $event is an instance of DpdEventDto
        // Access data: $event->waybill, $event->eventCode, $event->newWaybill
    }

    // Confirm processing (Required by DPD API to clear the queue)
    if ($response->requiresConfirmation()) {
        $this->dpdClient->markAsProcessed($response->confirmId);
    }
} catch (DpdApiException $e) {
    // Handle connection or API errors
}
```

### Checking a Single Waybill

To check the history of a specific parcel without affecting the global event queue:


```php
$history = $this->dpdClient->getWaybillHistory('0000000000000U');
```
### Delivery Tracking Utility

If you only need to know whether a parcel was successfully delivered (and exactly when), you can use the built-in `WaybillDeliveryTrackerInterface` which provides clean and easy-to-use domain logic.

```php
use Rafalli\DpdInfoServicesBundle\Service\WaybillDeliveryTrackerInterface;

public function __construct(
    private readonly WaybillDeliveryTrackerInterface $deliveryTracker
) {}

public function checkStatus(string $waybill): void
{
    // Returns true if the parcel is marked as delivered (including COD deliveries)
    if ($this->deliveryTracker->isDelivered($waybill)) {

    } 
}

public function checkDeliveryDate(string $waybill): void
{
    // Returns the DateTimeImmutable object of the exact delivery moment
    $date = $this->deliveryTracker->getDeliveryDate($waybill);
}
```

## Translations

The bundle provides an `EventCode` Enum that integrates with Symfony's Translation component. You can render translated descriptions directly in Twig:

```twig
{# Translation Domain: rafalli_dpd_info_services #}
{{ eventDto.eventCode.translationKey | trans({}, 'rafalli_dpd_info_services') }}
```

## Error Handling

All SOAP-related errors or validation issues throw a `Rafalli\DpdInfoServicesBundle\Exception\DpdApiException`. This exception encapsulates the original `SoapFault`, making it easy to debug while keeping your application domain clean.

## License

MIT License