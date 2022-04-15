<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

//--Ruta para El Site de Tienda Virtuel--//
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

//--Ruta de Admin--//
$app->get('/admin/', function() {

	//User::verifyLogin();

    //--Nombre de la Clase donde están       --//
    //-- espcificados los Templeite correctos--//
	$page = new PageAdmin();

	//--Nombre del Templeite Cuerpo de la Página--//
	$page->setTpl("index");

});

//--Ruta de Admin/login --//
$app->get('/admin/login/', function() {
    
	//--Deshabilitamos el Hader y Fotter--//
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	//--Nombre del Templeite Cuerpo de la Página--//
	$page->setTpl("login");
});
$app->post('/admin/login', function(){
	//--Método statico q vá a recibe logion y pass--//
	User::login($_POST['login'], $_POST['password']);

	//--Si no hay Errores Todo Bien--//
	header("Location : /admin/");
	exit;

});

$app->run();

?>
