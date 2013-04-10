<?php

require_once __DIR__ . '/bootstrap.php';

/**
 * MdMan_Writer_PandocTest
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_Writer_PandocTest extends PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MdMan_Writer_Pandoc 
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
        $this->writer = new MdMan_Writer_Pandoc($this->shellMock);
    }
    
    /**
     * Ensure the pandoc command is called properly.
     */
    public function testCallsPandoc()
    {
        $this->shellMock->expects($this->once())
            ->method('exec')
            ->with($this->equalTo('pandoc  /tmp/testfile.md -o /tmp/testfile.md.pdf'));
        
        $configMock = $this->getMock('MdMan_Configuration');
        $configMock->expects($this->at(0))
            ->method('getOption')
            ->with(MdMan_Configuration::PANDOC_TEMPLATE_OPTION)
            ->will($this->returnValue(null));
        $configMock->expects($this->at(1))
            ->method('getOption')
            ->with(MdMan_Configuration::OUTDIR_OPTION)
            ->will($this->returnValue('/tmp'));
        $configMock->expects($this->at(2))
            ->method('getOption')
            ->with(MdMan_Configuration::OUTFILE_OPTION)
            ->will($this->returnValue('testfile.md'));
        $this->writer->setConfig($configMock);
        
        $this->writer->execute();
    }
}

