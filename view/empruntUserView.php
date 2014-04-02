<?php
namespace view ;
class empruntUserView extends View {

	public function __construct($url, $emprunts, $iduser,$livre,$discipline, $user) {

		parent::__construct();
		$this->layout = 'empruntUser.html.twig';
		$this->arrayVar['emprunts'] = $emprunts ;
		$this->arrayVar['livre'] = $livre ;
		$this->arrayVar['discipline'] = $discipline ;
		$this->arrayVar['iduser'] = $iduser ;
		$this->arrayVar['user'] = $user ;
	}
}


?>
