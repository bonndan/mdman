<?php
/**
 * Abstract.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * Abstract writer class.
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
abstract class MdMan_Writer_Abstract implements MdMan_Writer
{
    /**
     * the markdown tree
     * @var \MdMan_MarkdownTree 
     */
    protected $tree;
    
    /**
     * Inject the markdown into the writer.
     * 
     * @param \MdMan_MarkdownTree $tree
     */
    public function setMarkdownTree(\MdMan_MarkdownTree $tree)
    {
        $this->tree = $tree;
    }
    
    /**
     * Implement the execution function.
     * 
     * 
     */
    abstract public function execute();
}