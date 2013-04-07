MdMan
=====

What is MdMan?
--------------

MdMan stands for "Markdown Manual". It is a plugin for PHPDocumentor 2 which extracts
markdown from class comment docblocks. It grabs the "short" and "long" description and
discards the rest of the API documentation. 
The gathered information is written to one single markdown file which then can be
used as manual. This file can be automatically converted into other formats like 
html, opendocument or pdf using [Pandoc](http://johnmacfarlane.net/pandoc/).


Features
--------

* Hooks into the PHPDoc parsing as a listener plugin
* Extracts the relevant contents before they are html-escaped
* Merges contents ordered by package


Installation
------------

Easy installation via composer: [see this example](https://github.com/bonndan/molcomponents-manual).


For advanced features is requires Python and scruffy for image generation and pandoc for creating pdf files:

```
sudo apt-get install plotutils
sudo apt-get install librsvg2-bin
sudo apt-get install graphviz

git clone clone https://github.com/aivarsk/scruffy
cd scruffy
./setup.py install

sudo apt-get install pandoc
```


To-Do
-----

* [see issue tracker] (https://github.com/bonndan/mdman/issues)
