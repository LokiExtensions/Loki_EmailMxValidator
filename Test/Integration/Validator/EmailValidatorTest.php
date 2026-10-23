<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Validator;

use Loki\EmailMxValidator\Test\Integration\Traits\ReplaceDns;
use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Loki\Components\Validator\EmailValidator;

#[AppArea('frontend')]
class EmailValidatorTest extends TestCase
{
    use ReplaceDns;

    protected function tearDown(): void
    {
        $this->restoreDns();
        parent::tearDown();
    }

    public function testWithNoValue(): void
    {
        $dnsStub = $this->replaceDns([]);
        $validator = ObjectManager::getInstance()->get(EmailValidator::class);

        $this->assertTrue($validator->validate(null));
        $this->assertTrue($validator->validate(''));
        $this->assertSame([], $dnsStub->getLookups());
    }

    #[Config('loki_components/validators/enable_mx_validation_for_email', 1, 'store', 'default')]
    #[DataProvider('getValues')]
    public function testWithVariousValues(string $email, true|string $expectedResult, array $expectedLookups): void
    {
        $dnsStub = $this->replaceDns(['yireo.com' => [['type' => 'MX']]]);

        $validator = ObjectManager::getInstance()->get(EmailValidator::class);
        $actualResult = $validator->validate($email);
        $this->assertSame($expectedLookups, $dnsStub->getLookups(), 'DNS lookups');

        if (true === $expectedResult) {
            $this->assertTrue($actualResult);
            return;
        }

        $this->assertIsArray($actualResult, var_export($actualResult, true));
        $error = (string)array_pop($actualResult);
        $this->assertStringContainsString($expectedResult, $error, var_export($actualResult, true));
    }

    public static function getValues(): array
    {
        return [
            ['jane@yireo.com', true, ['yireo.com']],
            ['jane@example.invalid', 'Domain "example.invalid" is not reachable for mail', ['example.invalid']],
            ['foo bar@yireo.com', 'Invalid email address', []],
            ['jane.yireo.com', 'Invalid email address', []],
        ];
    }

    #[Config('loki_components/validators/enable_mx_validation_for_email', 0, 'store', 'default')]
    public function testWithMxValidationDisabled(): void
    {
        $dnsStub = $this->replaceDns([]);

        $validator = ObjectManager::getInstance()->get(EmailValidator::class);
        $this->assertTrue($validator->validate('jane@example.invalid'));
        $this->assertSame([], $dnsStub->getLookups());
    }

    #[Config('loki_components/validators/enable_mx_validation_for_email', 1, 'store', 'default')]
    public function testWithRealDnsLookup(): void
    {
        $validator = ObjectManager::getInstance()->get(EmailValidator::class);

        $this->assertTrue($validator->validate('jane@yireo.com'));

        $result = $validator->validate('jane@example.invalid');
        $this->assertIsArray($result);
        $this->assertStringContainsString('is not reachable for mail', (string)array_pop($result));
    }
}
