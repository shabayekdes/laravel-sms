<?php

namespace Shabayek\Sms\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Guards that keep the test suite away from real SMS gateways.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class TestSafetyFeatureTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider connections
     */
    #[Test]
    #[DataProvider('connections')]
    public function it_blocks_requests_without_a_stub($connection)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected HTTP request in tests');

        Sms::driver($connection)->send('0123456789', 'test message');
    }

    /** @test */
    #[Test]
    public function it_does_not_include_credentials_in_the_blocked_request_message()
    {
        try {
            Sms::driver('smseg')->send('0123456789', 'test message');
            $this->fail('The request was not blocked.');
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString(self::TEST_PASSWORD, $e->getMessage());
        }
    }

    /** @test */
    #[Test]
    public function it_uses_test_credentials_instead_of_environment_values()
    {
        foreach (['smseg', 'smsmisr', 'victorylink', 'ooredoo'] as $connection) {
            $this->assertSame(self::TEST_USERNAME, config("sms.connections.{$connection}.username"));
            $this->assertSame(self::TEST_PASSWORD, config("sms.connections.{$connection}.password"));
        }
    }

    public static function connections(): array
    {
        return [
            'smseg' => ['smseg'],
            'smsmisr' => ['smsmisr'],
            'victorylink' => ['victorylink'],
        ];
    }
}
