<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EventCode: string implements TranslatableInterface
{
    case DELIVERED = '190101';
    case DELIVERED_COD = '190104';
    case RETURN_DECISION = '230403';
    case RETURN_RENUMBERED = '230408';
    case REDIRECTED = '230402';
    case PICKED_UP = '040101';
    case UNKNOWN = 'UNKNOWN';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::DELIVERED,
            self::DELIVERED_COD => true,
            default => false,
        };
    }

    public function isDelivered(): bool
    {
        return match ($this) {
            self::DELIVERED,
            self::DELIVERED_COD => true,
            default => false,
        };
    }

    public function getTranslationKey(): string
    {
        return sprintf('rafalli_dpd_info_services.event.%s', $this->value);
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->getTranslationKey(), [], 'rafalli_dpd_info_services', $locale);
    }
}
