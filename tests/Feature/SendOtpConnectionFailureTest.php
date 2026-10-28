<?php

namespace Shabayek\Sms\Tests\Feature;

use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Transport failures are reported by exception, not by a null return.
 *
 * The stub raises Guzzle's ConnectException, which the Laravel HTTP client
 * converts into its own ConnectionException, as it would for a timeout.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SendOtpConnectionFailureTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider otpFlows
     */
    public function it_propagates_connection_failures_from_send_otp($connection, $service, $url)
    {
        if ($service) {
            config()->set("sms.connections.{$connection}.service", $service);
        }

        $this->stubHttp([
            $url => function (Request $request) {
                throw new ConnectException('Connection timed out', $request->toPsrRequest());
            },
        ]);

        $this->expectException(ConnectionException::class);

        Sms::driver($connection)->sendOtp('0123456789');
    }

    public static function otpFlows(): array
    {
        return [
            'smseg normal' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/?*'],
            'smseg provider-managed' => ['smseg', 'otp', 'smssmartegypt.com/sms/api/otp-send'],
            'smsmisr normal' => ['smsmisr', 'normal', 'smsmisr.com/api/v2'],
            'smsmisr otp' => ['smsmisr', 'otp', 'smsmisr.com/api/vSMS'],
            'victorylink' => ['victorylink', null, 'smsvas.vlserv.com/*'],
        ];
    }
}
