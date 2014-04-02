<?php
namespace view ;
class EmpruntView extends View {

	public function __construct($url, $emprunts, $user) {

		parent::__construct();
		$this->layout = 'emprunt.html.twig';
		$this->arrayVar['emprunts'] = $emprunts ;
		$this->arrayVar['user'] = $user ;
	}
}


?>
