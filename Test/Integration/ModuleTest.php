<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\ModuleList;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testModuleIsEnabled(): void
    {
        $moduleList = ObjectManager::getInstance()->get(ModuleList::class);
        $this->assertTrue($moduleList->has('Loki_EmailMxValidator'));
    }

    public function testModuleIsLoadedAfterLokiComponents(): void
    {
        $moduleList = ObjectManager::getInstance()->get(ModuleList::class);
        $moduleNames = $moduleList->getNames();

        $this->assertContains('Loki_Components', $moduleNames);
        $this->assertGreaterThan(
            array_search('Loki_Components', $moduleNames, true),
            array_search('Loki_EmailMxValidator', $moduleNames, true)
        );
    }
}
