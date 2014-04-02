<?php
namespace view ;
class ChoixAdminView extends View {

	public function __construct($url, $user) {

		parent::__construct();
		$this->layout = 'choixAdmin.html.twig';
		$this->arrayVar['user'] = $user ;
		
	}
}


?>
