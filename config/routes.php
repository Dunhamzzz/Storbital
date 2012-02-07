<?php
/**
 * Short description for file.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.ctp)...
 */
	Router::connect('/', array('controller' => 'products', 'action' => 'index'));
	
//Custom Routes
	
	// Define /netbooks, /tablets, /nettops
	Router::connect('/netbooks', array('controller' => 'products', 'action' => 'type', 'netbooks'));
	Router::connect('/nettops', array('controller' => 'products', 'action' => 'type', 'nettops'));
	Router::connect('/tablets', array('controller' => 'products', 'action' => 'type', 'tablets'));
	
	Router::connect('/netbooks/all', array('controller' => 'products', 'action' => 'all', 'netbooks'));
	Router::connect('/nettops/all', array('controller' => 'products', 'action' => 'all', 'nettops'));
	Router::connect('/tablets/all', array('controller' => 'products', 'action' => 'all', 'tablets'));
	
	//Define Products /netbooks/Dell_Mini_10
	Router::connect('/:type/:slug',
		array('controller' => 'products', 'action' => 'view'),
		array(
			'pass' => array('type', 'slug'),
			'type' => 'tablets|nettops|netbooks'
		)
	);
	
	//Compare
	Router::connect('/compare', array('controller' => 'products', 'action' => 'compare'));
	
	//Search
	Router::connect('/search', array('controller' => 'products', 'action' => 'search'));
	
	//Shortlist
	Router::connect('/shortlist/add/:id',
		array('controller' => 'products', 'action' => 'shortlistAdd'),
		array(
			'pass' => array('id'),
			'id' => '[0-9]+'
		)
	);
	
	Router::connect('/shortlist/delete/:id',
		array('controller' => 'products', 'action' => 'shortlistDelete'),
		array('pass' => array('id'))
	);
	
	//Manus
	Router::connect('/manufacturers', array('controller' => 'manufacturers', 'action' => 'index'));
	Router::connect('/manufacturers/:slug',
		array('controller' => 'manufacturers', 'action' => 'view'),
		array(
			'pass' => array('slug'),
		)
	);
	
	//Admin Routes
	Router::connect('/admin', array('controller' => 'products', 'action' => 'dashboard', 'admin' => true));