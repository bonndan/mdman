<?php

require_once __DIR__ . '/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/MdMan/Listener.php';
require_once dirname(dirname(__DIR__)) . '/MdMan/SumlBlock.php';

use phpDocumentor\Reflection\DocBlock;

/**
 * MdMan_SumlBlockTest
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan_Tests
 */
class MdMan_SumlBlockTest extends PHPUnit_Framework_TestCase
{
    /**
     * Ensures the file name on the same line as the annotation is read.
     */
    public function testAnnotationLineFileNameIsRead()
    {
        $sumlBlock = new MdMan_SumlBlock($this->getSumlTag());
        $this->assertAttributeEquals('testfolder/testfile.png', 'destinationFile', $sumlBlock);
    }
    
    /**
     * Ensures the arguments are extracted properly.
     */
    public function testAnnotationArgsAreRead()
    {
        $sumlBlock = new MdMan_SumlBlock($this->getSumlTag());
        $this->assertAttributeContains('--png', 'args', $sumlBlock);
        $this->assertAttributeContains('--otherarg', 'args', $sumlBlock);
    }
    
    /**
     * Ensures an exception is thrown if no file name was found.
     */
    public function testExceptionIfFileNameIsMissing()
    {
        $content = "
/**
 * Test
 *
 * @package something
 *
 * @suml [--png] [--otherarg]
 * [one] -> [two]
 * [two] -> [three]
 */
";
        $block = new DocBlock($content);
        $this->setExpectedException("\RuntimeException");
        new MdMan_SumlBlock($this->getSumlTag($block));
    }
    
    /**
     * Ensures an exception if the first line is completely empty.
     */
    public function testExceptionIfFirstLineEmpty()
    {
        $content = "
/**
 * Test
 *
 * @package something
 *
 * @suml
 * [one] -> [two]
 * [two] -> [three]
 */
";
        $block = new DocBlock($content);
        $this->setExpectedException("\RuntimeException");
        new MdMan_SumlBlock($this->getSumlTag($block));
    }
    
    /**
     * Ensures the whole suml content is read
     */
    public function testEntireContentIsRead()
    {
        $sumlBlock = new MdMan_SumlBlock($this->getSumlTag());
        $output = $sumlBlock->getCommand();
        $this->assertContains('[one] -> [two]', $output);
        $this->assertContains('[two] -> [three]', $output);
    }
    
    /**
     * Returns the suml tag
     * 
     * @return \phpDocumentor\Reflection\DocBlock\Tag
     */
    protected function getSumlTag($block = null)
    {
        if ($block === null) {
            $block = $this->getDocBlock();
        }
        $sumls = $block->getTagsByName(MdMan_Listener::SUML_BLOCK);
        return current($sumls);
    }
    
    /**
     * Creates a dockblock with a valid suml entry.
     * 
     * @return DocBlock
     */
    protected function getDocBlock()
    {
        $content = "
/**
 * Test
 *
 * @package something
 *
 * @suml [--png] [--otherarg] testfolder/testfile.png
 * [one] -> [two]
 * [two] -> [three]
 */
";
        $block = new DocBlock($content);
        return $block;
    }
}