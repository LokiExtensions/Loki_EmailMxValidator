<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Plugin;

use Loki\Components\Validator\EmailValidator;
use Loki\EmailMxValidator\Plugin\ValidateEmailMxRecordPlugin;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

class ValidateEmailMxRecordPluginTest extends TestCase
{
    private const PLUGIN_NAME = 'Loki_EmailMxValidator::validateEmailMxRecord';

    #[AppArea(Area::AREA_FRONTEND)]
    public function testPluginIsRegisteredInFrontend(): void
    {
        $this->assertPluginIsRegistered();
    }

    #[AppArea(Area::AREA_ADMINHTML)]
    public function testPluginIsRegisteredInAdminhtml(): void
    {
        $this->assertPluginIsRegistered();
    }

    public function testEmailValidatorIsIntercepted(): void
    {
        $emailValidator = ObjectManager::getInstance()->get(EmailValidator::class);
        $this->assertInstanceOf(InterceptorInterface::class, $emailValidator);
    }

    private function assertPluginIsRegistered(): void
    {
        $pluginList = ObjectManager::getInstance()->get(PluginList::class);
        $pluginInfo = $pluginList->get(EmailValidator::class, []);

        $this->assertArrayHasKey(self::PLUGIN_NAME, $pluginInfo);
        $this->assertSame(ValidateEmailMxRecordPlugin::class, $pluginInfo[self::PLUGIN_NAME]['instance']);
    }
}
