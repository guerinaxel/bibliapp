<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Users extends Eloquent{

	public $table = 'Users';
	public $primaryKey ='idUser';
	public $timestamps=false;
}
?>