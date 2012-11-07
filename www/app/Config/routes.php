<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

// Index location
Router::connect('/', array('controller' => 'display', 'action' => 'index'));

// Public routes
Router::connect('/embed.js', array('controller' => 'display', 'action' => 'embed_script'));
Router::connect('/group/*', array('controller' => 'display', 'action' => 'group_display'));
Router::connect('/satellite/*', array('controller' => 'display', 'action' => 'satellite_display'));

// Administrator panel routes
Router::connect('/admin', array('controller' => 'panel', 'action' => 'index', 'admin' => true));
Router::connect('/admin/configuration', array('[method]' => 'POST', 'controller' => 'panel', 'action' => 'update_configuration', 'admin' => true));
Router::connect('/admin/satellite/add', array('[method]' => 'GET', 'controller' => 'satellite', 'action' => 'add', 'admin' => true));
Router::connect('/admin/satellite/add', array('[method]' => 'POST', 'controller' => 'satellite', 'action' => 'create', 'admin' => true));
Router::connect('/admin/satellite/:id/delete', array('[method]' => 'GET', 'controller' => 'satellite', 'action' => 'remove', 'admin' => true));
Router::connect('/admin/satellite/:id/delete', array('[method]' => 'POST', 'controller' => 'satellite', 'action' => 'delete', 'admin' => true));
Router::connect('/admin/satellite/:id/edit', array('[method]' => 'GET', 'controller' => 'satellite', 'action' => 'edit', 'admin' => true));
Router::connect('/admin/satellite/:id/edit', array('[method]' => 'POST', 'controller' => 'satellite', 'action' => 'change', 'admin' => true));
Router::connect('/admin/group/add', array('[method]' => 'GET', 'controller' => 'group', 'action' => 'add', 'admin' => true));
Router::connect('/admin/group/add', array('[method]' => 'POST', 'controller' => 'group', 'action' => 'create', 'admin' => true));
Router::connect('/admin/group/:id/delete', array('[method]' => 'GET', 'controller' => 'group', 'action' => 'remove', 'admin' => true));
Router::connect('/admin/group/:id/delete', array('[method]' => 'POST', 'controller' => 'group', 'action' => 'delete', 'admin' => true));
Router::connect('/admin/group/:id/edit', array('[method]' => 'GET', 'controller' => 'group', 'action' => 'edit', 'admin' => true));
Router::connect('/admin/group/:id/edit', array('[method]' => 'POST', 'controller' => 'group', 'action' => 'change', 'admin' => true));
Router::connect('/admin/station/add', array('[method]' => 'GET', 'controller' => 'station', 'action' => 'add', 'admin' => true));
Router::connect('/admin/station/add', array('[method]' => 'POST', 'controller' => 'station', 'action' => 'create', 'admin' => true));
Router::connect('/admin/station/:id/delete', array('[method]' => 'GET', 'controller' => 'station', 'action' => 'remove', 'admin' => true));
Router::connect('/admin/station/:id/delete', array('[method]' => 'POST', 'controller' => 'station', 'action' => 'delete', 'admin' => true));
Router::connect('/admin/station/:id/edit', array('[method]' => 'GET', 'controller' => 'station', 'action' => 'edit', 'admin' => true));
Router::connect('/admin/station/:id/edit', array('[method]' => 'POST', 'controller' => 'station', 'action' => 'change', 'admin' => true));
Router::connect('/admin/export/generate', array('[method]' => 'POST', 'controller' => 'export', 'action' => 'generate', 'admin' => true));

/**
 * Load all plugin routes.  See the CakePlugin documentation on 
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
