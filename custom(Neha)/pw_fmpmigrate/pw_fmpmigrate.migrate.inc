<?php
/**
 * @file
 * Declares our migrations.
 */
    

/**
 * Implements hook_migrate_api().
 */
function pw_fmpmigrate_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      'pw' => array(
        'title' => t('PW Migrations'),
      ),
    ),
    'migrations' => array(
      'FMPUser' => array(
        'class_name' => 'MigrateFMPUserMigration',
        'group_name' => 'pw',
      ),
    ),
  );
  return $api;
}
?>