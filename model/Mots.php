<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Mots extends Eloquent{

	public $table = 'Mots';
	public $primaryKey ='idMot';
	public $timestamps=false;
}
?>