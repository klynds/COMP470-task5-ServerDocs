<?php
session_start(); // used for quiz marking

class template
{
    var $assignedValues = array();
    var $tpl;

    function __construct($_path = ' ') // class constructor
    {

        if(!empty($_path))
        {
            if(file_exists($_path))
            {
                $this->tpl = file_get_contents($_path);
            }
            else
            {
                echo "<b>Template Error:</b> File not included.";
            }
        }
    }
    function assign($_searchString, $_replaceString) // assigns strings to be replaced within template
    {
        if(!empty($_searchString))
        {
            $this->assignedValues[strtoupper($_searchString)] = $_replaceString;
        }
    }
    function show() //performs text replacements
    {
        if(count($this->assignedValues) > 0)
        {
            foreach ($this->assignedValues as $key => $value)
            {
                $this->tpl = str_replace('{'.$key.'}', $value, $this->tpl);
            }
        }
        echo $this->tpl;
    }
}