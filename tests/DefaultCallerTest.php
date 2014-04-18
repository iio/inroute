<?php
namespace inroute;

class DefaultCallerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContainer()
    {
        $pimple = $this->getMock('\Pimple');
        $caller = new DefaultCaller($pimple);
        $this->assertEquals($pimple, $caller->getContainer());
    }

    public function testCall()
    {
        $route = $this->getMock(
            'inroute\Route',
            array(),
            array(),
            '',
            false
        );

        $callbackcalled = false;
        $that = &$this;
        $callback = function ($param) use (&$callbackcalled, $that, $route) {
            $callbackcalled = true;
            $that->assertSame($param, $route);
        };

        $pimple = $this->getMock('\Pimple');

        $caller = new DefaultCaller($pimple);
        $caller->call($callback, $route);

        $this->assertTrue($callbackcalled);
    }
}