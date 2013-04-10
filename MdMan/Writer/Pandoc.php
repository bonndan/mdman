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
        $template = $this->config->getOption(MdMan_Configuration::PANDOC_TEMPLATE_OPTION) ? 
            '--template=' . $this->config->getOption(MdMan_Configuration::PANDOC_TEMPLATE_OPTION) : '';
        
        $target = $this->getOutputTarget();
        $this->shell->exec('pandoc ' . $template . ' ' . $target . ' -o ' . $target . '.pdf');
    }

}