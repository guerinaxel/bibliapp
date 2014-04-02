<?php
namespace view ;
class ImportationView extends View {

	public function __construct($url, $user) {

		parent::__construct();
		$this->layout = 'importation.html.twig';
		$this->arrayVar['user'] = $user ;
	}
}


?>
