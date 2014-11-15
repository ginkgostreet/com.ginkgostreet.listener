<?php

/**
 * Defines the conditions for raising an event and messages the registry to
 * invoke listeners. Optionally, delay raising of an event by overriding
 * $this->queueConditionsAreMet().
 */
abstract class CRM_Listener_Event {

  /**
   * Returned when an event was queued.
   */
  const EVENT_WAS_QUEUED = 1;

  /**
   * Returned when an event was raised.
   */
  const EVENT_WAS_RAISED = 2;

  /**
   * The key for the civicmr_queue_item table
   */
  const QUEUE_NAME = 'deferred_events';

  /**
   * Messages the registry to invoke all listeners for this event, or queues the
   * event to be raised later if queue conditions are met.
   *
   * @return int Returns a constant to indicate whether the event was fired or
   *             queued: self::EVENT_WAS_QUEUED || self::EVENT_WAS_RAISED
   */
  public function raise() {
    if ($this->queueConditionsAreMet()) {
      $this->queueRaise();
      return self::EVENT_WAS_QUEUED;
    } else {
      CRM_Listener_Registry::invokeListeners($this);
      return self::EVENT_WAS_RAISED;
    }
  }

  /**
   * Raises the condition only if raise conditions are met.
   *
   * @return mixed Returns boolean FALSE if the condition was met, else returns
   *               the result of $this->raise()
   */
  public function raiseConditionally() {
    if ($this->raiseConditionsAreMet()) {
      return $this->raise();
    }
    return FALSE;
  }

  /**
   * Events should contain the logic for the conditions under which they will be
   * raised. Override this method and define the conditions for your event therein.
   *
   * This is called by $this->raiseConditionally().
   *
   * @return boolean
   */
  protected function raiseConditionsAreMet() {
    return TRUE;
  }

  /**
   * Override this to set up a condition to queue the event instead of raising it
   * immediately.
   *
   * @return boolean
   */
  protected function queueConditionsAreMet() {
    return FALSE;
  }

  /**
   * Queue event to be automatically raised by the event queue manager at a later
   * time
   *
   * @param int $delay_seconds Event will not be raised until at least this much time passes
   * @return int self::EVENT_WAS_QUEUED
   */
  public function queueRaise($delay_seconds = 0) {
    $queue_item = new CRM_Queue_DAO_QueueItem();
    $queue_item->queue_name  = self::QUEUE_NAME;
    $queue_item->submit_time = CRM_Utils_Time::getTime('YmdHis');
    $queue_item->data        = serialize($this);
    $queue_item->weight      = 0;

    $now = CRM_Utils_Time::getTimeRaw();
    $queue_item->release_time = date('YmdHis', $now + $delay_seconds);

    $queue_item->save();

    return self::EVENT_WAS_QUEUED;
  }
}