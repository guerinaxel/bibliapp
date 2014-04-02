<?php
namespace view ;
class StatistiqueView extends View {

	public function __construct($url, $livres) {

		parent::__construct();
		$this->layout = 'statistique.html.twig';
		$this->arrayVar['livres'] = $livres ;
	}
}


?>
