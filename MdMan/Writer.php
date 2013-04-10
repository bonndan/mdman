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
     * Factory method.
     */
    public static function create();
    
    /**
     * Inject a markdown tree.
     * 
     * @param MdMan_MarkdownTree $tree
     */
    public function setMarkdownTree(MdMan_MarkdownTree $tree);
    
    /**
     * Inject a config.
     * 
     * @param MdMan_Configuration $config
     */
    public function setConfig(MdMan_Configuration $config);
        
    /**
     * Execute the writer.
     * 
     */
    public function execute();
}