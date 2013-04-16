<?php

require_once __DIR__ . '/bootstrap.php';

/**
 * Tests the writer the generates markdown table of contents.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_Writer_MdToCTest extends PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MdMan_Writer_MdToC
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
        $this->writer = new MdMan_Writer_MdToC($this->shellMock);
    }
    
    /**
     * Ensure the pandoc command is called properly.
     */
    public function testWritesTableOfContents()
    {
        $expectedMarkdown = "[apackage][]
[aclass][]

original markdown";
        $this->shellMock->expects($this->once())
            ->method('file_put_contents')
            ->with('/tmp/testfile.md', $expectedMarkdown);
        $this->shellMock->expects($this->once())
            ->method('file_get_contents')
            ->with('/tmp/testfile.md')
            ->will($this->returnValue("original markdown"));
        
        $configMock = $this->getConfig();
        $this->writer->setConfig($configMock);
        
        $treeData = array('apackage' => array('aclass' => 'Test'));
        $tree = $this->getMock('MdMan_MarkdownTree');
        $tree->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($treeData));
        
        $this->writer->setMarkdownTree($tree);
        $this->writer->execute();
    }
    
    /**
     * Creates a mock config.
     * 
     */
    protected function getConfig()
    {
        $configMock = $this->getMock('MdMan_Configuration');
        $configMock->expects($this->at(0))
            ->method('getOption')
            ->with(MdMan_Configuration::OUTDIR_OPTION)
            ->will($this->returnValue(sys_get_temp_dir()));
        $configMock->expects($this->at(1))
            ->method('getOption')
            ->with(MdMan_Configuration::OUTFILE_OPTION)
            ->will($this->returnValue('testfile.md'));
        
        return $configMock;
    }
}

