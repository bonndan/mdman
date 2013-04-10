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
interface MdMan_MarkdownTree
{
    /**
     * Returns an associative array of packages and the classes' markdown
     * 
     * @return array (package => class => markdown)
     */
    public function getTree();
}