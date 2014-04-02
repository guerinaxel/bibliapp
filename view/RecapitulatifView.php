<?php
namespace view ;
class RecapitulatifView extends View {

	public function __construct($url, $disciplines, $message, $user) {

		parent::__construct();
		$this->layout = 'recapitulatif.html.twig';
		$this->arrayVar['disciplines'] = $disciplines ;
		$this->arrayVar['message'] = $message ;
		$this->arrayVar['user'] = $user ;
	}
}


?>
