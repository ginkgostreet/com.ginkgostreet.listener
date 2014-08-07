<?php
/**
 *
 */
class CRM_Listener_Registry {

  private static $option_group_id;

  public static function getOptionGroupID() {
    if (is_null(self::$option_group_id)) {
      self::$option_group_id = civicrm_api3('OptionGroup', 'getvalue', array(
        'name' => 'listener_registry',
        'return' => 'id',
      ));
    }

    return self::$option_group_id;
  }

  /**
   *
   * @param string $eventClass Class name of event
   * @param string $listenerClass Class name of listener
   * @param string $extension Name of extension that registered the listener, e.g. org.civicrm.volunteer
   * @param int $weight
   * @throws CRM_Exception
   */
  public static function addListener($eventClass, $listenerClass, $extension, $weight = NULL) {
    if (!class_exists($eventClass)) {
      throw new CRM_Exception("Can't register listener to nonexistent event");
    }

    if (!class_exists($listenerClass)) {
      throw new CRM_Exception("Can't register nonexistent listener");
    }

    if (!in_array('CRM_Listener', class_parents($listenerClass))) {
      throw new CRM_Exception("Class $listenerClass does not extend CRM_Listener");
    }

    if (!in_array('CRM_Listener_Event', class_parents($eventClass))) {
      throw new CRM_Exception("Class $eventClass does not extend CRM_Listener_Event");
    }

    if (is_null($weight)) {
      $weight = self::getNextWeight();
    }

    civicrm_api3('OptionValue', 'create', array(
      'grouping' => $extension,
      'name' => $eventClass,
      'option_group_id' => self::getOptionGroupID(),
      'value' => $listenerClass,
      'weight' => $weight,
    ));
  }

  public static function removeListener($listenerClass) {
    civicrm_api3('OptionValue', 'delete', array(
      'option_group_id' => self::getOptionGroupID(),
      'value' => $listenerClass,
    ));
  }

  public static function removeListeners($eventClass) {
    civicrm_api3('OptionValue', 'delete', array(
      'option_group_id' => self::getOptionGroupID(),
      'name' => $eventClass,
    ));
  }

  /**
   *
   * @param CRM_Listener_Event $event
   */
  public static function invokeListeners(CRM_Listener_Event $event) {
    $eventClass = get_class($event);
    $listeners = self::getListeners($eventClass);

    foreach ($listeners as $listenerClass) {
      $listener = new $listenerClass();
      try {
        $listener->handle($event);
      } catch(Exception $e) {
        CRM_Core_Error::debug_log_message($e->message);
        continue;
      }
    }
  }

  /**
   *
   * @param type $eventClass
   * @return type
   */
  public static function getListeners($eventClass) {
    $get = civicrm_api3('OptionValue', 'get', array(
      'name' => $eventClass,
      'option_group_id' => self::$option_group_id,
    ));

    $listeners = array();
    foreach ($get['values'] as $optionValue) {
      $listeners[] = $optionValue['value'];
    }

    return $listeners;
  }

  private static function getNextWeight($eventClass) {
    $optionValues = civicrm_api3('OptionValues', 'get', array(
      'name' => $eventClass,
      'option_group_id' => self::getOptionGroupID(),
    ));

    $weights = array(0);
    foreach ($optionValues['values'] as $o) {
      $weights[] = $o['weight'];
    }

    return max($weights) + 1;
  }
}