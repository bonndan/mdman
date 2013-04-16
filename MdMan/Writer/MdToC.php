<?php
/**
 * Markdown.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * The writer that generates table of content in markdown files (must be used
 * after markdown generation, should be used after pdf generation).
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
class MdMan_Writer_MdToC extends MdMan_Writer_Abstract
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
        foreach ($tree as $packageName => $package) {
            $contents .= "[$packageName][]" . PHP_EOL;
            foreach ($package as $className => $nil) {
                $contents .= "[$className][]" . PHP_EOL;
            }
        }
        
        $target = $this->getOutputTarget();
        $fileContents = $this->shell->file_get_contents($target);
        $this->shell->file_put_contents($target, $contents . PHP_EOL . $fileContents);
    }
}

