<?php

require_once __DIR__ . '/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/MdMan/Listener.php';

use \phpDocumentor\Reflection\Event\PostDocBlockExtractionEvent;

/**
 * MdMan_ListenerTest
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_ListenerTest extends PHPUnit_Framework_TestCase
{

    /**
     * plugin mock
     * @var phpDocumentor\Plugin\PluginAbstract 
     */
    protected $plugin;

    /**
     * system under test
     * @var MdMan_Listener 
     */
    protected $listener;

    /**
     * event dispatcher mock
     * @var \phpDocumentor\Event\Dispatcher
     */
    protected $eventDispatcher;

    /**
     * subject of the event
     * @var \phpDocumentor\Reflection\ClassReflector 
     */
    protected $reflector;

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->createPlugin();
        $this->listener = new MdMan_Listener($this->plugin);
    }

    /**
     * Ensures the listener only operates on class doc comments.
     */
    public function testDoesOnlyReadClassDocblocks()
    {
        $reflector = $this->getMockBuilder('\phpDocumentor\Reflection\BaseReflector')
            ->disableOriginalConstructor()
            ->getMock();
        $reflector->expects($this->never())
            ->method('getDefaultPackageName');

        $event = $this->createEvent($reflector);
        $this->listener->fetchMarkdownFromClassDocBlock($event);
    }

    /**
     * Ensures markdown is fetched.
     */
    public function testFetchesMarkdownFromClassDocblock()
    {
        $docblock = $this->createDocblock();
        $docblock->expects($this->once())
            ->method('getShortDescription');
        
        $event = $this->createEvent($docblock);
        $this->listener->fetchMarkdownFromClassDocBlock($event);
    }


    /**
     * Creates the plugin which is injected into the listener.
     * 
     */
    protected function createPlugin()
    {
        $this->plugin = $this->getMockBuilder("phpDocumentor\Plugin\PluginAbstract")
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder('\phpDocumentor\Event\Dispatcher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventDispatcher));

        $config = $this->getMockBuilder('Zend\Config\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($config));

        $translator = $this->getMock('Zend\I18n\Translator\Translator');
        $this->plugin->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($translator));
    }

    /**
     * Creates the event subject.
     * 
     * @return \phpDocumentor\Reflection\ClassReflector
     */
    protected function createReflector()
    {
        $this->reflector = $this->getMockBuilder('\phpDocumentor\Reflection\ClassReflector')
            ->disableOriginalConstructor()
            ->getMock();
        $this->reflector->expects($this->any())
            ->method('getDefaultPackageName')
            ->will($this->returnValue('TestPackage'));
        $this->reflector->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('TestClass'));
    }

    /**
     * Creates the fired event.
     * 
     * @param \phpDocumentor\Reflection\ClassReflector $subject
     * @param \phpDocumentor\Reflection\DocBlock       $docBlock
     * @return \PostDocBlockExtractionEvent
     */
    protected function createEvent($subject = null, $docBlock = null)
    {
        if ($subject === null) {
            $subject = $this->createReflector();
        }
        $event = new PostDocBlockExtractionEvent($subject);
        $event->setDocblock($docBlock);
        return $event;
    }

    /**
     * Creates a docblock mock object.
     * 
     * @return \phpDocumentor\Reflection\DocBlock
     */
    protected function createDocblock()
    {
        return $this->getMockBuilder('\phpDocumentor\Reflection\DocBlock')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
