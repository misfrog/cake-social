Cake Social
=========================

Cake Social is a plugin of the CakePHP 2.x that helps you connect your applications with Software-as-a-Service (SaaS) providers such as Facebook and Twitter.

Requirements
---------
CakePHP v2.x  
Composer

Installation
---------
Ensure require is present in composer.json. This will install the plugin into Plugin/CakeSocial:

	{
		"require": {
			"misfrog/cake-social": "*"
		}
	}

Enable plugin
---------
You need to enable the plugin in your app/Config/bootstrap.php file:

`CakePlugin::load('CakeSocial', array('routes' => true, 'bootstrap' => true));

Sample Applications
----------
An example project is available in the [showcase repository](https://github.com/misfrog/cake-social-showcase).

How to use
----------
TODO
