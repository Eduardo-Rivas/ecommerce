<?php 
//--Iniciamos el Uso de Sesiones--//
session_start();

require_once("vendor/autoload.php");
//require_once("functions.php");

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Slim\Slim;
use Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin/', function() {
    
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login/', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");
});
$app->post('/admin/login', function() {
	//User::login(post('deslogin'), post('despassword'));
	User::login($_POST['login'], $_POST['password']);

	header("Location: /admin/");
	exit;
});

//--Ruta para Deslogar--//
$app->get('/admin/logout', function() {
	User::logout();

	header("Location: /admin/login/");
	exit;
});


//1--Ruta para Consulta de Todos los Usuarios--//
$app->get('/admin/users', function(){
	User::verifyLogin();

	//--Buscamos todos los Usuarios--//
	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));

});

//6--Ruta para Deletar de Usuarios--//
$app->get('/admin/users/:iduser/delete', function($iduser){
	User::verifyLogin();

	$user = new User();

	//--Buscamos los Datos de ese Usuario--//
	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});

//2--Ruta para Cadastrar Usuarios (users.html)--//
$app->get('/admin/users/create', function(){
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});
//4--Ruta para Salovar los Usuarios--//
$app->post('/admin/users/create', function(){
	User::verifyLogin();

	$user = new User();

	//--Verifica y guarda en el $_post--//
	//--de inadmin el inadmon q fué enviado por post--//
	$_POST['inadmin'] = (isset($_POST['inadmin']))?1:0;

	//--Asigmamos los Valores q llegaron por $_POST--//
	$user->setData($_POST);

	$user->save();
	header("Location: /admin/users");
	exit;
});

//3--Ruta para Editar Usuarios (Boton Editar users.html)--//
$app->get('/admin/users/:iduser', function($iduser){
	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin']))?1:0;

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array( 
		"user"=>$user->getValues()
	));
});
//5--Ruta para Salovar la Edición de Usuarios--//
$app->post('/admin/users/:iduser', function($iduser){
	
	User::verifyLogin();

	$user = new User();

	//--Fuí y Tomé los datos de ese Usuario--//
	$user->get((int)$iduser);

	//--Tomo la Informacion del Fromulario--//
	$user->setData($_POST);

	//--Actualizao los Datos del Usaurio--//
	$user->update();

	header("Location: /admin/users");
	exit;
});

//--Ruta Olvidé la senha--//
$app->get('/admin/forgot/', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");	
});
//--Ruta via Post de Olvidé la Senha--//
$app->post('/admin/forgot/', function(){

	//--Recibimos el email y lo enviamos al Método getForgot()--//
    $user = User::getForgot($_POST["email"]);

    //--Enviamos el Email fué enviado con susseso--//
    header("Location: /admin/forgot/sent");
	exit;
});
//--Ruta Vara Notificar q el Email fué envíado--//
$app->get('/admin/forgot/sent', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");		
});
//--Retorno del Usuario a quien se envuó el Email Para Des-sencriptar--//
$app->get('/admin/login/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET['code']
	));		
});
$app->post('/admin/forgot/reset', function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

$app->run();

?>