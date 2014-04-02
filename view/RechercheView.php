<?php
namespace view ;
class RechercheView extends View {

	public function __construct($url, $livre, $indisponible, $idUser, $user) {

		parent::__construct();
		$this->layout = 'recherche.html.twig';
		$this->arrayVar['livre'] = $livre ;
		$this->arrayVar['dispo'] = $indisponible ;
		$this->arrayVar['idUser'] = $idUser ;
		$this->arrayVar['user'] = $user ;
	}
}


?>
