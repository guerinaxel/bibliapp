<?php
namespace view ;
class RechAvView extends View {

	public function __construct($url, $livre, $indisponible, $idUser, $user, $disciplines) {

		parent::__construct();
		$this->layout = 'rechAv.html.twig';
		$this->arrayVar['livre'] = $livre ;
		$this->arrayVar['dispo'] = $indisponible ;
		$this->arrayVar['idUser'] = $idUser ;
		$this->arrayVar['user'] = $user ;
		$this->arrayVar['tabOptions'] = $disciplines ;
	}
}


?>
