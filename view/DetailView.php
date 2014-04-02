<?php
namespace view ;
class DetailView extends View {

	public function __construct($url, $livre, $user, $indisponible, $mots) {

		parent::__construct();
		$this->layout = 'detail.html.twig';
		$this->arrayVar['livre'] = $livre ;
		$this->arrayVar['user'] = $user ;
		$this->arrayVar['dispo'] = $indisponible ;
		$this->arrayVar['mots'] = $mots ;
		//$this->arrayVar['dispo'] = $disponible;
	}
}


?>
