<?php
/**
 * MdMan_ContentProvider
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
interface MdMan_Writer
{
    /**
     * Inject a content provider
     * 
     * @param MdMan_ContentProvider $contentProvider
     */
    public function setContentProvider(MdMan_ContentProvider $contentProvider);
    
    /**
     * Inject a config.
     * 
     * @param MdMan_Configuration $config
     */
    public function setConfig(MdMan_Configuration $config);
        
    /**
     * Execute the writer.
     * 
     */
    public function execute();
}