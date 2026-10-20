<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkDataExploration\tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkDataExploration\Query\StatementTimeLimit;

/**
 * @group FeinwerkDataExploration
 */
class StatementTimeLimitTest extends TestCase
{
    private const SQL = 'SELECT a FROM t WHERE b = ?';

    public function testMariaDbGetsSetStatement(): void
    {
        $this->assertSame(
            'SET STATEMENT max_statement_time=30 FOR ' . self::SQL,
            StatementTimeLimit::apply('  ' . self::SQL, 30, '10.11.19-MariaDB-deb11')
        );
    }

    public function testMySqlGetsOptimizerHint(): void
    {
        $this->assertSame(
            'SELECT /*+ MAX_EXECUTION_TIME(30000) */ a FROM t WHERE b = ?',
            StatementTimeLimit::apply(self::SQL, 30, '8.0.36')
        );
    }

    public function testQueriesAreTaggedAfterTheHint(): void
    {
        // the tag makes explorations easy to find in SHOW PROCESSLIST and the slow query log; MySQL reads the
        // optimizer hint only right after SELECT
        $mysql = StatementTimeLimit::apply(StatementTimeLimit::tag('SELECT 1'), 30, '8.0.36');
        $this->assertSame('SELECT /*+ MAX_EXECUTION_TIME(30000) */ /* FeinwerkDataExploration */ 1', $mysql);

        $mariaDb = StatementTimeLimit::apply(StatementTimeLimit::tag('SELECT 1'), 30, '10.11.6-MariaDB');
        $this->assertSame('SET STATEMENT max_statement_time=30 FOR SELECT /* FeinwerkDataExploration */ 1', $mariaDb);

        $this->assertSame('UPDATE x', StatementTimeLimit::tag('UPDATE x'));
    }

    public function testOldMySqlUnchanged(): void
    {
        $this->assertSame(self::SQL, StatementTimeLimit::apply(self::SQL, 30, '5.6.51-log'));
    }

    public function testExistingLimitIsKept(): void
    {
        $mariaDb = 'SET STATEMENT max_statement_time=5 FOR ' . self::SQL;
        $mySql = 'SELECT /*+ MAX_EXECUTION_TIME(5000) */ a FROM t';
        $this->assertSame($mariaDb, StatementTimeLimit::apply($mariaDb, 30, '10.11.19-MariaDB'));
        $this->assertSame($mySql, StatementTimeLimit::apply($mySql, 30, '8.0.36'));
    }

    public function testZeroSecondsUnchanged(): void
    {
        $this->assertSame(self::SQL, StatementTimeLimit::apply(self::SQL, 0, '10.11.19-MariaDB'));
    }

    public function testNonSelectUnchanged(): void
    {
        $this->assertSame('DELETE FROM t', StatementTimeLimit::apply('DELETE FROM t', 30, '8.0.36'));
    }

    public function testTimeoutErrorDetectedByCode(): void
    {
        $db = $this->fakeDb([1969]);
        $this->assertTrue(StatementTimeLimit::isTimeoutError(new \Exception('SQLSTATE[70100]: 1969 whatever'), $db));
    }

    public function testTimeoutErrorDetectedByMessage(): void
    {
        $e = new \Exception('SQLSTATE[70100]: Query execution was interrupted (max_statement_time exceeded)');
        $this->assertTrue(StatementTimeLimit::isTimeoutError($e, $this->fakeDb([])));
    }

    public function testOtherErrorIsNoTimeout(): void
    {
        $e = new \Exception("SQLSTATE[42S22]: Column not found: 1054 Unknown column 'x'");
        $this->assertFalse(StatementTimeLimit::isTimeoutError($e, $this->fakeDb([])));
    }

    /** A db adapter stand-in whose isErrNo() knows the given error numbers. */
    private function fakeDb(array $errNos): object
    {
        return new class ($errNos) {
            public function __construct(private array $errNos)
            {
            }

            public function isErrNo($e, $errno): bool
            {
                return in_array((int) $errno, $this->errNos, true);
            }
        };
    }
}
