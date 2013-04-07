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
        //$longDescription = new \phpDocumentor\Reflection\DocBlock\LongDescription();
        $docblock->expects($this->once())
            ->method('getLongDescription')
            ->will($this->returnValue(null));
        
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        
        $event = $this->createEvent(null, $docblock);
        $this->listener->fetchMarkdownFromClassDocBlock($event);
    }

    /**
     * Ensures a given config option is read properly
     */
    public function testReadsConfigurationOption()
    {
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->listener->exportMarkdown();
    }
    
    /**
     * Ensures the pandoc command is called.
     */
    public function testCreatePDFUsingPandoc()
    {
        $this->shellMock->expects($this->once())
            ->method('exec')
            ->with($this->equalTo('pandoc  /tmp/testfile.md -o /tmp/testfile.md.pdf'));
        
        $this->createConfig();
        $this->createPlugin();
        $this->createListener();
        $this->listener->createPDFUsingPandoc();
    }
    
    /**
     * Ensures the pandoc command is not called if the config disables it.
     */
    public function testCreatePDFUsingPandocDisabled()
    {
        $this->shellMock->expects($this->never())
            ->method('exec');
        $config = array(
                'plugins' => array(
                    'plugin' => array(
                        'path' => MdMan_Listener::CONFIG_PLUGIN_PATH,
                        'option' => array(
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::OUTDIR_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => sys_get_temp_dir(),
                            ),
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::USE_PANDOC_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => false,
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
        $this->createListener();
        $this->listener->createPDFUsingPandoc();
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
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::OUTDIR_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => sys_get_temp_dir(),
                            ),
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::USE_PANDOC_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => true,
                            ),
                            array(
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_NAME => MdMan_Listener::OUTFILE_OPTION,
                                MdMan_Listener::CONFIG_PLUGIN_OPTION_VALUE => 'testfile.md',
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
