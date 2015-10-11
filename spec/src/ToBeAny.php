<?php
namespace Kastilyo\RabbitHole\Spec;

/**
 * Custom matcher for when you don't care
 */
class ToBeAny
{
    public static function match($actual, $expected = null)
    {
        return true;
    }

    public static function description()
    {
        return "be anything";
    }
}
