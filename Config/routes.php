<?php
/**
 * Routing for Opauth
 */
Router::connect('/auth/callback', array('plugin' => 'Social', 'controller' => 'UserConnections', 'action' => 'callback'));
Router::connect('/auth/*', array('plugin' => 'Social', 'controller' => 'UserConnections', 'action' => 'auth'));
