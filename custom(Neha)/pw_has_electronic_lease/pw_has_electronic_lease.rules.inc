<?php 
/**
 * Implements hook_rules_condition_info().
 */
function pw_has_electronic_lease_rules_condition_info() {
  return array(
    'pw_has_electronic_lease' => array(
      'group' => t('pw commerce'),
      'label' => t('Has electronic lease'),
      'parameter' => array(
        'user' => array(
          'type' => 'user',
          'label' => t('Validate registered users for subscriber role'),
        )
      ),
    ),
  );
}

/**
 * Callback function for custom condition info
 */
function pw_has_electronic_lease($user) {
  // Subscriber role id is 3
  if (array_key_exists(3, $user->roles)) {
    return TRUE;
  }
  else {
    return FALSE;
  }
}