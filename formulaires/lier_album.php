<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_lier_album_charger($id_article) {
	$valeurs = [
		'id_article' => $id_article,
		'identifiant' => 'lier_album_' . $id_article,
		'editable' => true
	];
	return $valeurs;
}

function formulaires_lier_album_verifier($id_article) {
	$erreurs = [];
	// si pas d'id, le selecteur generique n'a pas fonctionne
	// on fait comment alors ??
	if (!_request('id_collection')) {
		$erreurs['message_erreur'] = _T('collection:erreur_association_collection');
	}

	return $erreurs;
}

function formulaires_lier_album_traiter($id_article) {
	$res = [];
	$message = [];
	$id_collection = _request('id_collection');
	$association = false;

	include_spip('inc/autoriser');
	include_spip('action/editer_liens');

	if (autoriser('lierobjet', 'collection', $id_collection)) {
		$rang = sql_countsel('spip_collections_liens', 'id_collection=' . intval($id_collection));
		$association = objet_associer(['collection' => $id_collection], ['article' => $id_article], ['id_auteur' => $GLOBALS['visiteur_session']['id_auteur'] ?: 0,'rang' => $rang + 1]);
	}

	if (!$association) {
		return $res['message_erreur'] = _T('collection:erreur_association_collection');
	}else {
		$organiser = charger_fonction('collection_organiser_rangs', 'inc');
		$organiser($id_collection);
		include_spip('inc/invalideur');
		suivre_invalideur('1');
	}
	$message['editable'] = true;
	$message['message_ok'] = '<script type="text/javascript">if (window.jQuery) ajaxReload("albums");</script>';

	return $message;
}
