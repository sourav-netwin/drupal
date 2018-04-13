<?php
	//Implements hook_migrate_api()
	function migrate_crm_migrate_api()
	{
		$api = array(
			'api' => 2,
			'groups' => array(
				'basicCrm' => array(
					'title' => t('Basic CRM Migrations'),
					'disable_rules' => array('rules_sync_contacten_met_gebruikers'),
				)
			),
			'migrations' => array(
				'CrmMember' => array(
					'class_name' => 'CrmMemberMigration',
					'group_name' => 'basicCrm',
				)
			),
		);
		return $api;
	}
