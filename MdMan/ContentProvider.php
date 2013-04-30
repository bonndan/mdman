<?php
/**
 * MdMan_ContentProvider
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
interface MdMan_ContentProvider
{
    /**
     * Returns an associative array of packages and the classes' markdown
     * 
     * @return array (package => class => markdown)
     */
    public function getTree();
    
    /**
     * Instructs the content provider to collect certain blocks.
     * 
     * @param string $tagName
     */
    public function collectTagsOfType($tagName);
    
    /**
     * Returns the blocks of a certain type.
     * 
     * @param string $tagName
     * @return \phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getTagsOfType($tagName);
}