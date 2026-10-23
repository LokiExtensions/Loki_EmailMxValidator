<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Test\Unit\Plugin;

use Loki\Components\Validator\EmailValidator;
use Loki\EmailMxValidator\Config\Config;
use Loki\EmailMxValidator\Plugin\ValidateEmailMxRecordPlugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Dns\Dns;

class ValidateEmailMxRecordPluginTest extends TestCase
{
    public function testSkipsLookupWhenDisabled(): void
    {
        $dns = $this->createMock(Dns::class);
        $dns->expects($this->never())->method('getRecords');

        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(false), $dns);

        $this->assertTrue($plugin->afterValidate($this->getSubject(), true, 'jane@example.invalid'));
    }

    #[DataProvider('getEmptyValues')]
    public function testSkipsLookupWithEmptyValue(mixed $value): void
    {
        $dns = $this->createMock(Dns::class);
        $dns->expects($this->never())->method('getRecords');

        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $this->assertTrue($plugin->afterValidate($this->getSubject(), true, $value));
    }

    public static function getEmptyValues(): array
    {
        return [[null], [''], ['0']];
    }

    public function testKeepsErrorOfOriginalValidator(): void
    {
        $dns = $this->createMock(Dns::class);
        $dns->expects($this->never())->method('getRecords');

        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $result = $plugin->afterValidate($this->getSubject(), ['Invalid email address'], 'foo bar@yireo.com');
        $this->assertSame(['Invalid email address'], $result);
    }

    #[DataProvider('getValuesWithoutDomain')]
    public function testReturnsErrorWithoutDomain(string $email): void
    {
        $dns = $this->createMock(Dns::class);
        $dns->expects($this->never())->method('getRecords');

        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $this->assertSame(
            ['E-mail "'.$email.'" is not valid'],
            $plugin->afterValidate($this->getSubject(), true, $email)
        );
    }

    public static function getValuesWithoutDomain(): array
    {
        return [['jane.yireo.com'], ['jane@']];
    }

    public function testReturnsTrueWhenMxRecordsAreFound(): void
    {
        $dns = $this->getDnsMock('yireo.com', [['mx-record']]);
        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $this->assertTrue($plugin->afterValidate($this->getSubject(), true, 'jane@yireo.com'));
    }

    public function testReturnsErrorWhenNoMxRecordsAreFound(): void
    {
        $dns = $this->getDnsMock('example.invalid', []);
        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $this->assertSame(
            ['Domain "example.invalid" is not reachable for mail'],
            $plugin->afterValidate($this->getSubject(), true, 'jane@example.invalid')
        );
    }

    public function testUsesLastPartAsDomain(): void
    {
        $dns = $this->getDnsMock('yireo.com', [['mx-record']]);
        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $this->assertTrue($plugin->afterValidate($this->getSubject(), true, '"jane@home"@yireo.com'));
    }

    public function testUsesDefaultRetriesAndTimeout(): void
    {
        $dns = $this->getDnsMock('yireo.com', [['mx-record']], 1, 5);
        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns);

        $this->assertTrue($plugin->afterValidate($this->getSubject(), true, 'jane@yireo.com'));
    }

    public function testUsesCustomRetriesAndTimeout(): void
    {
        $dns = $this->getDnsMock('yireo.com', [['mx-record']], 3, 10);
        $plugin = new ValidateEmailMxRecordPlugin($this->getConfig(true), $dns, 3, 10);

        $this->assertTrue($plugin->afterValidate($this->getSubject(), true, 'jane@yireo.com'));
    }

    private function getDnsMock(
        string $expectedDomain,
        array $records,
        ?int $expectedRetries = null,
        ?int $expectedTimeout = null
    ): Dns&MockObject {
        $dns = $this->createMock(Dns::class);

        $setRetries = $dns->expects($this->once())->method('setRetries');
        if ($expectedRetries !== null) {
            $setRetries->with($expectedRetries);
        }
        $setRetries->willReturnSelf();

        $setTimeout = $dns->expects($this->once())->method('setTimeout');
        if ($expectedTimeout !== null) {
            $setTimeout->with($expectedTimeout);
        }
        $setTimeout->willReturnSelf();

        $dns->expects($this->once())
            ->method('getRecords')
            ->with($expectedDomain, DNS_MX)
            ->willReturn($records);

        return $dns;
    }

    private function getConfig(bool $enabled): Config
    {
        $config = $this->createMock(Config::class);
        $config->method('enableMxValidationForEmail')->willReturn($enabled);

        return $config;
    }

    private function getSubject(): EmailValidator
    {
        return $this->createMock(EmailValidator::class);
    }
}
