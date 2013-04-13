
<?php
/**
 * Listener.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

use \phpDocumentor\Plugin\ListenerAbstract;
use \phpDocumentor\Reflection\Event\PostDocBlockExtractionEvent;

/**
 * # MdMan Listener #
 * 
 * Listener which extracts Markdown from class docblocks.
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
class MdMan_Listener extends ListenerAbstract implements MdMan_MarkdownTree, MdMan_Configuration
{
    /**
     * suml annotations
     */
    const SUML_BLOCK = "suml";
    
    /**
     * plugin path to identify the plugin, assumes composer installation
     */
    const CONFIG_PLUGIN_PATH = 'vendor/bonndan/MdMan';
    const CONFIG_PLUGIN_OPTION_NAME = 'name';
    const CONFIG_PLUGIN_OPTION_VALUE = 'value';
    
    /**
     * configuration of the plugin
     * @var \Zend\Config\Config
     */
    protected $pluginConfig = null;
    
    /**
     * shell wrapper
     * @var \Bart\Shell
     */
    protected $shell = null;
    
    /**
     * package => entries
     * @var array
     */
    protected $packages = array();

    /**
     * Writers for markdown export.
     * @var array
     */
    protected $writers = array();
    
    /**
     * Constructor. Pass a shell wrapper for testing.
     * 
     * @param Plugin $plugin
     * @param \Bart\Shell $shell
     * @throws \LogicException
     */
    public function __construct($plugin, \Bart\Shell $shell = null)
    {
        parent::__construct($plugin);
        
        if ($shell === null) {
            $shell = new \Bart\Shell();
        }
        $this->shell = $shell;
        
        /* @var $config \Zend\Config\Config */
        $config = $this->plugin->getConfiguration();
        foreach ($config->plugins as $plugin) {
            if ($plugin->path == self::CONFIG_PLUGIN_PATH) {
                $this->pluginConfig = $plugin;
                break;
            }
        }
        
        if ($this->pluginConfig === null) {
            throw new \LogicException(
                'No plugin config found. At least outDir / outFile configuration is required.'
            );
        }
    }
    
    /**
     * Fetches markdown blocks from class docblocks.
     *
     * @param PostDocBlockExtractionEvent $data Event object 
     *
     * @phpdoc-event reflection.docblock-extraction.post
     *
     * @return void
     */
    public function fetchMarkdownFromClassDocBlock(PostDocBlockExtractionEvent $data)
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
        $markDown = $docblock->getShortDescription() . PHP_EOL;
        $longDesc = $docblock->getLongDescription();
        if (is_object($longDesc)) {//api is unclear
            $longDesc = $longDesc->getContents();
        }
        $markDown .= $longDesc . PHP_EOL;
        
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
     * Loads all the configured writer in the given sequence and executes them.
     * 
     * @phpdoc-event transformer.transform.pre
     */
    public function runExports()
    {
        $writers = $this->getConfiguredWriters();
        foreach ($writers as $writerName) {
            $writer = MdMan_Writer_Abstract::create($writerName);
            $this->writers[$writerName] = $writer;
            
            $this->writers[$writerName]->setConfig($this);
            $this->writers[$writerName]->setMarkdownTree($this);
            $this->writers[$writerName]->execute();
        }
    }
    
    /**
     * Retrieves a plugin option from the global configuration.
     * 
     * @param string $key
     * @param mixed  $default
     * @return string|null
     */
    public function getOption($key, $default = null)
    {
        foreach ($this->pluginConfig->option as $option) {
            if ($option->get(self::CONFIG_PLUGIN_OPTION_NAME) == $key) {
                return $option->get(self::CONFIG_PLUGIN_OPTION_VALUE);
            }
        }
        
        return $default;
    }
    
    /**
     * Returns the class names of the configured writers.
     * 
     * @return string[]
     */
    protected function getConfiguredWriters()
    {
        $writers = array();
        foreach ($this->pluginConfig->option as $option) {
            if ($option->get(self::CONFIG_PLUGIN_OPTION_NAME) == MdMan_Configuration::WRITER_OPTION) {
                $writers[] = $option->get(self::CONFIG_PLUGIN_OPTION_VALUE);
            }
        }
        
        return $writers;
    }
    
    /**
     * Returns the package/class tree of markdown contents.
     * 
     * @return array
     */
    public function getTree()
    {
        return $this->packages;
    }
    
    /**
     * Returns the used writers (non-empty after runExport has been called).
     * 
     * @return array
     */
    public function getWriters()
    {
        return $this->writers;
    }
}