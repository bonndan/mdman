
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
class MdMan_Listener extends ListenerAbstract implements MdMan_ContentProvider, MdMan_Configuration
{
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
     * @var MdMan_Writer[]
     */
    protected $writers = array();
    
    /**
     * collected tag blocks
     * @var \phpDocumentor\Reflection\DocBlock\Tag[]
     */
    protected $tags = array();
    
    /**
     * tags to collect
     * @var string[]
     */
    protected $collectedTagNames = array();
    
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
        
        $this->createWriters();
    }
    
    /**
     * Initializes the configured writers.
     */
    protected function createWriters()
    {
        $writers = $this->getConfiguredWriters();
        foreach ($writers as $writerName) {
            $writer = MdMan_Writer_Abstract::create($writerName);
            $this->writers[$writerName] = $writer;
            $this->writers[$writerName]->setConfig($this);
            $this->writers[$writerName]->setContentProvider($this);
        }
    }
    
    /**
     * Hook for class docblock extraction.
     *
     * @param PostDocBlockExtractionEvent $data Event object 
     *
     * @phpdoc-event reflection.docblock-extraction.post
     *
     * @return void
     */
    public function handleClassBlockExtraction(PostDocBlockExtractionEvent $data)
    {
        /* @var $element \phpDocumentor\Reflection\BaseReflector */
        $element = $data->getSubject();
        if (!$element instanceof \phpDocumentor\Reflection\ClassReflector) {
            return;
        }
        
        $this->fetchMarkdownFromClassDocBlock($data);
        $this->extractTags($data);
    }
    
    /**
     * Fetches markdown blocks from class docblocks.
     *
     * @param PostDocBlockExtractionEvent $data Event object 
     * @return void
     */
    protected function fetchMarkdownFromClassDocBlock(PostDocBlockExtractionEvent $data)
    {
        $element = $data->getSubject();
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
     * Checks all phpDocumentor tags whether they match the tags to collect.
     *
     * @param PostDocBlockExtractionEvent $data Event object containing the
     *     parameters.
     *
     * @return void
     */
    protected function extractTags(PostDocBlockExtractionEvent $data)
    {
        /** @var $docblock \phpDocumentor\Reflection\DocBlock */
        $docblock = $data->getDocblock();
        
        foreach (array_unique($this->collectedTagNames) as $tagName) {
            if (!$docblock->hasTag($tagName)) {
                continue;
            }
            
            $tags = $docblock->getTagsByName($tagName);
            /* @var $tag \phpDocumentor\Reflection\DocBlock_Tag[] */
            foreach ($tags as $tag) {
                $this->tags[$tagName][] = $tag;
            }
        }
    }

    /**
     * Executes all the configured writers in the given sequence.
     * 
     * @phpdoc-event transformer.transform.pre
     */
    public function runExports()
    {
        foreach ($this->writers as $writer) {
            $writer->execute();
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
     * @return MdMan_Writer[]
     */
    public function getWriters()
    {
        return $this->writers;
    }
    
    /**
     * Adds a tag name to the list of collected blocks.
     * 
     * @param string $tagName
     */
    public function collectTagsOfType($tagName)
    {
        $this->collectedTagNames[] = $tagName;
    }

    /**
     * Returns the collected tags of a given name.
     * 
     * @param string $tagName
     * @return \phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getTagsOfType($tagName)
    {
        if (!isset($this->tags[$tagName])) {
            return array();
        }
        return $this->tags[$tagName];
    }

}