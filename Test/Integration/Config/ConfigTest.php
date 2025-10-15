<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Config;

use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\TestCase;
use Loki\EmailMxValidator\Config\Config;

class ConfigTest extends TestCase
{
    #[ConfigFixture('loki_components/general/debug', 1)]
    #[ConfigFixture('loki_components/validators/enable_mx_validation_for_email', 1)]
    public function testMxValidationForEmailEnabled()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $this->assertTrue($config->enableMxValidationForEmail());
    }

    #[ConfigFixture('loki_components/validators/enable_mx_validation_for_email', 0)]
    public function testMxValidationForEmailDisabled()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $this->assertFalse($config->enableMxValidationForEmail());
    }
}
