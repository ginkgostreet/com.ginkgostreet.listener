<?php
/**
 * Abstract class that all listeners must extend
 *
 * @author fgomez
 */
abstract class CRM_Listener {
  abstract function handle(CRM_Listener_Event $event);
}
