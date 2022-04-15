<?php namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model 
{

	const SESSION = "User";

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
	];

	public static function login($login, $password):User
	{

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {

			$user = new User();

			$user->setData($data);

			//var_dump($data);
			//exit;

			$_SESSION[User::SESSION] = $user->getValues();
			//var_dump($_SESSION[User::SESSION]);
			//exit;

			return $user;

		} else {

			throw new \Exception("Não foi possível fazer login.");

		}

	}

	public static function verifyLogin()
	{
		
		if (
			!isset($_SESSION[User::SESSION])	//--No Existe la Session--//
			|| 
			!$_SESSION[User::SESSION]	//--Session no está Vacia--//
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0   //--Id del Usuario > 0--//
			||
			(int)$_SESSION[User::SESSION]["inadmin"] !== 1 //--Id Administrador--//
		) {
			header("Location: /admin/login/");
			exit;
		}

	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}


}

?>