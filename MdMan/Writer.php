<?php
/**
 * MdMan_MarkdownTree
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * Interface expected by writers which work on the markdown tree.
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
interface MdMan_Writer
{
    /**
     * Inject a markdown tree.
     * 
     * @param MdMan_MarkdownTree $tree
     */
    public function setMarkdownTree(MdMan_MarkdownTree $tree);
    
    /**
     * Pass the configuration, execute the writer.
     * 
     * @param MdMan_Configuration $config
     */
    public function execute(MdMan_Configuration $config);
}