<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Enums\ResponseCode;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Response codes, configurable messages and message encoding.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class ResponseCodeTest extends TestCase
{
    private const XML = '<?xml version="1.0" encoding="utf-8"?>';

    /**
     * @test
     *
     * @dataProvider sendResponses
     */
    public function it_returns_a_response_code_for_each_gateway_answer($driver, $url, $response, $code, $providerCode, $providerMessage)
    {
        $this->stubHttp([$url => Http::response(...$response)]);

        $sms = Sms::driver($driver)->send('0123456789', 'test message');

        $this->assertSame($code === ResponseCode::Sent, $sms['success']);
        $this->assertSame($code, $sms['code']);
        $this->assertSame($code->defaultMessage(), $sms['message']);
        $this->assertSame(['code' => $providerCode, 'message' => $providerMessage], $sms['provider']);
    }

    public function sendResponses(): array
    {
        $smsmisr = 'smsmisr.com/api/v2';
        $smseg = 'smssmartegypt.com/sms/api/?*';
        $victorylink = 'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*';
        $ooredoo = 'ooredoo.test/Messenger.asmx/HTTP_SendSms*';

        return [
            'smsmisr sent' => ['smsmisr', $smsmisr, [['code' => 1901], 200], ResponseCode::Sent, '1901', null],
            'smsmisr rejected' => ['smsmisr', $smsmisr, [['code' => '1902'], 200], ResponseCode::Rejected, '1902', null],
            'smsmisr http error' => ['smsmisr', $smsmisr, [['code' => '1901'], 500], ResponseCode::HttpError, '1901', null],
            'smsmisr invalid' => ['smsmisr', $smsmisr, ['oops', 200], ResponseCode::InvalidResponse, null, null],
            'smseg sent' => ['smseg', $smseg, [['type' => 'success'], 200], ResponseCode::Sent, null, null],
            'smseg rejected' => ['smseg', $smseg, [['type' => 'error', 'error' => ['msg' => 'Invalid sender']], 200], ResponseCode::Rejected, null, 'Invalid sender'],
            'smseg http error' => ['smseg', $smseg, ['', 503], ResponseCode::HttpError, null, null],
            'smseg invalid' => ['smseg', $smseg, [['data' => []], 200], ResponseCode::InvalidResponse, null, null],
            'victorylink sent' => ['victorylink', $victorylink, [self::XML.'<int>0</int>', 200], ResponseCode::Sent, '0', null],
            'victorylink rejected' => ['victorylink', $victorylink, [self::XML.'<int>-1</int>', 200], ResponseCode::Rejected, '-1', null],
            'victorylink http error' => ['victorylink', $victorylink, [self::XML.'<int>0</int>', 500], ResponseCode::HttpError, null, null],
            'victorylink invalid' => ['victorylink', $victorylink, ['Service Unavailable', 200], ResponseCode::InvalidResponse, null, null],
            'ooredoo sent' => ['ooredoo', $ooredoo, [self::XML.'<SendResult><Result>OK</Result></SendResult>', 200], ResponseCode::Sent, 'OK', null],
            'ooredoo rejected' => ['ooredoo', $ooredoo, [self::XML.'<SendResult><Result>Failed</Result></SendResult>', 200], ResponseCode::Rejected, 'Failed', null],
            'ooredoo http error' => ['ooredoo', $ooredoo, ['', 502], ResponseCode::HttpError, null, null],
            'ooredoo invalid' => ['ooredoo', $ooredoo, [self::XML.'<SendResult></SendResult>', 200], ResponseCode::InvalidResponse, null, null],
        ];
    }

    /** @test */
    public function the_null_driver_returns_the_disabled_code()
    {
        $sms = Sms::driver('null')->send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertSame(ResponseCode::Disabled, $sms['code']);
        $this->assertSame('SMS driver is disabled', $sms['message']);
    }

    /** @test */
    public function it_uses_the_configured_message_for_a_code()
    {
        config()->set('sms.messages.rejected', 'The gateway refused the message');

        $this->stubHttp([
            'smsmisr.com/api/v2' => Http::response(['code' => '1902']),
        ]);

        $sms = Sms::driver('smsmisr')->send('0123456789', 'test message');

        $this->assertSame(ResponseCode::Rejected, $sms['code']);
        $this->assertSame('The gateway refused the message', $sms['message']);
    }

    /** @test */
    public function it_falls_back_to_the_default_message_when_a_code_is_not_configured()
    {
        config()->set('sms.messages', []);

        $sms = Sms::driver('null')->send('0123456789', 'test message');

        $this->assertSame('SMS driver is disabled', $sms['message']);
    }

    /** @test */
    public function the_code_serializes_to_its_string_value()
    {
        $sms = Sms::driver('null')->send('0123456789', 'test message');

        $this->assertSame('"disabled"', json_encode($sms['code']));
        $this->assertSame(ResponseCode::Disabled, ResponseCode::from('disabled'));
    }

    /** @test */
    public function it_sends_an_arabic_otp_message_as_arabic()
    {
        config()->set('sms.message', 'كود التحقق الخاص بك هو: {code}');

        $this->stubHttp([
            'smsmisr.com/api/v2' => Http::response(['code' => 1901]),
        ]);

        Sms::driver('smsmisr')->sendOtp('0123456789');

        Http::assertSent(function (Request $request) {
            return $request['message'] === 'كود التحقق الخاص بك هو: 1234' && $request['language'] === 2;
        });
    }

    /**
     * @test
     *
     * @dataProvider encodings
     */
    public function it_detects_the_encoding_from_the_message_text($driver, $url, $response, $field, $text, $expected)
    {
        $this->stubHttp([$url => Http::response(...$response)]);

        Sms::driver($driver)->send('0123456789', $text);

        Http::assertSent(function (Request $request) use ($field, $expected) {
            return $request[$field] === $expected;
        });
    }

    public function encodings(): array
    {
        $smsmisr = ['smsmisr', 'smsmisr.com/api/v2', [['code' => 1901], 200], 'language'];
        $victorylink = ['victorylink', 'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*', [self::XML.'<int>0</int>', 200], 'SMSLang'];
        $ooredoo = ['ooredoo', 'ooredoo.test/Messenger.asmx/HTTP_SendSms*', [self::XML.'<SendResult><Result>OK</Result></SendResult>', 200], 'messageType'];

        return [
            'smsmisr english' => array_merge($smsmisr, ['Hello', 1]),
            'smsmisr arabic' => array_merge($smsmisr, ['مرحبا', 2]),
            'smsmisr unicode' => array_merge($smsmisr, ['Héllo 👋', 3]),
            'victorylink english' => array_merge($victorylink, ['Hello', 'E']),
            'victorylink arabic' => array_merge($victorylink, ['مرحبا', 'A']),
            'ooredoo english' => array_merge($ooredoo, ['Hello', 'Latin']),
            'ooredoo arabic' => array_merge($ooredoo, ['مرحبا', 'ArabicWithArabicNumbers']),
        ];
    }
}
