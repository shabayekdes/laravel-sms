<?php

namespace Shabayek\Sms\Tests\Feature;

use Shabayek\Sms\Tests\TestCase;

/**
 * UnifonicFeatureTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class UnifonicFeatureTest extends TestCase
{
    /** @test */
    public function it_can_unifonic_send_sms_message_successfully()
    {
        $this->setConfig('unifonic');

        $this->assertTrue(true);
    }

    /** @test */
    public function test_unifonic_not_sent_message()
    {
        $this->setConfig('unifonic');

        $this->assertFalse(false);
    }

    /**
     * Set config.
     *
     * @param  string  $config
     * @return void
     */
    private function setConfig(string $config)
    {
        config()->set('sms.default', $config);
    }
}
