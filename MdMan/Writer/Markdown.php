<?php
/**
 * Markdown.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * The writer that generates one markdown file without modifying the contents.
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
class MdMan_Writer_Markdown extends MdMan_Writer_Abstract
{
    /**
     * Write markdown into the outfile.
     * 
     * 
     */
    public function execute()
    {
        $contents = '';
        $tree = $this->tree->getTree();
        foreach ($tree as $package) {
            foreach ($package as $class) {
                $contents .= PHP_EOL . $class;
            }
        }
        
        $target = $this->getOutputTarget();
        $this->shell->file_put_contents($target, $contents);
    }
}