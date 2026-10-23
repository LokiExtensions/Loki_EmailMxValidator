<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Stub;

use Spatie\Dns\Dns;
use Spatie\Dns\Support\Domain;

class DnsStub extends Dns
{
    private array $lookups = [];

    /**
     * @param array<string, array> $records Records per domain, unknown domains have no records
     */
    public function __construct(
        private readonly array $records = []
    ) {
        parent::__construct();
    }

    public function getRecords(Domain|string $search, int|string|array $types = DNS_ALL): array
    {
        $domain = (string)$search;
        $this->lookups[] = $domain;

        return $this->records[$domain] ?? [];
    }

    public function getLookups(): array
    {
        return $this->lookups;
    }
}
