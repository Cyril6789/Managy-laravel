<?php session_start();

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 19/02/2017
 * Time: 12:42
 */
class elFinderPerso
{
    private $tab_path;
    private $i=0;

    public function __construct()
    {
        $this->tab_path = Array();
    }

    public function addPath($path, $alias='')
    {


        $this->tab_path[$this->i]['path'] = sha1($_SESSION['cal']).'/'.$path;
        $this->tab_path[$this->i]['alias'] = $alias;



        $racine = './../files_managy/'.sha1($_SESSION['cal']);
        if(!is_dir($racine))
            mkdir($racine);

        $t = explode('/', $path);
        for($i=0; $i<count($t); $i++)
        {
            $link = $racine;
            for($j=0; $j<=$i; $j++)
                $link .= '/'.$t[$j];
            if(!is_dir($link))
                mkdir($link);
        }
        $this->i++;
    }

    public function getTabPath()
    {
        return $this->tab_path;
    }

    function __toString()
    {
        $_SESSION['path'] = $this->tab_path;


        return '
                
				<div id="elfinder"></div>
                ';
    }

}