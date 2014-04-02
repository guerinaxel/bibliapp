<?php
namespace view ;
class InscriptionView extends View {

	public function __construct($url, $login, $user) {

		parent::__construct();
		$this->layout = 'inscription.html.twig';
		$this->arrayVar['login'] = $login;
		$this->arrayVar['user'] = $user ;
	}
}


?>
