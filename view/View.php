<?php
namespace view ;
abstract class View {
	protected $layout =null ;
	protected $obj ;
	protected $arrayVar = array();

	public function __construct($o=null) { 
		$monUrl = $_SERVER['SCRIPT_NAME']; 
		$monUrl = substr_replace($monUrl, '', -9);
		$this->obj = $o ;
		$this->arrayVar['url_css'] = $monUrl.'templates/css/bootstrap.css';
		$livres = \model\Livre::all();
		$tabTitre = array();
		foreach($livres as $livre) {
			$tabTitre[] = $livre->titre;
		}
		$this->arrayVar['titres'] = $tabTitre;
		$this->arrayVar['url_base'] = $monUrl;
		$disciplines = \model\Discipline::all();
		$tabDiscipline = array();
		foreach($disciplines as $discipline) {
			$tabDiscipline[] = $discipline;
		}
		$this->arrayVar['disciplines'] = $tabDiscipline;
		
	}

	public function addVar($var, $val) { 
		$this->arrayVar[$var]=$val;
	}

	public function render()
	{
		// '\' devant 'twig_Loader_Filesystem('templates')' pour ne pas rechercher dans le namespace view
		$loader = new \Twig_Loader_Filesystem('templates');
		$twig = new \Twig_Environment( $loader );
		$tmpl = $twig->loadTemplate($this->layout);
		return $tmpl->render($this->arrayVar);
	}

	//public function display() { echo \controller\Csrf::csrfguard_replace_forms($this->render()) ; }
	public function display() {echo \controller\Csrf::csrfguard_replace_forms($this->render()) ; }
}

?>