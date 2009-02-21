<?php
/**
 * INDEX BOOTSTRAP
 *
 * This is the starting point (index) for the system. Here we check cached files
 * and then precede to load the system and finally run the controller.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */


/**
 * DEFINE START-UP SYSTEM VALUES
 */

//Get the current time so we can tell how long it takes to run this script
define('START_TIME', microtime(true));
define('START_MEMORY_USAGE', memory_get_usage());

//Set the unique name of the current page
$var = preg_replace("/([^a-z0-9_\-\.]+)/i", '', $_SERVER["REQUEST_URI"]);
define('PAGE_NAME', ($var ? $var : 'index'));


// Get the Site Name: www.site.com -also protects from XSS/CSFR attacks
//preg_match('/(?=[a-z]+:\/\/)?(([a-z0-9\-]{1,70}\.){1,5}([a-z]{2,4}))|localhost/i',
preg_match('/(?=([a-z]+:\/\/)?)(([a-z0-9\-]{1,70}\.){1,5}([a-z]{2,4}))|localhost/i',
($_SERVER["SERVER_NAME"] ? $_SERVER["SERVER_NAME"] : $_SERVER['HTTP_HOST']),
$matches);

//MUST HAVE A HOST!
if(empty($matches[0])) {
   die('don\'t mess with the host.');
}

//Define it for the whole script
define('SITE_NAME', $matches[0]);

//Check to see if it is an ajax request
if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
&& $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
	define('AJAX_REQUEST', 1);
} else {
	define('AJAX_REQUEST', 0);
}

//Require the config file for this site name
require('sites/'. SITE_NAME. '/config.php');

//Require the config file for this server
require_once('sites/'. SITE_NAME. '/server.php');

//Include the common file
require_once(INCLUDES_DIR. 'common.php');

//Get IP address - if proxy lets get the REAL IP address
$ip = !empty($_SERVER["HTTP_X_FORWARDED_FOR"]) 
	? $_SERVER["HTTP_X_FORWARDED_FOR"] 
	: $_SERVER['REMOTE_ADDR'];

//Clean It
sanitize_text($ip);

//Set it
define('IP_ADDRESS', $ip);


/**
 * Check for cached version -die if found
 */
if(fetch_cache(md5(PAGE_NAME. AJAX_REQUEST), null, null)) {

	//If debuging is enabled
	if(DEBUG_MODE) {
		$time = round((microtime(true) - START_TIME), 5);
		$memory = round((memory_get_usage() - START_MEMORY_USAGE) / 1024);

		die('<!-- Rendered in '. $time. ' seconds using '. $memory. ' kb of memory -->');
	}

}





/**
 * Load the Core System Files
 */
//glob() is much slower so we use opendir...
if ($dh = opendir(INCLUDES_DIR)) {
	while (($file = readdir($dh)) !== false) {
		if(preg_match("/.php/", $file)) {
			$file = INCLUDES_DIR. $file;
			//Include the file
			require_once($file);
		}
	}
	closedir($dh);
} else {
	die('Couldn\'t load the system files');
}



/**
 * strip the slashes that have been added to our POST/GET data!
 */
if (ini_get('magic_quotes_gpc')) {

	function array_clean(&$value) {
		$value = stripslashes($value);
	}
	//php 5+ only
	array_walk_recursive($_GET, 'array_clean');
	array_walk_recursive($_POST, 'array_clean');
	array_walk_recursive($_COOKIE, 'array_clean');
}




/**
 * Get the controller from the URI
 */
$routes	= routes::current();

//Set default controller/method if none is set in URL
$routes->set_defaults(
$config['default_controller'],
$config['default_method'],
$config['permitted_uri_chars']
);

//Parse the URI
$routes->parse();

//Fetch the controller/method
$controller	= $routes->fetch(0);
$method		= $routes->fetch(1);


/**
 * START-UP THE SYSTEM!
 */

//If the file doesn't exist - default to the core
if(!file_exists(SITE_DIR. 'controllers/'. $controller. '.php')) {
	$controller = 'core';

	//Else include it
} else {
	//Include the file that has the class
	require_once(SITE_DIR. 'controllers/'. $controller. '.php');
}

//If that file does NOT contain a matching class name
if (!class_exists($controller)) {
	die($controller. ' class not found');
}

//Make sure someone isn't trying to access core/private functions
if(($method !== 'request_error' && method_exists('core', $method))
|| !method_exists($controller, $method)
|| !is_callable(array($controller, $method))) {

	//Trigger a 404 not found error
	$method = 'request_error';

}

//Create a new instance of that controller and pass the $config
$controller = new $controller($config);

//Call the startup hook
$controller->call_hook('startup');

// Call the requested method.
// Any URI segments present (besides the class/function)
// will be passed to the method for convenience
call_user_func_array(array(&$controller, $method), array_slice($routes->fetch(true), 2));

//Call the post-controller hook
$controller->call_hook('post_method');

// And we're done!
$controller->render();

//Call the finish hook
$controller->call_hook('finish');