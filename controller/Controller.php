<?php
namespace controller ;
use utils\HttpRequest;
/**
 *  La classe Controller
 *
 *  Classe abstraite définissant un controleur
 *  implante la méthode callAction
 *
 *  @package controller
 */
abstract class Controller
{
	
	public function __construct()
	{

	}

  /**
   *  callAction : appel d'une action dans le controleur
   *  le choix est realise a partir de l'entree 'a' dans le tableau de données
   *
   *
   *   @access public
   *   @param Array $data : tableau de données
   *   @return void
   */

	public function callAction($action, array $request)
	{
            

		if( ($action!=false) &&
                    (!is_null($action) && array_key_exists($action,static::$_listActions))
                  ) {
			$method = static::$_listActions[$action];
			$this->$method($request);
		}
		else	
		{ 
			$this->defaultAction($request);
		}
	}

	abstract protected function defaultAction(array $request);
}
