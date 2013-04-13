<?php
/**
 * Listener.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * The pandoc writer calls pandoc for pdf generation.
 * 
 * Possible options for pandoc:
 * 
 * <code>
 * --indented-code-classes=php,numberLines --highlight-style=tango -f markdown+lhs --listings
 * </code>
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
class MdMan_Writer_Pandoc extends MdMan_Writer_Abstract
{
    /**
     * Execute calls pandoc.
     */
    public function execute()
    {
        $options = $this->config->getOption(MdMan_Configuration::PANDOC_OPTIONS);
        
        $target = $this->getOutputTarget();
        $this->shell->exec('pandoc ' . $options . ' ' . $target . ' -o ' . $target . '.pdf');
    }

}