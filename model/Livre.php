<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Livre extends Eloquent{

	public $table = 'Livre';
	public $primaryKey ='idLivre';
	public $timestamps=false;	

	public function discipline()
	{
	  return $this->belongsTo('\model\Discipline', 'idDiscipline');
	}

	public function edition()
	{
	  return $this->belongsTo('\model\Edition', 'idEdition');
	}
}
?>