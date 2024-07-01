<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

if (!defined('_SELECTEUR_GENERIQUE_ACTIVER_PUBLIC')) {
	define('_SELECTEUR_GENERIQUE_ACTIVER_PUBLIC', true);
}

// surcharger l'autorisation de selecteur_generique pour notre autocomplete
// puisqu'on bloque l'accès à l'espace privé pour les rédacteurs
function autoriser_geol_albums_autocomplete_dist($faire, $type, $id, $qui, $opt) {
	return isset($qui['id_auteur']) and $qui['id_auteur'] > 0;
}
