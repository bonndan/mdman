<?php

/**
 * SumlBlock
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @package MdMan
 */
use \phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Represents the content of a suml annotation
 * 
 * <code>
 * @suml [--png] ppdoc.png
 *  
 * [Console Command] uses -> [DirectoryIterator],
 * [DirectoryIterator] -*> [Scanning Strategy],
 * [Scanning Strategy] finds -*> [Markdown blocks],
 * [Markdown blocks] write -> [Markdown files],
 * [Scanning Strategy] finds -*> [UML blocks],
 * [UML blocks] generate -> [png]
 * </code>
 * 
 */
class MdMan_SumlBlock
{
    const SUML_COMMAND = 'suml';
    
    /**
     * suml command line arguments
     * @var array (string)
     */
    private $args = array();

    /**
     * path and name of the destination file
     * @var string 
     */
    private $destinationFile = null;
    
    /**
     * Suml content.
     * @var string
     */
    private $content = "";

    /**
     * Pass the first line of the ppDocSuml block to the constructor. 
     * 
     * @param \phpDocumentor\Reflection\DocBlock\Tag $tag
     * @throws \RuntimeException
     */
    public function __construct(Tag $tag)
    {
        $lines = explode(PHP_EOL, $tag->getDescription());
        $this->scanFirstLine($lines[0]);

        if ($this->destinationFile === null) {
            throw new RuntimeException(
            'First line of suml block must contain the file name. Location: '
            . $tag->getLocation()
            );
        }
        
        for ($i = 1; $i < count($lines); $i++) {
            $this->content .= $lines[$i];
        }
    }
    
    /**
     * Scans the first line for options and/or the destination file name.
     * 
     * @param string $line
     */
    protected function scanFirstLine($line)
    {
        $words = explode(' ', $line);
        foreach ($words as $word) {
            $first = $word[0];
            if ($first == '[') {
                $arg = trim($word, '[]');
                $this->args[] = $arg;
            } else {
                $this->destinationFile = $word;
            }
        }
    }

    /**
     * returns the command to generate the image
     * 
     * @throws \LogicException
     */
    public function getCommand()
    {
        $command = self::SUML_COMMAND;
        $command .= ' ' . implode(' ', $this->args) . ' ';
        $command .= '"' . str_replace(PHP_EOL, '', $this->content) . '"';
        $command .= ' > ' . $this->destinationFile;

        return $command;
    }

}