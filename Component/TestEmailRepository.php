<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Component;

use Loki\Components\Component\ComponentRepository;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Repository of the test component used by the functional tests (layout handle loki_email_mx_validator_test)
 */
class TestEmailRepository extends ComponentRepository
{
    private const SESSION_KEY = 'loki_email_mx_validator_test_email';

    public function __construct(
        private readonly SessionManagerInterface $session
    ) {
    }

    public function getValue(): mixed
    {
        return $this->session->getData(self::SESSION_KEY);
    }

    public function saveValue(mixed $value): void
    {
        $this->session->setData(self::SESSION_KEY, $value);
    }
}
