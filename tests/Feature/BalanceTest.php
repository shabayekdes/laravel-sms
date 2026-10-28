<?php

namespace Shabayek\Sms\Tests\Feature;

use ErrorException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Current balance() behaviour of every driver.
 *
 * Several cases below report a failed request as a balance, or throw while
 * parsing. They are pinned on purpose: how a failed balance check should be
 * reported is still an open decision (RM-HTTP-03), and these tests make any
 * change to it visible.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class BalanceTest extends TestCase
{
    /** @test */
    #[Test]
    public function smseg_returns_the_points_from_the_response()
    {
        $this->stubHttp([
            'smssmartegypt.com/sms/api/getBalance*' => Http::response(['type' => 'success', 'data' => ['points' => 250]], 200),
        ]);

        $this->assertSame(250, Sms::driver('smseg')->balance());

        Http::assertSent(function (Request $request) {
            return $request->method() === 'GET'
                && $request['username'] === self::TEST_USERNAME;
        });
    }

    /** @test */
    #[Test]
    public function smseg_returns_zero_for_an_error_response()
    {
        $this->stubHttp([
            'smssmartegypt.com/sms/api/getBalance*' => Http::response(['type' => 'error'], 200),
        ]);

        $this->assertSame(0, Sms::driver('smseg')->balance());
    }

    /** @test */
    #[Test]
    public function smseg_currently_returns_zero_for_a_server_error()
    {
        $this->stubHttp([
            'smssmartegypt.com/sms/api/getBalance*' => Http::response('<html>Server Error</html>', 500),
        ]);

        $this->assertSame(0, Sms::driver('smseg')->balance());
    }

    /** @test */
    #[Test]
    public function smsmisr_returns_the_balance_from_the_response()
    {
        $this->stubHttp([
            'smsmisr.com/api/Request*' => Http::response(['code' => '6000', 'Balance' => 7511], 200),
        ]);

        $this->assertSame(7511, Sms::driver('smsmisr')->balance());

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), 'request=status');
        });
    }

    /** @test */
    #[Test]
    public function smsmisr_returns_zero_for_an_error_code()
    {
        $this->stubHttp([
            'smsmisr.com/api/Request*' => Http::response(['code' => 'Error'], 200),
        ]);

        $this->assertSame(0, Sms::driver('smsmisr')->balance());
    }

    /** @test */
    #[Test]
    public function smsmisr_currently_returns_zero_for_a_server_error()
    {
        $this->stubHttp([
            'smsmisr.com/api/Request*' => Http::response('<html>Server Error</html>', 500),
        ]);

        $this->assertSame(0, Sms::driver('smsmisr')->balance());
    }

    /**
     * @test
     *
     * @dataProvider victoryLinkCredits
     */
    #[Test]
    #[DataProvider('victoryLinkCredits')]
    public function victorylink_returns_the_credit_value_as_is($value, $expected)
    {
        $this->stubHttp([
            'smsvas.vlserv.com/*/CheckCredit*' => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">'.$value.'</int>', 200),
        ]);

        $this->assertSame($expected, Sms::driver('victorylink')->balance());
    }

    public static function victoryLinkCredits(): array
    {
        // Victory Link's Reseller API document says 0 means unlimited and -1
        // means invalid credentials; both are returned unchanged for now.
        return [
            'available credit' => ['150', 150],
            'zero' => ['0', 0],
            'negative error code' => ['-1', -1],
        ];
    }

    /** @test */
    #[Test]
    public function victorylink_currently_throws_for_a_body_that_is_not_xml()
    {
        $this->stubHttp([
            'smsvas.vlserv.com/*/CheckCredit*' => Http::response('Service Unavailable', 503),
        ]);

        $this->expectException(ErrorException::class);

        Sms::driver('victorylink')->balance();
    }

    /** @test */
    #[Test]
    public function ooredoo_returns_the_sms_credit_from_several_pairs()
    {
        $this->stubOoredooCredits('<CreditPair><Type>MMS</Type><Value>3</Value></CreditPair><CreditPair><Type>SMS</Type><Value>50</Value></CreditPair>');

        $this->assertSame(50, Sms::driver('ooredoo')->balance());
    }

    /** @test */
    #[Test]
    public function ooredoo_returns_the_sms_credit_from_a_single_pair()
    {
        $this->stubOoredooCredits('<CreditPair><Type>SMS</Type><Value>50</Value></CreditPair>');

        $this->assertSame(50, Sms::driver('ooredoo')->balance());
    }

    /** @test */
    #[Test]
    public function ooredoo_returns_zero_without_an_sms_credit()
    {
        $this->stubOoredooCredits('<CreditPair><Type>MMS</Type><Value>3</Value></CreditPair>');

        $this->assertSame(0, Sms::driver('ooredoo')->balance());
    }

    /** @test */
    #[Test]
    public function ooredoo_currently_returns_a_negative_credit_as_positive()
    {
        $this->stubOoredooCredits('<CreditPair><Type>SMS</Type><Value>-20</Value></CreditPair>');

        $this->assertSame(20, Sms::driver('ooredoo')->balance());
    }

    /** @test */
    #[Test]
    public function ooredoo_currently_throws_when_the_response_has_no_credits()
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_Authenticate2*' => Http::response('<AuthResult2><Result>Failed</Result></AuthResult2>', 200),
        ]);

        $this->expectException(ErrorException::class);

        Sms::driver('ooredoo')->balance();
    }

    /**
     * Stub the Ooredoo balance response with the given credit pairs.
     *
     * @param  string  $pairs
     * @return void
     */
    private function stubOoredooCredits(string $pairs): void
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_Authenticate2*' => Http::response('<AuthResult2><Credits>'.$pairs.'</Credits><Result>OK</Result></AuthResult2>', 200),
        ]);
    }
}
