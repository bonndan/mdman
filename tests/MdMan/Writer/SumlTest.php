<?php

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the suml writer.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_Writer_SumlTest extends PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MdMan_Writer_Suml 
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
        $this->writer = new MdMan_Writer_Suml($this->shellMock);
    }
    
    /**
     * Ensures the suml tags are collected.
     */
    public function testTriggersTagCollection()
    {
        $provider = $this->getMock('MdMan_ContentProvider');
        $provider->expects($this->any())
            ->method('collectTagsOfType')
            ->with(MdMan_Writer_Suml::SUML_BLOCK);
        
        $this->writer->setContentProvider($provider);
    }
    
    /**
     * Ensures execute
     */
    public function testExecute()
    {
        $this->shellMock->expects($this->once())
            ->method('exec');
     
        $docblock = "/**
 * @suml some/file.png
 * [a] -> [b]
 */
";
        $provider = $this->getMock('MdMan_ContentProvider');
        $provider->expects($this->any())
            ->method('getTagsOfType')
            ->with(MdMan_Writer_Suml::SUML_BLOCK)
            ->will($this->returnValue(
                array(
                    new \phpDocumentor\Reflection\DocBlock\Tag('suml', $docblock)
                )
             ));
        
        $this->writer->setContentProvider($provider);
        $this->writer->execute();
    }
}

