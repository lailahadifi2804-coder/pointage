<?php

defined('BASEPATH') or exit('No direct script access allowed');

$lang['hikvision_connector']            = 'Connecteur Hikvision';
$lang['hikvision_connector_management'] = 'Connecteur Hikvision - Gestion';
$lang['hikvision_settings']             = 'Paramètres Hikvision';
$lang['hikvision_mapping']              = 'Mapping employés';
$lang['hikvision_unmapped_events']      = 'Événements non mappés';
$lang['hikvision_logs']                 = 'Journal des événements';

$lang['hikvision_enabled']              = 'Activer la synchronisation Hikvision';
$lang['hikvision_db_connection']        = 'Connexion à la base tierce Hikvision';
$lang['hikvision_db_host']              = 'Hôte';
$lang['hikvision_db_port']              = 'Port';
$lang['hikvision_db_name']              = 'Base de données';
$lang['hikvision_db_user']              = 'Utilisateur';
$lang['hikvision_db_password']          = 'Mot de passe';
$lang['hikvision_password_hint']        = 'Laisser vide (ou "********") pour conserver le mot de passe déjà enregistré.';
$lang['hikvision_source_table']         = 'Table / vue source';
$lang['hikvision_test_connection']      = 'Tester la connexion';
$lang['hikvision_connection_ok']        = 'Connexion réussie (%d lignes trouvées dans la table).';
$lang['hikvision_connection_failed']    = 'Impossible de se connecter à la base de données tierce. Vérifiez les identifiants.';
$lang['hikvision_table_not_found']      = 'Connexion réussie mais la table/vue configurée est introuvable.';

$lang['hikvision_column_mapping']       = 'Correspondance des colonnes';
$lang['hikvision_column_id']            = 'Colonne ID';
$lang['hikvision_column_datetime']      = 'Colonne Authentication Date and Time';
$lang['hikvision_column_direction']     = 'Colonne Direction';
$lang['hikvision_column_device_name']   = 'Colonne Device Name';
$lang['hikvision_column_device_serial'] = 'Colonne Device Serial No.';
$lang['hikvision_column_person_name']   = 'Colonne Person Name';
$lang['hikvision_column_card_no']       = 'Colonne Card No.';

$lang['hikvision_sync_settings']            = 'Paramètres de synchronisation';
$lang['hikvision_timezone_source']          = 'Fuseau horaire de la source';
$lang['hikvision_poll_interval_minutes']    = 'Fréquence de synchronisation (minutes)';
$lang['hikvision_batch_size']               = 'Taille de lot';
$lang['hikvision_max_retry']                = 'Nombre max. de tentatives';

$lang['hikvision_add_mapping']              = 'Ajouter un mapping';
$lang['hikvision_card_no']                  = 'Card No.';
$lang['hikvision_staff']                    = 'Employé Perfex';
$lang['hikvision_person_name_reference']    = 'Nom Hikvision (référence)';
$lang['hikvision_device_serial_no']         = 'N° de série du contrôleur (optionnel)';
$lang['hikvision_create_mapping']           = 'Créer le mapping';

$lang['hikvision_unmapped_events_help']     = 'Ces cartes ont été vues dans les événements Hikvision mais ne sont associées à aucun employé Perfex. Aucun pointage n\'a été créé pour elles tant qu\'aucun mapping n\'existe.';
$lang['hikvision_no_unmapped_events']       = 'Aucun événement non mappé.';
$lang['hikvision_occurrences']              = 'Occurrences';
$lang['hikvision_last_seen']                = 'Dernière occurrence';

$lang['hikvision_all_statuses']             = 'Tous les statuts';
$lang['hikvision_auth_datetime']            = 'Date et heure';
$lang['hikvision_direction']                = 'Direction';
$lang['hikvision_status']                   = 'Statut';
$lang['hikvision_error']                    = 'Erreur';
$lang['hikvision_retry']                    = 'Relancer';
$lang['hikvision_status_pending']           = 'En attente';
$lang['hikvision_status_imported']          = 'Importé';
$lang['hikvision_status_unmapped']          = 'Non mappé';
$lang['hikvision_status_error']             = 'Erreur';
$lang['hikvision_status_ignored']           = 'Ignoré';
