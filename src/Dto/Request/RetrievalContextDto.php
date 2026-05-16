<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\Dto\Request;

final class RetrievalContextDto
{
    public function __construct(
        public int $limit = 100,
        public string $language = 'PL',
    ) {
        if ($this->limit < 1 || $this->limit > 500) {
            throw new \InvalidArgumentException('Event limit must be between 1 and 500.');
        }
    }
}
