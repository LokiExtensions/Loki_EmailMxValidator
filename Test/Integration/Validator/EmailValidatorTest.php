<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Validator;

use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use PHPUnit\Framework\TestCase;
use Loki\Components\Validator\EmailValidator;

#[AppArea('frontend')]
class EmailValidatorTest extends TestCase
{
    public function testWithNoValue(): void
    {
        $validator = ObjectManager::getInstance()->get(EmailValidator::class);
        $actualResult = $validator->validate(null);
        $error = array_pop($actualResult);
        $this->assertStringContainsString('is not valid', $error, var_export($actualResult, true));
    }

    /**
     * @param string $email
     * @param true|string $expectedResult
     * @return void
     * @dataProvider getValues
     */
    #[Config('loki_components/validators/enable_mx_validation_for_email', true)]
    public function testWithVariousValues(string $email, true|string $expectedResult): void
    {
        $validator = ObjectManager::getInstance()->get(EmailValidator::class);
        $actualResult = $validator->validate($email);
        if (true === $expectedResult) {
            $this->assertTrue($actualResult);
            return;
        }

        $error = array_pop($actualResult);
        $this->assertStringContainsString($expectedResult, $error, var_export($actualResult, true));
    }

    public function getValues(): array
    {
        return [
            ['jane@example.com', true],
            ['jane@example.comcom', 'is not reachable for mail'],
        ];
    }
}
