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

/**
 * Insertion dans le pipeline affiche_milieu (SPIP)
 *
 * Ajout de contenu sur certaines pages de l'espace privé
 * Ajoute le formulaire d'ajout d'auteurs sur la page des collections
 *
 * @param array $flux
 * 		Le contexte du pipeline
 * @return array $flux
 * 		Le contexte du pipeline complété
 */
function geol_albums_affiche_milieu($flux) {
	$texte = false;
	$e = trouver_objet_exec($flux['args']['exec']);

	// auteurs sur les collections
	if (!$e['edition'] AND in_array($e['type'], array('collection'))) {
		$texte = recuperer_fond('prive/objets/editer/liens', array(
			'table_source' => 'auteurs',
			'objet' => $e['type'],
			'id_objet' => $flux['args'][$e['id_table_objet']]
		));
	}

	if ($texte) {
		if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
			$flux['data'] = substr_replace($flux['data'],$texte,$p,0);
		else
			$flux['data'] .= $texte;
	}

	return $flux;
}

/**
 * Insertion dans le pipeline optimiser_base_disparus (SPIP)
 * 
 * Optimiser la base de données : collections à la poubelle, liens orphelins sur les collections
 *
 * @param int $n
 * @return int
 */
function collections_optimiser_base_disparus($flux){
	// collections à la poubelle
	sql_delete("spip_collections", "statut='poubelle' AND maj < ".sql_quote(trim($flux['args']['date'],"'")));
	// optimiser les liens des collections vers des objets effacés et depuis des collections effacées
	include_spip('action/editer_liens');
	$flux['data'] += objet_optimiser_liens(array('collection'=>'*'),'*');
	return $flux;
}

/**
 * Insertion dans le pipeline collections_liste_types (plugin collections)
 * 
 * Ajoute les deux types de collections possibles :
 * - coopérative
 * - personnelle
 *
 * @param array $flux
 * 		La liste des types de collections disponibles
 * @return array $flux
 * 		La liste des types de collections complétée
 */
function geol_albums_collections_liste_types($flux){
	if(!is_array($flux))
		$flux = array();
	
	$flux['perso'] = _T('collection:type_perso');
	$flux['coop'] = _T('collection:type_coop');
	
	return $flux;
}


/**
 * Insertion dans le pipeline collections_liste_genres (plugin collections)
 *
 * Ajoute les quatre genres de collections possibles :
 * - mixed
 * - image
 * - audio
 * - video
 *
 * @param array $flux
 * 		La liste des genres possibles
 * @return array
 * 		La liste des genres complétés
 */
function geol_albums_collections_liste_genres($flux){
	if(!is_array($flux))
		$flux = array();
	
	$flux['mixed'] = _T('collection:genre_mixed');
	$flux['image'] = _T('collection:genre_photo');
	$flux['audio'] = _T('collection:genre_musique');
	$flux['video'] = _T('collection:genre_video');
	
	return $flux;
}