<?php 
namespace Hcode;

class PageAdmin extends Page {

	//--$tpl_dir Tiene la Ruta Donde están los Templeite de Admin--//
	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		parent::__construct($opts, $tpl_dir);
	}

}

?>
