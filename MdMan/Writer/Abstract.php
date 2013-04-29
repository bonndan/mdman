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
     * the markdown content provider
     * @var MdMan_ContentProvider 
     */
    protected $contentProvider;
    
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
     * Returns an instance of the class given as param.
     * 
     * @param $string $writerClass
     * @return MdMan_Writer_Abstract
     */
    public static function create($writerClass)
    {
        return new $writerClass(new \Bart\Shell());
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
     * @param MdMan_ContentProvider $contentProvider
     */
    public function setContentProvider(MdMan_ContentProvider $contentProvider)
    {
        $this->contentProvider = $contentProvider;
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
}