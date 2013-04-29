<?php
/**
 * DegradeHeadings.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * The writer that degrades the class markdown headings by two levels (to match
 * the automatically inserted headings). It also discards any markdown inserted
 * before the first class markdown heading.
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
        $tree = $this->contentProvider->getTree();
        foreach ($tree as $packageName => $package) {
            $contents .= PHP_EOL . PHP_EOL .'# ' . $packageName . ' #' . PHP_EOL;
            foreach ($package as $className => $class) {
                $contents .= PHP_EOL . PHP_EOL . '## ' . trim($className, "\\") . ' ##' . PHP_EOL . PHP_EOL;
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
        $content = $this->clip($content);
        
        $pattern     = "/( ?)(#{1,})( ?)/";
        $replacement = "$1#$2#$3";
        
        return preg_replace($pattern, $replacement, $content);
    }

    /**
     * Discards any content before the first hash.
     * 
     * @param string $content
     * @return string
     */
    protected function clip($content)
    {
        /* if the strpos is false, is is treated as zero */
        $firstHash = (int)strpos($content, '#');
        
        return PHP_EOL . substr($content, $firstHash);
    }
}