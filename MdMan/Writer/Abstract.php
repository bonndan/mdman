<?php
/**
 * Abstract.php
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */

/**
 * Abstract writer class.
 * 
 * @package MdMan
 * @author  Daniel Pozzi <bonndan76@googlemail.com>
 */
abstract class MdMan_Writer_Abstract implements MdMan_Writer
{
    /**
     * the markdown tree
     * @var MdMan_MarkdownTree 
     */
    protected $tree;
    
    /**
     * the plugin configuration
     * @var MdMan_Configuration $config
     */
    protected $config;
    
    /**
     * shell abstraction layer.
     * @var \Bart\Shell 
     */
    protected $shell;
    
    /**
     * Returns an instance of itself.
     * 
     * @return MdMan_Writer_*
     */
    public static function create()
    {
        return new static(new \Bart\Shell());
    }
    
    /**
     * Pass a shell instance.
     * 
     * @param \Bart\Shell $shell
     */
    public function __construct(\Bart\Shell $shell)
    {
        $this->shell = $shell;
    }
    
    /**
     * Inject the markdown into the writer.
     * 
     * @param \MdMan_MarkdownTree $tree
     */
    public function setMarkdownTree(MdMan_MarkdownTree $tree)
    {
        $this->tree = $tree;
    }
    
    /**
     * Inject the configuration into the writer.
     * 
     * @param MdMan_Configuration $config
     */
    public function setConfig(MdMan_Configuration $config)
    {
        $this->config = $config;
    }
    
    /**
     * Returns the path of the output target.
     * 
     * @return string
     */
    protected function getOutputTarget()
    {
        $this->assertConfigHasBeenSet();
        $outDir  = $this->config->getOption(MdMan_Configuration::OUTDIR_OPTION);
        $outFile = $this->config->getOption(MdMan_Configuration::OUTFILE_OPTION);
        $target = $outDir . DIRECTORY_SEPARATOR . $outFile;
        
        return $target;
    }
    
    /**
     * Ensures the config is present.
     * 
     * @throws RuntimeException
     */
    protected function assertConfigHasBeenSet()
    {
        if ($this->config === null) {
            throw new RuntimeException('Config is missing in writer.');
        }
    }
    
    /**
     * Implement the execution function.
     * 
     * @param MdMan_Configuration $config
     */
    abstract public function execute();
}