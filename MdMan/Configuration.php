<?php
/**
 * Plugin configuration interface.
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
interface MdMan_Configuration
{
    /**
     * The Pandoc / LateX sty file to use.
     * @var string
     */
    const PANDOC_TEMPLATE_OPTION = 'pandoc-template';
    
    /**
     * Element that adds writers to the plugin.
     * @var string
     */
    const WRITER_OPTION = 'writer';
    
    /**
     * output options names
     */
    const OUTDIR_OPTION = 'outDir';
    const OUTFILE_OPTION = 'outFile';
    
    /**
     * Returns a configuration option if available.
     * 
     * @param string $key
     * @param string $default
     * 
     * @return string|null
     */
    public function getOption($key, $default = null);
}