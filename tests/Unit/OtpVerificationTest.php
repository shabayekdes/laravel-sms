<?php

namespace Shabayek\Sms\Tests\Unit;

use Exception;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Local OTP verification (Driver::verify).
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class OtpVerificationTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider matchingOtps
     */
    public function it_accepts_a_matching_otp($otp, $actualOtp)
    {
        $this->assertTrue(Sms::driver('victorylink')->verify('0123456789', $otp, $actualOtp));
    }

    /**
     * @test
     *
     * @dataProvider rejectedOtps
     */
    public function it_rejects_a_wrong_or_malformed_otp($otp, $actualOtp)
    {
        $this->assertFalse(Sms::driver('victorylink')->verify('0123456789', $otp, $actualOtp));
    }

    /** @test */
    public function it_requires_the_actual_otp()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Actual otp is required for this service');

        Sms::driver('victorylink')->verify('0123456789', 123456, null);
    }

    public static function matchingOtps(): array
    {
        return [
            'int and int' => [123456, 123456],
            'string and int' => ['123456', 123456],
            'int and string' => [123456, '123456'],
            'string and string' => ['123456', '123456'],
            'development code' => [1234, 1234],
        ];
    }

    public static function rejectedOtps(): array
    {
        return [
            'wrong int' => [654321, 123456],
            'wrong string' => ['654321', 123456],
            'boolean true' => [true, 123456],
            'boolean true against string' => [true, '123456'],
            'boolean false' => [false, 123456],
            'null' => [null, 123456],
            'empty string' => ['', 123456],
            'empty strings on both sides' => ['', ''],
            'leading space' => [' 123456', 123456],
            'trailing space' => ['123456 ', 123456],
            'trailing newline' => ["123456\n", 123456],
            'decimal string' => ['123456.0', 123456],
            'exponent string' => ['1.23456e5', 123456],
            'float' => [123456.0, 123456],
            'leading zero' => ['0123456', 123456],
            'trailing letters' => ['123456abc', 123456],
            'negative int' => [-123456, -123456],
            'array' => [[], 123456],
            'boolean actual otp' => [123456, true],
        ];
    }
}
