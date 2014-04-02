<?php
namespace model;
use utils\HttpRequest;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Emprunt extends Eloquent{

	public $table = 'Emprunt';
	public $primaryKey ='idEmprunt';
	public $timestamps=false;

		public function livre()
     {
          return $this->belongsTo('\model\Livre', 'idLivre');
     }

	public function users()
     {
          return $this->belongsTo('\model\Users', 'idUser');
     }
}
?>