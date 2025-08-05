<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Insertion des css du plugin dans les pages publiques
 *
 * @param $flux
 * @return mixed
 */
function geol_albums_insert_head_css($flux) {
	$flux .= "\n" . '<link rel="stylesheet" href="' . find_in_path('css/geol_albums.css') . '" />';
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
	if ($e and !$e['edition'] and in_array($e['type'], ['collection'])) {
		$texte = recuperer_fond('prive/objets/editer/liens', [
			'table_source' => 'auteurs',
			'objet' => $e['type'],
			'id_objet' => $flux['args'][$e['id_table_objet']]
		]);
	}

	if ($texte) {
		if ($p = strpos($flux['data'], '<!--affiche_milieu-->')) {
			$flux['data'] = substr_replace($flux['data'], $texte, $p, 0);
		} else {
			$flux['data'] .= $texte;
		}
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
function collections_optimiser_base_disparus($flux) {
	// collections à la poubelle
	sql_delete('spip_collections', "statut='poubelle' AND maj < " . sql_quote(trim($flux['args']['date'], "'")));
	// optimiser les liens des collections vers des objets effacés et depuis des collections effacées
	include_spip('action/editer_liens');
	$flux['data'] += objet_optimiser_liens(['collection' => '*'], '*');
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
function geol_albums_collections_liste_types($flux) {
	if (!is_array($flux)) {
		$flux = [];
	}

	$flux['perso'] = _T('collection:type_perso');
	$flux['coop'] = _T('collection:type_coop');

	return $flux;
}

/**
 * Enlever les id_ de la table spip_collections du critère selections conditionnelles,
 * ailleurs que sur cette table
 *
 * @param array $flux
 * @return array
 */
function geol_albums_exclure_id_conditionnel($flux) {
	if ($flux['args']['table'] !== 'spip_collections') {
		$flux['data'] = array_merge($flux['data'], ['id_collection']);
	}
	return $flux;
}