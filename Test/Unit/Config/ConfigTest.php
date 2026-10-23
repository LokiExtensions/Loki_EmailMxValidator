<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Unit\Config;

use Loki\EmailMxValidator\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    #[DataProvider('getConfigValues')]
    public function testEnableMxValidationForEmail(mixed $configValue, bool $expectedResult): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('loki_components/validators/enable_mx_validation_for_email', ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $config = new Config($scopeConfig);
        $this->assertSame($expectedResult, $config->enableMxValidationForEmail());
    }

    public static function getConfigValues(): array
    {
        return [
            ['1', true],
            [1, true],
            ['0', false],
            [0, false],
            ['', false],
            [null, false],
        ];
    }
}
