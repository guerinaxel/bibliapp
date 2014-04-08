<?php
require 'vendor/autoload.php' ;
require 'model/SQLModel.php';
require'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

$app = new \Slim\Slim();

//Module connexion -----------------------------------------------------------

require_once 'config.php';
require_once $phpcas_path . '/CAS.php';
phpCAS::setDebug();
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::setNoCasServerValidation();
phpCAS::forceAuthentication();

if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}

$user = model\Users::where('nom', '=', phpCAS::getUser())->get();
//Ajout du nombre d'emprunts de l'utilisateur
if (isset($user[0])){
	$nbEmprunt = model\Emprunt::whereRaw('idUser = '.$user[0]->idUser.' and dateRetour is null')->count();
	$user[0]->nbEmprunt = $nbEmprunt;
}
	
if (phpCAS::getUser() != ""){   //Si phpCAS::getUser() est remplie Alors
	/*if (phpCAS::getUser(){strlen(phpCAS::getUser())-1 == "u"){ //Si l'utilisateur est un élève alors (le dernier caractère est "u")
		phpCAS::logout(); //logout
	}*/
		$login = phpCAS::getUser();
}

//Admettons ici que phpCAS::getUser() = "doan2u"
//$login = "toto"; Login de l'utilisateur connecté mis dans une variable pour utilisation dans l'appli
//user = model\Users::where('nom', '=', $login)->get();Temporaire

//IMPORTANT !!!! POUR SE DECONNECTER => Ajouter "?logout" dans l'url

//Fin module connexion -----------------------------------------------------------

//Middleware permettant d'obliger l'utilisateur à s'inscrire, celui-ci se trouve dans toutes les routes
$enregistrement = function ($user) {
    return function () use ($user) {
		//Si l'utilisateur n'est pas dans la base alors il est redirigé vers la page d'inscription
		if (!isset($user[0])){
			$monUrl = $_SERVER['SCRIPT_NAME']; 
			$monUrl = substr_replace($monUrl, '', -9);
			$app = \Slim\Slim::getInstance();
            $app->flash('error', 'Vous devez vous inscrire');
            $app->redirect($monUrl.'inscription');
		}
    };
};
//Fin du middleware à mettre dans toutes les routes (sauf les inscriptions) comme : "$app->get('/',$enregistrement($user), function() use ($login)"

//Middleware pour bloquer l'accès aux routes admin, aux personnes qui ne sont pas administrateur
$droitAdmin = function ($user) {
    return function () use ($user) {
		//Si l'utilisateur n'est pas admin, il est redirigé
		$monUrl = $_SERVER['SCRIPT_NAME']; 
		$monUrl = substr_replace($monUrl, '', -9);
		if ($user[0]['droit'] <> 'A'){
			$app = \Slim\Slim::getInstance();
            $app->flash('error', 'Interdiction');
            $app->redirect($monUrl);
		}
    };
};
//Fin du middleware à mettre dans toutes les routes admin comme : "$app->get('/',$droitAdmin($user), function() use ($login)"
//CSRF
function mw1($app){
		
		if (count($_POST))
		{
			if ( !isset($_POST['CSRFName']) or !isset($_POST['CSRFToken']) )
			{
				trigger_error("No CSRFName found, probable invalid request.",E_USER_ERROR);		
			} 
			$name =$_POST['CSRFName'];
			$token=$_POST['CSRFToken'];
			if (!\controller\Csrf::csrfguard_validate_token($name, $token))
			{ 
				$app->halt();
			}
		}
}
//Fin CSRF

//---------------------------- Accueil -----------------------------------------
$app->get('/',$enregistrement($user), function() use ($login, $user)
{
	$a = new \controller\BibliController();
	$a->callAction('accueil', array($login, $user));
});

//---------------------------- Détail d'un livre -------------------------------

$app->get('/livre/?id=:id',$enregistrement($user), function($id) use ($user)
{
	$a = new \controller\BibliController();
	$a->callAction('detailLivre', array($id, $user));

});

$app->get('/livre/?isbn=:isbn',$enregistrement($user), function($isbn) use ($user)
{
	$a = new \controller\BibliController();
	$a->callAction('detailLivre_isbn', array($isbn, $user));

});


//---------------------------- Recherche de livres -------------------------------

$app->get('/recherche',$enregistrement($user), function() use ($app, $login, $user)
{
	$a = new \controller\BibliController();
	$a->callAction('recherche', array($app->request->get(),$login, $user));

});

//---------------------------- Recherche avancée de livres -------------------------------

$app->get('/rechercheAv',$enregistrement($user), function() use ($app, $login, $user)
{
	$a = new \controller\BibliController();
	$a->callAction('rechercheAv', array($app->request->get(), $login, $user));

});

//---------------------------- Emprunt de livre -------------------------------

$app->post('/emprunt/:id',$enregistrement($user), function($id) use ($login, $app)
{
	$a = new \controller\BibliController();
	$a->callAction('emprunt', array($login, $id, $app));

});

//---------------------------- Rendre livre -------------------------------

$app->post('/retour/:id',$enregistrement($user), function($id) use ($login, $app)
{
	$a = new \controller\BibliController();
	$a->callAction('retour', array($login, $id, $app));

});

//---------------------------- Formulaire inscription -------------------------------

$app->get('/inscription', function() use ($login, $user)
{
	$a = new \controller\BibliController();
	$a->callAction('inscriptionForm', array($login, $user));

});

//---------------------------- Inscription dans la base -------------------------------

$app->post('/inscription','mw1', function() use ($app, $login)
{
	$monUrl = $_SERVER['SCRIPT_NAME']; 
	$monUrl = substr_replace($monUrl, '', -9);
	$a = new \controller\BibliController();
	$a->callAction('inscription', array($app->request->post(), $login));
	$app->redirect($monUrl);
});

//---------------------------- Liste des emprunts pour un utilisateur -------------------------------

$app->get('/user/emprunt',$enregistrement($user), function() use ($login, $user)
{
	$a = new \controller\BibliController();
	$a->callAction('listeEmprunt', array($login, $user));

});

//---------------------------- Changement de l'email de l'utilisateur -------------------------------

$app->post('/user/email',$enregistrement($user),'mw1', function() use ($app, $user)
{
	$monUrl = $_SERVER['SCRIPT_NAME']; 
	$monUrl = substr_replace($monUrl, '', -9);
	$a = new \controller\BibliController();
	$a->callAction('changeEmail', array($app->request->post(), $user[0]));
	$app->redirect($monUrl.'user/emprunt');

});

//*************************** Partie administrateur ********************************

//---------------------------- Accueil administration lui donnant 4 choix -------------------------

$app->get('/admin/',$enregistrement($user),$droitAdmin($user), function() use ($user)
{
	$a = new \controller\AdminController();
	$a->callAction('choixAdmin', array($user));

});

//---------------------------- Ajout d'un livre -------------------------

$app->post('/admin/ajout',$enregistrement($user),$droitAdmin($user),'mw1', function() use ($app)
{
	$a = new \controller\AdminController();
	$a->callAction('ajoutLivre', array($app->request->post()));

});

//---------------------------- Saisie de l'ISBN -------------------------

$app->get('/admin/ISBN',$enregistrement($user),$droitAdmin($user), function()
{
	echo "Saisie de l'ISBN : Cette route n'est pas obligatoire, la saisie peut être gérée par js";

});

//---------------------------- Récapitulatif d'un livre -------------------------

$app->get('/admin/recapitulatif',$enregistrement($user),$droitAdmin($user), function() use ($user)
{
	$message = "";
	$a = new \controller\AdminController();
	$a->callAction('recapitulatif', array($message, $user));

});

//---------------------------- Formulaire modification d'un livre -------------------------

$app->get('/admin/livre/:id',$enregistrement($user),$droitAdmin($user), function($id) use ($user)
{
	$a = new \controller\AdminController();
	$a->callAction('modificationForm', array($user, $id));
});

//---------------------------- Suppression d'un livre -------------------------

$app->get('/admin/livre/suppr/:id',$enregistrement($user),$droitAdmin($user), function($id) use ($user,$app)
{
	$monUrl = $_SERVER['SCRIPT_NAME']; 
	$monUrl = substr_replace($monUrl, '', -9);
	$a = new \controller\AdminController();
	$a->callAction('supressionLivre', array($user, $id));
	$app->redirect($monUrl);
});

//---------------------------- Modification d'un livre -------------------------

$app->post('/admin/modification/:id',$enregistrement($user),$droitAdmin($user),'mw1', function($id) use ($app)
{
	$monUrl = $_SERVER['SCRIPT_NAME']; 
	$monUrl = substr_replace($monUrl, '', -9);
	$a = new \controller\AdminController();
	$a->callAction('modification', array($app->request->post(), $id));
	$app->redirect($monUrl.'livre/id='.$id);

});

//---------------------------- Formulaire importation/exportation --------------------------------------

$app->get('/admin/importation',$enregistrement($user),$droitAdmin($user), function() use ($user)
{
	$a = new \controller\AdminController();
	$a->callAction('formImportation', array($user));

});
//---------------------------- importation --------------------------------------
$app->post('/admin/import',$enregistrement($user),$droitAdmin($user), function() use ($app)
{
	$monUrl = $_SERVER['SCRIPT_NAME']; 
	$monUrl = substr_replace($monUrl, '', -9);
	$a = new \controller\AdminController();
	$a->callAction('import', array($app->request->post()));
	$app->redirect($monUrl.'admin/importation');

});

//---------------------------- exportation --------------------------------------
$app->post('/admin/export',$enregistrement($user),$droitAdmin($user), function() use ($app)
{
	$a = new \controller\AdminController();
	$a->callAction('export', array($app->request->post()));

});
//---------------------------- Suivi des emprunts -------------------------------------

$app->get('/admin/suivi',$enregistrement($user),$droitAdmin($user), function() use ($user)
{
	$a = new \controller\AdminController();
	$a->callAction('suivi', array($user));

});
//---------------------------- relance des emprunts -------------------------------------
$app->post('/admin/relance',$enregistrement($user),$droitAdmin($user), function() use ($app)
{
	$a = new \controller\AdminController();
	$a->callAction('relance', array($app->request->post()));

});

$app->post('/admin/relanceDate',$enregistrement($user),$droitAdmin($user), function() use ($app)
{
	$a = new \controller\AdminController();
	$a->callAction('relanceDate', array($app->request->post()));

});

//---------------------------- Statistiques -------------------------------------

$app->get('/admin/statistiques',$enregistrement($user),$droitAdmin($user), function()
{
	$a = new \controller\AdminController();
	$a->callAction('statistiques', array());

});

$app->run();

?>
