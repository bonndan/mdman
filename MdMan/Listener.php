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
        $outDir  = $this->getOption('outDir','manual');
        $outFile = $this->getOption('outFile','manual.md');
        
        $contents = '';
        foreach ($this->packages as $packageName => $package) {
            $contents .= PHP_EOL . PHP_EOL .'# ' . $packageName . ' #' . PHP_EOL;
            foreach ($package as $className => $class) {
                $contents .= PHP_EOL . PHP_EOL . '## ' . trim($className, "\\") . ' ##' . PHP_EOL;
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
        $outDir  = $this->getOption('outDir','manual');
        $outFile = $this->getOption('outFile','manual.md');
        
        $template = $this->getOption(self::PANDOC_TEMPLATE_OPTION) ? 
            '--template=' . $this->getOption(self::PANDOC_TEMPLATE_OPTION) : '';
        
        $target = $outDir . '/' . $outFile;
        exec('pandoc ' . $template . ' ' . $target . ' -o ' . $target . '.pdf');
    }
    
    /**
     * Retrieves a plugin option from the global configuration.
     * 
     * @param string $key
     * @param mixed  $default
     * @return string|null
     */
    protected function getOption($key, $default = null)
    {
        $config = $this->plugin->getConfiguration();
        foreach ($config->plugins as $plugin) {
            if ($plugin->path != 'vendor/bonndan/MdMan') {
                continue;
            }
            
            $option = $plugin->option;
            foreach ($option as $opt) {
                if ($opt->name == $key) {
                    return $opt->value;
                }
            }
        }
        
        return $default;
    }
}