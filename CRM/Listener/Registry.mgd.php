<?php
// This file declares a managed database record of type "OptionGroup".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
return array (
  0 =>
  array (
    'name' => 'listener_registry',
    'entity' => 'OptionGroup',
    'params' =>
    array (
      'is_reserved' => 1,
      'label' => ts('Listener Registry', array('domain' => 'com.ginkgostreet.listener')),
      'name' => 'listener_registry',
      'version' => 3,
    ),
  ),
);