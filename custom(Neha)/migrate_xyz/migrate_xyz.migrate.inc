<?php

/**
 * @file
 * Declares our migrations.
 */
    

/**
 * Implements hook_migrate_api().
 */
function migrate_xyz_migrate_api() {
  
  $api = array(
    'api' => 2,
    'groups' => array(
      'example' => array(
        'title' => t('Example'),
        'default_format' => 'filtered_html',
      ),
    ),
    'migrations' => array(
       'ExamplePage' => array('class_name' => 'ExamplePageMigration','group_name' => 'example'),
       //'ExampleMail' => array('class_name' => 'ExampleMailMigration','group_name' => 'example'),
    ),
    
    /*'field handlers' => array(
      'ExampleMailFieldHandler',
    )*/
   
  );
  
  
  return $api;
  
  
}
