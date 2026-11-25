<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Query\RegexpError;

/**
 * A regular expression the database rejects (filter groups, 2026-11-23): its dialect differs from PHP's check.
 *
 * @group FeinwerkDataExploration
 */
class RegexpErrorTest extends TestCase
{
    public function testMysqlAndMariadbRegexpErrorCodes(): void
    {
        foreach ([3685, 3692, 3699, 1139] as $code) {
            $this->assertTrue(RegexpError::is(new \Exception('x', $code), $this->db()), (string) $code);
        }
        $this->assertFalse(RegexpError::is(new \Exception('x', 1064), $this->db()));
        $this->assertFalse(RegexpError::is(new \Exception('x', 3700), $this->db()));
    }

    public function testRecognisedByTheMessageWhenTheCodeIsLost(): void
    {
        $this->assertTrue(RegexpError::is(new \Exception("SQLSTATE[HY000]: General error: 1139 Got error 'missing )' from regexp"), $this->db()));
        $this->assertTrue(RegexpError::is(new \Exception('Mismatched parenthesis in regular expression.'), $this->db()));
        $this->assertFalse(RegexpError::is(new \Exception('Table does not exist'), $this->db()));
    }

    private function db(): object
    {
        return new class {
            public function isErrNo(\Throwable $e, $code): bool
            {
                return $e->getCode() === $code;
            }
        };
    }
}
