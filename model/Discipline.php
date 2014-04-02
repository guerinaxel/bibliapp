<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Discipline extends Eloquent{

	public $table = 'Discipline';
	public $primaryKey ='idDiscipline';
	public $timestamps=false;
}
?>