<?php

namespace Shabayek\Sms\Tests\Unit;

use Illuminate\Support\Facades\Facade;
use Shabayek\Sms\Tests\TestCase;

/**
 * Laravel package discovery aliases declared in composer.json.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class ComposerAliasTest extends TestCase
{
    /** @test */
    public function it_points_every_alias_to_an_existing_facade()
    {
        $composer = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);
        $aliases = $composer['extra']['laravel']['aliases'] ?? [];

        $this->assertNotEmpty($aliases);

        foreach ($aliases as $alias => $class) {
            $this->assertTrue(class_exists($class), "Alias [{$alias}] points to missing class [{$class}].");
            $this->assertTrue(is_subclass_of($class, Facade::class), "Alias [{$alias}] does not point to a facade.");
        }
    }
}
