<?php

require_once 'listener.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function listener_civicrm_config(&$config) {
  _listener_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function listener_civicrm_xmlMenu(&$files) {
  _listener_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function listener_civicrm_install() {
  return _listener_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function listener_civicrm_uninstall() {
  return _listener_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function listener_civicrm_enable() {
  return _listener_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function listener_civicrm_disable() {
  return _listener_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function listener_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _listener_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function listener_civicrm_managed(&$entities) {
  return _listener_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function listener_civicrm_caseTypes(&$caseTypes) {
  _listener_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function listener_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _listener_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function listener_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  $params = array('name' => CRM_Listener_Event::QUEUE_NAME);
  $queueManager = new CRM_Queue_Queue_Sql($params);

  $throttle = civicrm_api3('Setting', 'getvalue', array(
    'group' => 'com.ginkgostreet.listener',
    'name' => 'queue_throttle',
  ));
  $max = ($throttle > $queueManager->numberOfItems()) ? $queueManager->numberOfItems() : $throttle;

  for ($i = $max; $i > 0; $i--) {
    $timeout = 3600;
    $queueItem = $queueManager->claimItem($timeout);

    if ($queueItem !== FALSE) {
      $event = $queueItem->data;
      CRM_Listener_Registry::invokeListeners($event);

      $queueManager->deleteItem($queueItem);
    }
  }
}