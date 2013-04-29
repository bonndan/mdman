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
     * config
     * @var \Zend\Config\Config
     */
    protected $config;
    
    /**
     * subject of the event
     * @var \phpDocumentor\Reflection\ClassReflector 
     */
    protected $reflector;
    
    /**
     * mock of the shell wrapper
     * @var \Bart\Shell
     */
    protected $shellMock;

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->shellMock = $this->getMock("\Bart\Shell");
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
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->listener->handleClassBlockExtraction($event);
    }

    /**
     * Ensures markdown is fetched.
     */
    public function testFetchesMarkdownFromClassDocblock()
    {
        $docblock = $this->createDocblock();
        $docblock->expects($this->once())
            ->method('getShortDescription');
        //$longDescription = new \phpDocumentor\Reflection\DocBlock\LongDescription();
        $docblock->expects($this->once())
            ->method('getLongDescription')
            ->will($this->returnValue(null));
        
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        
        $event = $this->createEvent(null, $docblock);
        $this->listener->handleClassBlockExtraction($event);
    }

    /**
     * Ensures a given config option is read properly
     */
    public function testReadsConfigurationOption()
    {
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
    }
    
    
    /**
     * Ensures only the plugin config is read.
     */
    public function testReadsConfigurationOnlyForOption()
    {
        
        $config = array(
            'plugins' => array(
                'plugin' => array(
                    'path' => 'some/other/path',
                    'option' => array(
                        array(
                            MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::OUTDIR_OPTION,
                            MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => sys_get_temp_dir(),
                        ),
                        array(
                            MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::OUTFILE_OPTION,
                            MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => 'testfile.md',
                        ),
                    )
                )
            )
        );
        
        $this->createConfig($config);
        $this->createPlugin();
        
        $this->setExpectedException('\LogicException');
        $this->createListener();
    }

    /**
     * Ensures the listener also implements the content provider methods for writers.
     * 
     */
    public function testImplementsContentProvider()
    {
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->assertInstanceOf('MdMan_ContentProvider', $this->listener);
        $this->assertInternalType('array', $this->listener->getTree());
    }
    
    /**
     * Ensures all configured writers are executed.
     */
    public function testRunExports()
    {
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->listener->runExports();
        
        $writers = $this->listener->getWriters();
        $this->assertEquals(1, count($writers));
        
        $mock = current($writers);
        $this->assertInstanceOf('WriterMock', $mock);
        $this->assertTrue($mock->executed);
    }
    
    /**
     * Ensures that a tag is appended to the list of collected tags.
     */
    public function testCollectTagsOfType()
    {
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->listener->collectTagsOfType('test');
        $this->assertAttributeContains('test', 'collectedTagNames', $this->listener);
    }
    
    /**
     * Ensures the tags are really collected.
     */
    public function testTagsAreCollected()
    {
        $docblock = $this->createDocblock();
        $docblock->expects($this->at(0))
            ->method('hasTag')
            ->will($this->returnValue(false));
        $docblock->expects($this->at(1))
            ->method('hasTag')
            ->will($this->returnValue(true));
        $tag = new stdClass;
        $docblock->expects($this->any())
            ->method('getTagsByName')
            ->will($this->returnValue(array($tag)));
        
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->listener->collectTagsOfType('testTag');
        
        $event = $this->createEvent(null, $docblock);
        $this->listener->handleClassBlockExtraction($event);
        $this->assertNotEmpty($this->listener->getTagsOfType('testTag'));
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

        $this->plugin->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($this->config));

        $translator = $this->getMock('Zend\I18n\Translator\Translator');
        $this->plugin->expects($this->any())
            ->method('getTranslator')
            ->will($this->returnValue($translator));
    }
    
    /**
     * Create a config.
     * 
     * @param array $config
     */
    protected function createConfig(array $config = null)
    {
        if (null === $config) {
            $config = array(
                'plugins' => array(
                    'plugin' => array(
                        'path' => MdMan_Listener::CONFIG_PLUGIN_PATH,
                        'option' => array(
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Configuration::OUTDIR_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => sys_get_temp_dir(),
                            ),
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Configuration::OUTFILE_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => 'testfile.md',
                            ),
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Configuration::WRITER_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => 'WriterMock',
                            ),
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Configuration::WRITER_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => 'WriterMock',
                            ),
                        )
                    )
                )
            );
        }
        
        $this->config = new \Zend\Config\Config($config);
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
        if ($subject == null) {
            $this->createReflector();
            $subject = $this->reflector;
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
    
    /**
     * Creates the tested Listener
     */
    protected function createListener()
    {
        $this->listener = new MdMan_Listener($this->plugin, $this->shellMock);
    }
}

/**
 * Mock for testing.
 */
class WriterMock implements MdMan_Writer
{
    public $executed;
    public $config;
    public $tree;
    public $contentProvider;
    
    public function execute()
    {
        $this->executed = true;
    }

    public function setConfig(\MdMan_Configuration $config)
    {
        $this->config = $config;
    }

    public function setContentProvider(\MdMan_ContentProvider $contentProvider)
    {
        $this->contentProvider = $contentProvider;
    }
}