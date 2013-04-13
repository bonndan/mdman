<?php
/**
 * DegradeHeadings.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * The writer that degrades the class markdown headings by two levels (to match
 * the automatically inserted
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
class MdMan_Writer_ByPackageAndClass extends MdMan_Writer_Abstract
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
                $contents .= $this->getMarkdownWithDegradedHeadings($class);
            }
        }
        
        $target = $this->getOutputTarget();
        $this->shell->file_put_contents($target, $contents);
    }
    
    /**
     * Replaces the first and second level heading.
     * 
     * @param string $content
     * @return string
     */
    protected function getMarkdownWithDegradedHeadings($content)
    {
        $pattern     = "/( ?)(#{1,})( ?)/";
        $replacement = "$1#$2#$3";
        
        $content = preg_replace($pattern, $replacement, $content);
        
        return $content;
    }
}