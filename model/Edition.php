<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Edition extends Eloquent{

	public $table = 'Edition';
	public $primaryKey ='idEdition';
	public $timestamps=false;
}
?>