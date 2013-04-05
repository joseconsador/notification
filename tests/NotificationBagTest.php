<?php

use Mockery as m;

class NotificationBagTest extends PHPUnit_Framework_TestCase
{
    private $bag;

    public function tearDown()
    {
        m::close();
    }

    protected function setUp()
    {
        $session = m::mock('Illuminate\Session\Store');
        $config = m::mock('Illuminate\Config\Repository');

        $session->shouldReceive('get')
            ->once()
            ->andReturn('[{"type":"error","message":"test error","format":":message!"},{"type":"warning","message":"test warning","format":":message..."}]');

        $config->shouldReceive('get')->with('notification::default_format')->andReturn('<div class="alert alert-:type">:message</div>');
        $config->shouldReceive('get')->with('notification::default_formats')->andReturn(array('__' => array()));

        $this->bag = new \Krucas\Notification\NotificationsBag('test',$session, $config);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Krucas\Notification\NotificationsBag', $this->bag);

        return $this->bag;
    }

    /**
     * @depends testConstructor
     */
    public function testIsSetDefaultFormatFromConfig(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->getFormat());

        return $bag;
    }

    /**
     * @depends testIsSetDefaultFormatFromConfig
     */
    public function testMessagesIsLoadedFromFlash(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertCount(2, $bag);
        $this->assertCount(2, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->all()->first());
        $this->assertEquals('test error', $bag->all()->first()->getMessage());
        $this->assertEquals('error', $bag->all()->first()->getType());
        $this->assertEquals(':message!', $bag->all()->first()->getFormat());

        return $bag;
    }

    /**
     * @depends testMessagesIsLoadedFromFlash
     */
    public function testAddFlashableSuccessMessageWithCustomFormat(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->getSessionStore()
            ->shouldReceive('flash')
            ->once()
            ->with(
                'notifications_test',
                '[{"message":"all ok","format":"custom: :message","type":"success","flashable":true,"alias":null}]'
            );

        $bag->success('all ok', 'custom: :message');

        $this->assertCount(3, $bag);
        $this->assertCount(3, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('success')->first());
        $this->assertEquals('all ok', $bag->get('success')->first()->getMessage());
        $this->assertEquals('success', $bag->get('success')->first()->getType());
        $this->assertEquals('custom: :message', $bag->get('success')->first()->getFormat());
        $this->assertTrue($bag->get('success')->first()->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddFlashableSuccessMessageWithCustomFormat
     */
    public function testAddFlashableWarningMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->getSessionStore()->shouldReceive('flash')->once();

        $bag->warning('second message');

        $this->assertCount(3, $bag);
        $this->assertCount(4, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('warning')->first());
        $this->assertEquals('test warning', $bag->get('warning')->first()->getMessage());
        $this->assertEquals('warning', $bag->get('warning')->first()->getType());
        $this->assertEquals(':message...', $bag->get('warning')->first()->getFormat());
        $this->assertFalse($bag->get('warning')->first()->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddFlashableWarningMessage
     */
    public function testAddFlashableInfoMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->getSessionStore()->shouldReceive('flash')->once();

        $bag->info('info m');

        $this->assertCount(4, $bag);
        $this->assertCount(5, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('info')->first());
        $this->assertEquals('info m', $bag->get('info')->first()->getMessage());
        $this->assertEquals('info', $bag->get('info')->first()->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->get('info')->first()->getFormat());
        $this->assertTrue($bag->get('info')->first()->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddFlashableInfoMessage
     */
    public function testAddFlashableErrorMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->getSessionStore()->shouldReceive('flash')->once();

        $bag->error('e m');

        $this->assertCount(4, $bag);
        $this->assertCount(6, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('error')->first());
        $this->assertEquals('test error', $bag->get('error')->first()->getMessage());
        $this->assertEquals('error', $bag->get('error')->first()->getType());
        $this->assertEquals(':message!', $bag->get('error')->first()->getFormat());
        $this->assertFalse($bag->get('error')->first()->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddFlashableErrorMessage
     */
    public function testAddInstantSuccessMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->successInstant('s m');

        $this->assertCount(4, $bag);
        $this->assertCount(7, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('success')[1]);
        $this->assertEquals('s m', $bag->get('success')[1]->getMessage());
        $this->assertEquals('success', $bag->get('success')[1]->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->get('success')[1]->getFormat());
        $this->assertFalse($bag->get('success')[1]->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddInstantSuccessMessage
     */
    public function testAddInstantInfoMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->infoInstant('i m');

        $this->assertCount(4, $bag);
        $this->assertCount(8, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('info')[1]);
        $this->assertEquals('i m', $bag->get('info')[1]->getMessage());
        $this->assertEquals('info', $bag->get('info')[1]->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->get('info')[1]->getFormat());
        $this->assertFalse($bag->get('info')[1]->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddInstantInfoMessage
     */
    public function testAddInstantWarningMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->warningInstant('w m');

        $this->assertCount(4, $bag);
        $this->assertCount(9, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('warning')[2]);
        $this->assertEquals('w m', $bag->get('warning')[2]->getMessage());
        $this->assertEquals('warning', $bag->get('warning')[2]->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->get('warning')[2]->getFormat());
        $this->assertFalse($bag->get('warning')[2]->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddInstantWarningMessage
     */
    public function testAddInstantErrorMessage(\Krucas\Notification\NotificationsBag $bag)
    {
        $bag->errorInstant('e m');

        $this->assertCount(4, $bag);
        $this->assertCount(10, $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->all());
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('error')[2]);
        $this->assertEquals('e m', $bag->get('error')[2]->getMessage());
        $this->assertEquals('error', $bag->get('error')[2]->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->get('error')[2]->getFormat());
        $this->assertFalse($bag->get('error')[2]->isFlashable());

        return $bag;
    }

    /**
     * @depends testAddInstantErrorMessage
     */
    public function testHowManyContainersCreated(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertCount(4, $bag);

        return $bag;
    }

    /**
     * @depends testHowManyContainersCreated
     */
    public function testHowManyMessagesAdded(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertCount(10, $bag->all());

        return $bag;
    }

    /**
     * @depends testHowManyMessagesAdded
     */
    public function testGetErrorMessageContainer(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertCount(3, $bag->get('error'));
        $this->assertInstanceOf('Krucas\Notification\Collection', $bag->get('error'));

        return $bag;
    }

    /**
     * @depends testGetErrorMessageContainer
     */
    public function testGetFirstMessageFromContainer(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertInstanceOf('Krucas\Notification\Message', $bag->get('error')->first());
        $this->assertEquals('test error', $bag->get('error')->first()->getMessage());
        $this->assertEquals('error', $bag->get('error')->first()->getType());
        $this->assertEquals(':message!', $bag->get('error')->first()->getFormat());

        return $bag;
    }

    /**
     * @depends testGetFirstMessageFromContainer
     */
    public function testOverrideMessageFormat(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $bag->getFormat());
        $bag->setFormat(':message');
        $this->assertEquals(':message', $bag->getFormat());
        $bag->setFormat(':message!', 'error');
        $this->assertEquals(':message!', $bag->getFormat('error'));
        $this->assertEquals(':message', $bag->getFormat());

        return $bag;
    }

    /**
     * @depends testOverrideMessageFormat
     */
    public function testToArray(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertEquals(array(
            'collections'   => array(
                'error'     => array(
                    array(
                        'message'   => 'test error',
                        'type'      => 'error',
                        'format'    => ':message!',
                        'flashable' => false,
                        'alias'     => null
                    ),
                    array(
                        'message'   => 'e m',
                        'type'      => 'error',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => true,
                        'alias'     => null
                    ),
                    array(
                        'message'   => 'e m',
                        'type'      => 'error',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => false,
                        'alias'     => null
                    )
                ),
                'success'   => array(
                    array(
                        'message'   => 'all ok',
                        'type'      => 'success',
                        'format'    => 'custom: :message',
                        'flashable' => true,
                        'alias'     => null
                    ),
                    array(
                        'message'   => 's m',
                        'type'      => 'success',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => false,
                        'alias'     => null
                    )
                ),
                'warning'   => array(
                    array(
                        'message'   => 'test warning',
                        'type'      => 'warning',
                        'format'    => ':message...',
                        'flashable' => false,
                        'alias'     => null
                    ),
                    array(
                        'message'   => 'second message',
                        'type'      => 'warning',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => true,
                        'alias'     => null
                    ),
                    array(
                        'message'   => 'w m',
                        'type'      => 'warning',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => false,
                        'alias'     => null
                    )
                ),
                'info'      => array(
                    array(
                        'message'   => 'info m',
                        'type'      => 'info',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => true,
                        'alias'     => null
                    ),
                    array(
                        'message'   => 'i m',
                        'type'      => 'info',
                        'format'    => '<div class="alert alert-:type">:message</div>',
                        'flashable' => false,
                        'alias'     => null
                    )
                )
            ),
            'container'     => 'test',
            'format'        => ':message'
        ), $bag->toArray());


        return $bag;
    }

    /**
     * @depends testToArray
     */
    public function testToJson(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertContains('"container":"test"', $bag->toJson());

        return $bag;
    }

    /**
     * @depends testToJson
     */
    public function testShowWarningContainer(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertContains('<div class="alert alert-warning">w m</div>', $bag->show('warning'));

        return $bag;
    }

    /**
     * @depends testShowWarningContainer
     */
    public function testShowAllContainers(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertContains('<div class="alert alert-warning">w m</div>', $bag->show());

        return $bag;
    }

    /**
     * @depends testShowAllContainers
     */
    public function testShowAllContainersWithACustomFormat(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertContains('w m', $bag->show(null, ':message'));

        return $bag;
    }

    /**
     * @depends testShowAllContainersWithACustomFormat
     */
    public function testToString(\Krucas\Notification\NotificationsBag $bag)
    {
        $this->assertContains('info m', (string) $bag);

        return $bag;
    }

    public function testAddingMessageArray()
    {
        $this->bag->infoInstant(array(
            'first',
            'second'
        ));

        $this->assertCount(2, $this->bag->get('info'));
        $this->assertInstanceOf('Krucas\Notification\Message', $this->bag->get('info')->first());
        $this->assertEquals('first', $this->bag->get('info')->first()->getMessage());
        $this->assertEquals('info', $this->bag->get('info')->first()->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $this->bag->get('info')->first()->getFormat());
        $this->assertFalse($this->bag->get('info')->first()->isFlashable());
    }

    public function testAddingMessageArrayWithCustomFormat()
    {
        $this->bag->infoInstant(array(
            array('first', ':message'),
            'second'
        ));

        $this->assertCount(2, $this->bag->get('info'));
        $this->assertInstanceOf('Krucas\Notification\Message', $this->bag->get('info')->first());
        $this->assertEquals('first', $this->bag->get('info')->first()->getMessage());
        $this->assertEquals('info', $this->bag->get('info')->first()->getType());
        $this->assertEquals(':message', $this->bag->get('info')->first()->getFormat());
        $this->assertFalse($this->bag->get('info')->first()->isFlashable());

        $this->assertInstanceOf('Krucas\Notification\Message', $this->bag->get('info')[1]);
        $this->assertEquals('second', $this->bag->get('info')[1]->getMessage());
        $this->assertEquals('info', $this->bag->get('info')[1]->getType());
        $this->assertEquals('<div class="alert alert-:type">:message</div>', $this->bag->get('info')[1]->getFormat());
        $this->assertFalse($this->bag->get('info')[1]->isFlashable());
    }

    public function testSetCustomFormatAndDisplayAMessage()
    {
        $this->bag->setFormat('no format');

        $this->bag->infoInstant(array(
            array('first', ':message'),
            'second'
        ));

        $this->assertEquals('no format', $this->bag->getFormat());

        $this->assertCount(2, $this->bag->get('info'));
        $this->assertInstanceOf('Krucas\Notification\Message', $this->bag->get('info')->first());
        $this->assertEquals('first', $this->bag->get('info')->first()->getMessage());
        $this->assertEquals('info', $this->bag->get('info')->first()->getType());
        $this->assertEquals(':message', $this->bag->get('info')->first()->getFormat());
        $this->assertFalse($this->bag->get('info')->first()->isFlashable());

        $this->assertInstanceOf('Krucas\Notification\Message', $this->bag->get('info')[1]);
        $this->assertEquals('second', $this->bag->get('info')[1]->getMessage());
        $this->assertEquals('info', $this->bag->get('info')[1]->getType());
        $this->assertEquals('no format', $this->bag->get('info')[1]->getFormat());
        $this->assertFalse($this->bag->get('info')[1]->isFlashable());

        $this->assertEquals('test error!test warning...firstno format', $this->bag->show());
    }
}