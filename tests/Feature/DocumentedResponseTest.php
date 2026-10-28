<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shabayek\Sms\Enums\ResponseCode;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Responses built from the provider documentation, not from the drivers.
 *
 * Sources:
 * - SMS EG: smseg.com/en/docs, "SMS API" success sample.
 * - SMS Misr: smsmisr.com docs for POST /api/SMS/ (success sample and the
 *   19xx code table). The driver still calls the undocumented /api/v2
 *   endpoint (RM-BLK-MI-01), so these bodies are served from there.
 * - Ooredoo: archived Messenger.asmx operation pages for SendResult and
 *   AuthResult2. ASMX HTTP-GET responses declare the service namespace on
 *   the root element; its exact URI is not documented, so tempuri.org is
 *   used, as in the Victory Link fixtures.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class DocumentedResponseTest extends TestCase
{
    /** @test */
    #[Test]
    public function smseg_accepts_the_documented_success_body()
    {
        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response([
                'type' => 'success',
                'msg' => 'Your message was sent !',
                'data' => ['smsid' => 458281, 'sent' => 2, 'failed' => 0],
            ], 200),
        ]);

        $sms = Sms::driver('smseg')->send('201111111111', 'test message');

        $this->assertTrue($sms['success']);
        $this->assertSame(ResponseCode::Sent, $sms['code']);
    }

    /** @test */
    #[Test]
    public function smsmisr_accepts_the_documented_success_body()
    {
        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response(['code' => '1901', 'SMSID' => '12345', 'Cost' => '1'], 200),
        ]);

        $sms = Sms::driver('smsmisr')->send('201111111111', 'test message');

        $this->assertTrue($sms['success']);
        $this->assertSame(ResponseCode::Sent, $sms['code']);
        $this->assertSame('1901', $sms['provider']['code']);
    }

    /**
     * @test
     *
     * @dataProvider smsMisrErrorCodes
     */
    #[Test]
    #[DataProvider('smsMisrErrorCodes')]
    public function smsmisr_rejects_every_documented_error_code($code)
    {
        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response(['code' => $code], 200),
        ]);

        $sms = Sms::driver('smsmisr')->send('201111111111', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertSame(ResponseCode::Rejected, $sms['code']);
        $this->assertSame($code, $sms['provider']['code']);
    }

    public static function smsMisrErrorCodes(): array
    {
        return [
            'invalid request' => ['1902'],
            'invalid username or password' => ['1903'],
            'invalid sender' => ['1904'],
            'invalid mobile' => ['1905'],
            'insufficient credit' => ['1906'],
            'server updating' => ['1907'],
            'invalid DelayUntil format' => ['1908'],
            'invalid message' => ['1909'],
            'invalid language' => ['1910'],
            'text too long' => ['1911'],
            'invalid environment' => ['1912'],
        ];
    }

    /** @test */
    #[Test]
    public function ooredoo_accepts_the_documented_send_result()
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response(
                '<?xml version="1.0" encoding="utf-8"?>'
                .'<SendResult xmlns="http://tempuri.org/">'
                .'<RejectedNumbers /><TransactionID>8f2c1d7e</TransactionID><NetPoints>99</NetPoints><Result>OK</Result>'
                .'</SendResult>',
                200
            ),
        ]);

        $sms = Sms::driver('ooredoo')->send('97455555555', 'test message');

        $this->assertTrue($sms['success']);
        $this->assertSame('OK', $sms['provider']['code']);
    }

    /** @test */
    #[Test]
    public function ooredoo_reads_the_sms_credit_from_the_documented_auth_result()
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_Authenticate2*' => Http::response(
                '<?xml version="1.0" encoding="utf-8"?>'
                .'<AuthResult2 xmlns="http://tempuri.org/">'
                .'<Credits>'
                .'<CreditPair><Type>MMS</Type><Value>3</Value></CreditPair>'
                .'<CreditPair><Type>SMS</Type><Value>50</Value></CreditPair>'
                .'<CreditPair><Type>USSD</Type><Value>7</Value></CreditPair>'
                .'</Credits>'
                .'<NetPoints>60</NetPoints><Originators><string>TestSender</string></Originators>'
                .'<CustomerID>1</CustomerID><Result>OK</Result>'
                .'</AuthResult2>',
                200
            ),
        ]);

        $this->assertSame(50, Sms::driver('ooredoo')->balance());
    }
}
