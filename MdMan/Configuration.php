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
     * pandoc command line call options
     * <code>
     * --indented-code-classes=php,numberLines --highlight-style=tango -f markdown+lhs --listings
     * </code>
     * @var string 
     */
    const PANDOC_OPTIONS = 'pandoc-options';
    
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