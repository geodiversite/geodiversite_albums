<?php
 
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Insertion dans le pipelines insert_head (SPIP)
 * Insérer les js du séleceteur générique s'ils ne sont pas déjà là
 *
 * @param string $flux
 * 	Le contenu textuel de la balise #INSERT_HEAD
 * @return string
 * 	Le contenu modifié
 */
function geol_albums_insert_head($flux){
	include_spip('selecteurgenerique_fonctions');
	$flux .= selecteurgenerique_verifier_js($flux);
	return $flux;
}

/**
 * Insertion des css du plugin dans les pages publiques
 *
 * @param $flux
 * @return mixed
 */
function geol_albums_insert_head_css($flux) {
	$flux .= "\n".'<link rel="stylesheet" href="'. find_in_path('css/geol_albums.css') .'" />';
	return $flux;
}