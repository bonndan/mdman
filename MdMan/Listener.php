<?php

use \phpDocumentor\Plugin\ListenerAbstract;

/**
 * MdMan Listener
 */
class MdMan_Listener extends ListenerAbstract
{
    /**
     * suml annotations
     */
    const SUML_BLOCK = "suml";
    
    /**
     * The Pandoc / LateX sty file to use.
     * @var string
     */
    const PANDOC_TEMPLATE_OPTION = 'pandoc-template';
    
    /**
     * package => entries
     * @var array
     */
    protected $packages = array();

    /**
     * Fetches markdown blocks from class docblocks.
     *
     * @param PostDocBlockExtractionEvent $data Event object 
     *
     * @phpdoc-event reflection.docblock-extraction.post
     *
     * @return void
     */
    public function fetchMarkdownFromClassDocBlock($data)
    {
        /* @var $element \phpDocumentor\Reflection\BaseReflector */
        $element = $data->getSubject();
        if (!$element instanceof \phpDocumentor\Reflection\ClassReflector) {
            return;
        }
        $package = $element->getDefaultPackageName();
        $class   = $element->getName();

        /* @var $docblock \phpDocumentor\Reflection\DocBlock */
        $docblock = $data->getDocblock();
        if ($docblock->hasTag('package')) {
            $packages = $docblock->getTagsByName('package');
            $package  = current($packages)->getContent();
        }
        $markDown = $docblock->getShortDescription() . PHP_EOL . $docblock->getLongDescription()->getContents();
        $this->packages[$package][$class] = $markDown;
    }

    /**
     * Checks all phpDocumentor whether they match the given rules.
     *
     * @param PostDocBlockExtractionEvent $data Event object containing the
     *     parameters.
     *
     * @phpdoc-event reflection.docblock-extraction.post
     *
     * @return void
     */
    public function fetchSUMLFromClassBlock($data)
    {
        /** @var $element \phpDocumentor\Reflection\BaseReflector */
        $element = $data->getSubject();
        if (!$element instanceof \phpDocumentor\Reflection\ClassReflector) {
            return;
        }

        /** @var $docblock \phpDocumentor\Reflection\DocBlock */
        $docblock = $data->getDocblock();
        if (!$docblock->hasTag(self::SUML_BLOCK)) {
            return;
        }

        $tags = $docblock->getTagsByName(self::SUML_BLOCK);
        /* @var $tag \phpDocumentor\Reflection\DocBlock_Tag[] */
        foreach ($tags as $tag) {
            $suml = $tag->getContent();
        }
    }

    /**
     * Dumps the packages before transformation.
     * 
     * @phpdoc-event transformer.transform.pre
     */
    public function exportMarkdown()
    {
        $options = $this->plugin->getOptions();
        $outDir  = isset($options['outDir']) ? $options['outDir'] : 'manual';
        $outFile = isset($options['outFile']) ? $options['outFile'] : 'manual.md';
        
        $contents = '';
        foreach ($this->packages as $packageName => $package) {
            $contents .= '# ' . $packageName . ' #' . PHP_EOL;
            foreach ($package as $className => $class) {
                $contents .= '## ' . $className . ' ##' . PHP_EOL;
                $contents .= $class;
            }
        }
        file_put_contents($outDir . '/' . $outFile, $contents);
    }
    
    /**
     * Runs pandoc.
     * 
     * @phpdoc-event transformer.transform.pre
     */
    public function createPDFUsingPandoc()
    {
        $options = $this->plugin->getOptions();
        $outDir  = isset($options['outDir']) ? $options['outDir'] : 'manual';
        $outFile = isset($options['outFile']) ? $options['outFile'] : 'manual.md';
        
        $template = isset($options[self::PANDOC_TEMPLATE_OPTION]) ? 
            '--template=' . $options[self::PANDOC_TEMPLATE_OPTION] : '';
        
        exec('cd ' .$outDir . ' && pandoc ' . $template . ' ' . $outFile . ' -o ' . $outFile . '.pdf');
    }
}