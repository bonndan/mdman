<?php

require_once __DIR__ . '/bootstrap.php';

/**
 * MdMan_Writer_ByPackageAndClassTest
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_Writer_ByPackageAndClassTest extends PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MdMan_Writer_ByPackageAndClass 
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
        $this->writer = new MdMan_Writer_ByPackageAndClass($this->shellMock);
    }
    
    /**
     * Ensure the pandoc command is called properly.
     */
    public function testExportsMarkdown()
    {
        $this->shellMock->expects($this->once())
            ->method('file_put_contents')
            ->with('/tmp/testfile.md', $this->getTargetMarkdown());
        
        $configMock = $this->getMock('MdMan_Configuration');
        $configMock->expects($this->at(0))
            ->method('getOption')
            ->with(MdMan_Configuration::OUTDIR_OPTION)
            ->will($this->returnValue(sys_get_temp_dir()));
        $configMock->expects($this->at(1))
            ->method('getOption')
            ->with(MdMan_Configuration::OUTFILE_OPTION)
            ->will($this->returnValue('testfile.md'));
        $this->writer->setConfig($configMock);
        
        $tree = $this->getMock('MdMan_MarkdownTree');
        $tree->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue(
                array('apackage' => array('aclass' => $this->getMarkdown()))
             ));
        
        $this->writer->setMarkdownTree($tree);
        $this->writer->execute();
    }
    
    /**
     * Markdown with headings.
     * @return string
     */
    protected function getMarkdown()
    {
        return "
# heading1 #

some text

## heading2 ##
";
    }
    
    /**
     * Markdown with headings.
     * @return string
     */
    protected function getTargetMarkdown()
    {
        return "

# apackage #


## aclass ##


### heading1 ###

some text

#### heading2 ####
";
    }
}

