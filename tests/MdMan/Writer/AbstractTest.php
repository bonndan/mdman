<?php

require_once __DIR__ . '/bootstrap.php';

/**
 * MdMan_Writer_PandocTest
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_Writer_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MdMan_Writer_Abstract 
     */
    protected $writer;
    
    /**
     * mock of the shell wrapper
     * @var \Bart\Shell
     */
    protected $shellMock;
    
    public function setUp()
    {
        parent::setUp();
        $this->shellMock = $this->getMock("\Bart\Shell");
        $this->writer = $this->getMockForAbstractClass('MdMan_Writer_Abstract', array($this->getMock("\Bart\Shell")));
    }
    
    /**
     * Ensures the create factory method returns a writer.
     */
    public function testCreate()
    {
        $res = MdMan_Writer_Abstract::create('MdMan_Writer_Markdown');
        $this->assertInstanceOf('MdMan_Writer_Markdown', $res);
    }
    
    /**
     * Ensures the config is present
     */
    public function testInjectConfig()
    {
        $config = $this->getMock('MdMan_Configuration');
        $this->writer->setConfig($config);
        $this->assertAttributeEquals($config, 'config', $this->writer);
    }
    
    /**
     * Ensures the content provider is injected properly
     */
    public function testInjectContentProvider()
    {
        $provider = $this->getMock('MdMan_ContentProvider');
        $this->writer->setContentProvider($provider);
        $this->assertAttributeEquals($provider, 'contentProvider', $this->writer);
    }
}

