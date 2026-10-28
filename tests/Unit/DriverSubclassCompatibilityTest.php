<?php

namespace Shabayek\Sms\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Shabayek\Sms\Tests\Fixtures\LegacyOtpDriver;
use Shabayek\Sms\Tests\TestCase;

/**
 * Existing consumer subclasses of Driver must keep loading.
 *
 * An incompatible method signature is a fatal error at class load time,
 * so the class is loaded in a separate PHP process.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class DriverSubclassCompatibilityTest extends TestCase
{
    /** @test */
    #[Test]
    public function it_loads_a_subclass_that_declares_is_valid_otp_without_a_return_type()
    {
        $script = sprintf(
            'require %s; echo class_exists(%s) ? "loaded" : "missing";',
            var_export(realpath(__DIR__.'/../../vendor/autoload.php'), true),
            var_export(LegacyOtpDriver::class, true)
        );

        exec(escapeshellarg(PHP_BINARY).' -r '.escapeshellarg($script).' 2>&1', $output, $status);

        $this->assertSame(0, $status, implode("\n", $output));
        $this->assertSame(['loaded'], $output);
    }
}
