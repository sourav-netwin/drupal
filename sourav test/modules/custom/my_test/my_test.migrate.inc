<?php
/**
 * Implements hook_migrate_api().
 */
function my_test_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      'example_test' => array(
        'title' => t('test XYZ Migrations'),
      ),
    ),
    'migrations' => array(
      'ExampleTest' => array(
        'class_name' => 'ExampleTestMigration',
        'group_name' => 'example_test',
      ),
    ),
  );
  return $api;
}