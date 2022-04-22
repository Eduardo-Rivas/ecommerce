<?php namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model 
{
	const SESSION = "User";

	//--Esta Clase no se puede subir al Git--//
	const SECRET = "Clave_secrteta_1";
	const SECRET_IV = "Clave_secreta_2";

	protected $fields = [
		"iduser", "idperson", "deslogin", 
		"despassword", "inadmin", "dtergister", 
		"desperson", "desemail", "nrphone"
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

	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users a 
								 INNER JOIN tb_persons b USING(idperson) 
								 ORDER BY b.desperson");

	}

	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin,
													:despassword, :desemail,
													:nrphone, :inadmin)", 
					array(":desperson"=>$this->getdesperson(),	
						  ":deslogin"=>$this->getdeslogin(),
						  ":despassword"=>User::getPasswordHash($this->getdespassword()),
						  ":desemail"=>$this->getdesemail(),
						  ":nrphone"=>$this->getnrphone(),
						  ":inadmin"=>$this->getinadmin()	
		));
		$this->setData($results[0]);
	}

	//--Tomamos los Datos el Usuario Por iduser--//
	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b 
			         USING(idperson) 
			         WHERE a.iduser = :iduser", array(
			         	":iduser"=>$iduser
			         ));

		$this->setData($results[0]);
	}

	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin,
													:despassword, :desemail,
													:nrphone, :inadmin)", 
					array(":iduser"=>$this->getiduser(),
						  ":desperson"=>$this->getdesperson(),	
						  ":deslogin"=>$this->getdeslogin(),
						  ":despassword"=>$this->getdespassword(),
						  ":desemail"=>$this->getdesemail(),
						  ":nrphone"=>$this->getnrphone(),
						  ":inadmin"=>$this->getinadmin()	
		));

		$this->setData($results[0]);
	}

	//--Deletea un Usuario--//
	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}

	public static function getForgot($email)
	{
		$sql = new Sql();

		$result1 = $sql->select("SELECT * FROM tb_persons a 
								INNER JOIN tb_users b USING(idperson) 
								WHERE a.desemail = :email", array(
								":email"=>$email	
		));

		if(count($result1) === 0)
		{
			throw new \Exception("Imposible Recuperar la Senha...");
		}	
		else
		{
			//--Creamos un nuevo Registro en la tabla de recovery de Senhas--//
			$data = $result1[0];
			$result2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER['REMOTE_ADDR']
			));	
			//--El Procedure Insertó y luego hizo un SELECT--//
			if(count($result2) === 0)
			{
				throw new \Exception("Imposible Recuperar la Senha...");
			}	
			else
			{
				$dataRecovery = $result2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 
										'AES-128-CBC', 
										pack("a16", User::SECRET), 
										0, pack("a16", User::SECRET_IV)
				);

				$code = base64_encode($code);

				//--Pasamos el codigo por un Link via get--//
				$link = "http://www.educommerce.com.br/admin/login/forgot/reset?code=$code";


				//if ($inadmin === true) {
				//	$link = "http://www.educommerce.com.br/admin/forgot/reset?code=$code";
				//} else {
				//	$link = "http://www.educommerce.com.br/forgot/reset?code=$code";
				//}		

				//--Tenemos q envíar el Link por Email--//
				$asunto = "Re-definir Senha de Eduweb Store";
				$mailer = new Mailer($data["desemail"], 
									 $data["desperson"],
									 $asunto,
									 "forgot",
					array(
						"name"=>$data["desperson"],
						"link"=>$link
					));

				//--Enviamos el Mail--//
				$mailer->sent();
				return $data;
			}	

		}	
	}


	public static function validForgotDecrypt($code)
	{
		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', 
									  pack("a16", User::SECRET), 
									  0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
								INNER JOIN tb_users b USING(iduser)
								INNER JOIN tb_persons c USING(idperson)
								WHERE a.idrecovery = :idrecovery
								AND a.dtrecovery IS NULL
								AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", 
								array(":idrecovery"=>$idrecovery
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			return $results[0];
		}

	}

	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() 
					WHERE idrecovery = :idrecovery", array(
					":idrecovery"=>$idrecovery
		));

	}

	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password 
			        WHERE iduser = :iduser", array(
					":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}
	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

}
?>