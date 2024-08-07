<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Fonction d'installation et de mise à jour du plugin.
 *
 * Effectue une migration des albums basés sur les grappes vers les tables du plugin media_collections
 *
 * @param string $nom_meta_base_version
 * 		Le nom de la meta d'installation
 * @param float $version_cible
 * 		Le numéro de version vers laquelle mettre à jour
 */
function geol_albums_upgrade($nom_meta_base_version, $version_cible) {
	$maj = [];
	$maj['create'] = [
		['maj_tables', ['spip_collections', 'spip_collections_liens']],
		['geol_albums_init'],
		['ecrire_config', 'select2/active', 'oui']
	];
	$maj['1.0.1'] = [
		['ecrire_config', 'select2/active', 'oui']
	];
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Migration des albums grappes vers media_collections
 *
 * Appelée lors de l'installation du plugin
 */
function geol_albums_init() {
	if ($grappes = sql_allfetsel('*', 'spip_grappes', sql_in('type', ['album_perso', 'album_coop', 'balade']))) {
		include_spip('action/editer_objet');
		include_spip('action/editer_liens');

		foreach ($grappes as $grappe) {
			// récupérer les infos des anciens albums (grappes)
			$set = [
				'id_admin' => $grappe['id_admin'],
				'titre' => $grappe['titre'],
				'descriptif' => $grappe['descriptif'],
				'date' => $grappe['date']
			];

			if ($grappe['type'] == 'album_perso') {
				$set['type_collection'] = 'perso';
			} elseif ($grappe['type'] == 'album_coop') {
				$set['type_collection'] = 'coop';
			} elseif ($grappe['type'] == 'balade') {
				$set['type_collection'] = 'balade';
			}

			// créer des collections
			$id_collection = objet_inserer('collection');
			if ($id_collection > 0) {
				objet_modifier('collection', $id_collection, $set);
				objet_instituer('collection', $id_collection, ['statut' => 'publie']);

				// copie des liens de grappes_liens vers collections_liens pour les articles
				$articles = sql_allfetsel('*', 'spip_grappes_liens', "objet = 'article' AND id_grappe = " . $grappe['id_grappe']);
				foreach ($articles as $article) {
					objet_associer(['collection' => $id_collection], [$article['objet'] => $article['id_objet']], ['rang' => $article['rang']]);
				}

				// associer l'auteur id_admin de la grappe à la collection
				objet_associer(['auteur' => $grappe['id_admin']], ['collection' => $id_collection]);

				// copie des liens de grappes_liens vers auteurs_liens pour les auteurs
				$auteurs = sql_allfetsel('*', 'spip_grappes_liens', "objet = 'auteur' AND id_grappe = " . $grappe['id_grappe']);
				foreach ($auteurs as $auteur) {
					objet_associer([$auteur['objet'] => $auteur['id_objet']], ['collection' => $id_collection]);
				}

				// maj des liens des forums attachés aux grappes
				$forums = sql_allfetsel('id_forum', 'spip_forum', "objet = 'grappe' AND id_objet = " . $grappe['id_grappe']);
				foreach ($forums as $forum) {
					sql_updateq('spip_forum', ['objet' => 'collection', 'id_objet' => $id_collection], 'id_forum = ' . $forum['id_forum']);
				}

				// maj des liens des points gis attachés aux grappes
				$points = sql_allfetsel('id_gis', 'spip_gis_liens', "objet = 'grappe' AND id_objet = " . $grappe['id_grappe']);
				foreach ($points as $point) {
					sql_updateq('spip_gis_liens', ['objet' => 'collection', 'id_objet' => $id_collection], 'id_gis = ' . $point['id_gis']);
				}
			}
		}
	}
}

/**
 * Fonction de désinstallation du plugin.
 *
 * Supprime les tables spip_collections et spip_collections_liens
 * Supprime également :
 * - les révisions potentielles de collections (spip_versions et spip_versions_fragments)
 * - les forums attachés aux collections
 * - les liens des auteurs liés aux collections
 * - la meta d'installation du plugin
 *
 * @param string $nom_meta_base_version
 * 		Le nom de la meta d'installation
 */
function geol_albums_vider_tables($nom_meta_base_version) {
	sql_drop_table('spip_collections');
	sql_drop_table('spip_collections_liens');

	# Nettoyer les versionnages et forums
	sql_delete('spip_versions', sql_in('objet', ['collection']));
	sql_delete('spip_versions_fragments', sql_in('objet', ['collection']));
	sql_delete('spip_forum', sql_in('objet', ['collection']));
	sql_delete('spip_auteurs_liens', sql_in('objet', ['collection']));

	effacer_meta($nom_meta_base_version);
}
