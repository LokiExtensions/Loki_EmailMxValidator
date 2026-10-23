<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Integration\Validator;

use Loki\Components\Component\ComponentInterface;
use Loki\Components\Test\Integration\Traits\AssertComponentErrors;
use Loki\Components\Test\Integration\Traits\GetComponent;
use Loki\Components\Util\Controller\RepositoryDispatcher;
use Loki\Components\Validator\Validator;
use Loki\EmailMxValidator\Test\Integration\Traits\ReplaceDns;
use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
#[AppIsolation(true)]
#[Config('loki_components/validators/enable_mx_validation_for_email', 1, 'store', 'default')]
class ComponentValidationTest extends TestCase
{
    use GetComponent;
    use AssertComponentErrors;
    use ReplaceDns;

    private const COMPONENT_NAME = 'loki-email-mx-validator.test.email';
    private const HANDLE = 'loki_email_mx_validator_test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->replaceDns(['yireo.com' => [['type' => 'MX']]]);
    }

    protected function tearDown(): void
    {
        $this->restoreDns();
        parent::tearDown();
    }

    public function testTestComponentHasEmailValidator(): void
    {
        $this->assertContains('email', $this->getTestComponent()->getValidators());
    }

    public function testDomainWithoutMxRecordAddsLocalError(): void
    {
        $component = $this->getTestComponent();
        $validator = ObjectManager::getInstance()->get(Validator::class);

        $this->assertFalse($validator->validate($component, 'jane@example.invalid'));
        $this->assertComponentHasError($component, 'Domain "example.invalid" is not reachable for mail');
    }

    public function testDomainWithMxRecordAddsNoErrors(): void
    {
        $component = $this->getTestComponent();
        $validator = ObjectManager::getInstance()->get(Validator::class);

        $this->assertTrue($validator->validate($component, 'jane@yireo.com'));
        $this->assertComponentHasNoErrors($component);
    }

    public function testInvalidValueIsNotSaved(): void
    {
        $component = $this->getTestComponent();
        $component->getRepository()->saveValue('previous@yireo.com');

        $dispatcher = ObjectManager::getInstance()->get(RepositoryDispatcher::class);
        $dispatcher->dispatch($component, 'jane@example.invalid');

        $this->assertSame('previous@yireo.com', $component->getRepository()->getValue());
        $this->assertComponentHasError($component, 'is not reachable for mail');
    }

    public function testValidValueIsSaved(): void
    {
        $component = $this->getTestComponent();
        $component->getRepository()->saveValue(null);

        $dispatcher = ObjectManager::getInstance()->get(RepositoryDispatcher::class);
        $dispatcher->dispatch($component, 'jane@yireo.com');

        $this->assertSame('jane@yireo.com', $component->getRepository()->getValue());
        $this->assertComponentHasNoErrors($component);
    }

    private function getTestComponent(): ComponentInterface
    {
        return $this->getComponentWithBlock(self::COMPONENT_NAME, [self::HANDLE]);
    }
}
