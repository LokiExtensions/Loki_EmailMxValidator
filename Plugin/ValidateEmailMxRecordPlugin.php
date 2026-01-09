<?php declare(strict_types=1);

namespace Loki\EmailMxValidator\Plugin;

use Loki\Components\Validator\EmailValidator;
use Loki\EmailMxValidator\Config\Config;
use Spatie\Dns\Dns;

class ValidateEmailMxRecordPlugin
{
    public function __construct(
        private Config $config,
        private Dns $dns,
        private int $retries = 1,
        private int $timeout = 5,
    ) {
    }

    public function afterValidate(
        EmailValidator $subject,
        bool|array $result,
        mixed $value,
    ): bool|array {
        if ($this->config->enableMxValidationForEmail()) {
            $result = $this->checkMxRecord((string)$value);
            if (is_array($result)) {
                return $result;
            }
        }

        return $result;
    }

    private function checkMxRecord(string $email): bool|array
    {
        $parts = explode('@', $email);
        if (count($parts) < 2) {
            $message = (string)__('E-mail "%s" is not valid');

            return [sprintf($message, $email)];
        }

        $domain = $parts[1];
        $mxRecords = $this->dns
            ->setRetries($this->retries)
            ->setTimeout($this->timeout)
            ->getRecords($domain, DNS_MX);

        if (count($mxRecords) === 0) {
            $message = (string)__('Domain "%s" is not reachable for mail');

            return [sprintf($message, $domain)];
        }

        return true;
    }
}
