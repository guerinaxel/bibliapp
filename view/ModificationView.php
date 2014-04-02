<?php
namespace view ;
class ModificationView extends View {

	public function __construct($url, $livre, $user) {

		parent::__construct();
		$this->layout = 'modification.html.twig';
		$this->arrayVar['livre'] = $livre ;
		$this->arrayVar['user'] = $user ;
	}
}


?>
