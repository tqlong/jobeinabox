<?php

namespace Jobe;

/* ==============================================================
 *
 * C++
 *
 * ==============================================================
 *
 * @copyright  2014 Richard Lobb, University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class CppTask extends LanguageTask
{

    public function __construct($filename, $input, $params)
    {
        parent::__construct($filename, $input, $params);
        $this->default_params['compileargs'] = array(
        '-Wall',
        '-Werror');
    }

    public static function getVersionCommand()
    {
        return array('gcc --version', '/gcc \(.*\) ([0-9.]*)/');
    }

    public function compile()
    {
        $src = basename($this->sourceFileName);
        $this->executableFileName = $execFileName = "$src.exe";
        $compileargs = $this->getParam('compileargs');
        $linkargs = $this->getParam('linkargs');
        $cmd = "g++ " . implode(' ', $compileargs) . " -o $execFileName $src " . implode(' ', $linkargs);
        list($output, $this->cmpinfo) = $this->runInSandbox($cmd);
    }


    // A default name for C++ programs
    public function defaultFileName($sourcecode)
    {
        return 'prog.cpp';
    }


    // The executable is the output from the compilation
    public function getExecutablePath()
    {
        return "./" . $this->executableFileName;
    }


    public function getTargetFile()
    {
        return '';
    }
}
