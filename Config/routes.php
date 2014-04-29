<?php
/**
 * Routing for Opauth
 */
Router::connect('/connect/callback', array('plugin' => 'Social', 'controller' => 'UserConnections', 'action' => 'callback'));
Router::connect('/connect/*', array('plugin' => 'Social', 'controller' => 'UserConnections', 'action' => 'connect'));
