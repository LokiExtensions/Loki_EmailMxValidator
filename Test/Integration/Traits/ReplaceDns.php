<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Traits;

use Loki\EmailMxValidator\Plugin\ValidateEmailMxRecordPlugin;
use Loki\EmailMxValidator\Test\Integration\Stub\DnsStub;
use Magento\Framework\App\ObjectManager;
use ReflectionProperty;
use Spatie\Dns\Dns;

/**
 * Swaps the DNS client of the shared plugin instance (which is also the instance cached by the PluginList)
 */
trait ReplaceDns
{
    private ?Dns $originalDns = null;

    protected function replaceDns(array $records): DnsStub
    {
        $dnsStub = new DnsStub($records);
        $property = $this->getDnsProperty();
        $plugin = $this->getPlugin();

        if (null === $this->originalDns) {
            $this->originalDns = $property->getValue($plugin);
        }

        $property->setValue($plugin, $dnsStub);

        return $dnsStub;
    }

    protected function restoreDns(): void
    {
        if (null === $this->originalDns) {
            return;
        }

        $this->getDnsProperty()->setValue($this->getPlugin(), $this->originalDns);
        $this->originalDns = null;
    }

    private function getPlugin(): ValidateEmailMxRecordPlugin
    {
        return ObjectManager::getInstance()->get(ValidateEmailMxRecordPlugin::class);
    }

    private function getDnsProperty(): ReflectionProperty
    {
        return new ReflectionProperty(ValidateEmailMxRecordPlugin::class, 'dns');
    }
}
