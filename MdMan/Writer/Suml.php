<?php
/**
 * Writers that renders (s)uml from content of @suml tags.
 * 
 * @package MdMan
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * Writers that renders (s)uml from content of @suml tags.
 * 
 * @package MdMan
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MdMan_Writer_Suml extends MdMan_Writer_Abstract implements MdMan_Writer
{
    /**
     * suml annotations
     */
    const SUML_BLOCK = "suml";
    
    /**
     * Triggers collection of Suml tags.
     * 
     * @param MdMan_ContentProvider $contentProvider
     */
    public function setContentProvider(MdMan_ContentProvider $contentProvider)
    {
        parent::setContentProvider($contentProvider);
        $contentProvider->collectTagsOfType(self::SUML_BLOCK);
    }
    
    /**
     * Renders the suml blocks.
     */
    public function execute()
    {
        $tags = $this->contentProvider->getTagsOfType(self::SUML_BLOCK);
        foreach ($tags as $tag) {
            /* @var $tag \phpDocumentor\Reflection\DocBlock\Tag */
            $block = new MdMan_SumlBlock($tag);
            $this->shell->exec($block->getCommand());
        }
    }
}