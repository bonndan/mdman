<?php
/**
 * Markdown.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * The writer that generates one markdown file.
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
        foreach ($tree as $packageName => $package) {
            $contents .= PHP_EOL . PHP_EOL .'# ' . $packageName . ' #' . PHP_EOL;
            foreach ($package as $className => $class) {
                $contents .= PHP_EOL . PHP_EOL . '## ' . trim($className, "\\") . ' ##' . PHP_EOL;
                $contents .= $class;
            }
        }
        
        $target = $this->getOutputTarget();
        $this->shell->file_put_contents($target, $contents);
    }
}