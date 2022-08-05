<?php

/**
 * Plugin Collections (ou albums)
 * (c) 2012-2013 kent1 (http://www.kent1.info - kent1@arscenic.info)
 * Licence GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('geol_albums_fonctions');

// declaration vide pour ce pipeline.
function geol_albums_autoriser() {
}

// -----------------
// Objet collections

// creer
function autoriser_collection_creer_dist($faire, $type, $id, $qui, $opt) {
	return in_array($qui['statut'], ['0minirezo', '1comite','6forum']);
}

// voir les fiches completes
function autoriser_collection_voir_dist($faire, $type, $id, $qui, $opt) {
	return true;
}

/**
 * Autorisation à lier un média à une collection
 *
 * Peuvent le faire :
 * -* les admins de collections
 * -* les auteurs liés à la collection (participants)
 * -* les administrateurs du site
 */
function autoriser_collection_lierobjet_dist($faire, $type, $id, $qui, $opt) {
	return  collection_admin($id, $qui) or collection_auteur($id, $qui) or (($qui['statut'] == '0minirezo') and !$qui['restreint']);
}
// modifier
function autoriser_collection_modifier_dist($faire, $type, $id, $qui, $opt) {
	/**
	 * Si pas numérique, c'est une création
	 * On se réfère à l'autorisation adéquate
	 */
	if (!is_numeric($id)) {
		return autoriser('creer', $type, $id, $qui, $opt);
	}

	/**
	 * Sinon ce sont les admins de la collection et les administrateurs non restreints
	 */
	return collection_admin($id, $qui) or (($qui['statut'] == '0minirezo') and !$qui['restreint']);
}

// supprimer
function autoriser_collection_supprimer_dist($faire, $type, $id, $qui, $opt) {
	return collection_admin($id, $qui) or (($qui['statut'] == '0minirezo') and !$qui['restreint']);
}


// associer (lier / delier)
function autoriser_associercollections_dist($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo' and !$qui['restreint'];
}

/**
 * Autorisation d'association d'auteurs à une collection
 * La collection doit être coopérative
 */
function autoriser_collection_associerauteurs_dist($faire, $type, $id, $qui, $opt) {
	$type = sql_getfetsel('type_collection', 'spip_collections', 'id_collection=' . intval($id));
	if ($type != 'coop') {
		return false;
	}

	return in_array($qui['statut'], ['0minirezo','1comite','6forum']);
}
