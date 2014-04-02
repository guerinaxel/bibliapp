<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class MotsLivre extends Eloquent{

	public $table = 'mots_livre';
	public $primaryKey ='idMotLivre';
	public $timestamps=false;
	
	public function mots()
	{
	  return $this->belongsTo('\model\Mots', 'idMot');
	}
	
	public function livre()
	{
	  return $this->belongsTo('\model\Livre', 'idLivre');
	}
}
?>