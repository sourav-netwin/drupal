<?php

/**
 * @file
 * Declares our migrations.
 */
    

/**
 * Implements hook_migrate_api().
 */
function migrate_fmp_migrate_api()
{
	$api = array(
		'api' => 2,
		'groups' => array(
			'fmp' => array(
				'title' => t('FMP Migrations'),
				 'disable_rules'     => array('rules_sync_contacten_met_gebruikers'),
			),
			
			'fmp_assoc' => array(
				'title' => t('FMP Assocaition Migrations'),
			    'disable_rules'     => array('rules_sync_contacten_met_gebruikers'),
				
			),
			
			'fmp_activity' => array(
				'title' => t('FMP Activity Migrations'),
			    'disable_rules'     => array('rules_sync_contacten_met_gebruikers'),
				
			),
		),
		'migrations' => array(
			'FmpMembers' => array(
				'class_name' => 'FmpMembersMigration',
				'group_name' => 'fmp',
			),
			
			'FmpAssociation' => array(
				'class_name' => 'FmpAssociationMigration',
				'group_name' => 'fmp_assoc',
			),	
			
			'FmpPhonecall' => array(
				'class_name' => 'FmpPhonecallMigration',
				'group_name' => 'fmp_activity',
			),	
			
			'FmpMailIn' => array(
				'class_name' => 'FmpMailInMigration',
				'group_name' => 'fmp_activity',
			),
			
			'FmpMailOut' => array(
				'class_name' => 'FmpMailOutMigration',
				'group_name' => 'fmp_activity',
			),
		),
	);
	
	return $api;
}
