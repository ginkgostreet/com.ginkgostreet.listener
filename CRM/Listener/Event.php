<?php

/**
 *
 */
abstract class CRM_Listener_Event {

  /**
   * The key for the civicmr_queue_item table
   */
  const QUEUE_NAME = 'deferred_events';

  /**
   * Messages the registry to invoke all listeners for this event.
   */
  public function raise() {
    CRM_Listener_Registry::invokeListeners($this);
  }

  /**
   * Allow event to be automatically raised by the event queue manager
   *
   * @param int $delay_seconds Event will not be raised until at least this much time passes
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
  }
}