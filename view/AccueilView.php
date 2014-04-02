<?php
namespace view ;
class AccueilView extends View {

	public function __construct($url, $livre, $indisponible, $idUser, $user) {

		parent::__construct();
		$this->layout = 'accueil.html.twig';
		$this->arrayVar['livre'] = $livre ;
		$this->arrayVar['dispo'] = $indisponible ;
		$this->arrayVar['idUser'] = $idUser ;
		$this->arrayVar['user'] = $user ;
	}
}


?>
