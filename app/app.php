<?php
/**
 * Abandon - A flat file blog for lazy people
 *
 * @package  Abandon
 * @author   Abban Dunne <info@abandon.ie>
 */

namespace Abandon;

class App
{
	public $router, $github, $filesystem, $unzip, $template;
	private $conifg, $routes, $vars;

	public function __construct()
	{
		$this->config = include_once 'config.php';
		$this->routes = include_once 'routes.php';
		$this->vars   = include_once 'globals.php';
		
		$this->debug();

		$this->router     = new \Klein\Klein();
		$this->github     = new \Github\Client();
		$this->filesystem = new \Illuminate\Filesystem\Filesystem();
		$this->unzip      = new \VIPSoft\Unzip\Unzip();
		$this->template   = new \Mustache_Engine();
	}



	public function run()
	{
		// Set url globals from the request merge user globals if they exist
		$this->router->respond(function($request)
		{
			$this->globals($request);
			$this->menus();
		});

		$this->router->respond('/', function($request){ return $this->index($request); });

		$this->router->dispatch();

		//$this->github->authenticate($this->config['github']['token'], FALSE, \Github\Client::AUTH_URL_TOKEN);

		//echo '<pre>';
		
		//$commits = $this->github->api('repo')->commits()->all($this->config['github']['username'], $this->config['github']['repo'], array('sha' => 'master'));
		//print_r($commits);

		//$post = $this->github->api('repo')->contents()->show($this->config['github']['username'], $this->config['github']['repo'], 'categories/notebook');
		//print_r($post);

		//echo base64_decode($post['content']);
		
		/*$blogzip = __DIR__.'/../public/blog.zip';
		file_put_contents($blogzip, $this->github->api('repo')->contents()->archive($this->config['github']['username'], $this->config['github']['repo'], 'zipball'));
		$this->unzip->extract($blogzip, __DIR__.'/../public/blog');
		unset($blogzip);*/
	}


	public function menu()
	{

	}


	public function index($request)
	{
		$this->vars['content'] = $this->template->render($this->filesystem->get($this->config['template_folder'].'/index.html'), $this->vars);
		return $this->render();
	}


	public function category()
	{

	}



	public function page()
	{

	}



	private function render()
	{
		return $this->template->render($this->filesystem->get($this->config['template_folder'].'/wrapper.html'), $this->vars);
	}



	/**
	 * Grabs the request from the request object and creates some template variables
	 * 
	 * @param  object $request
	 * @return void
	 */
	private function globals($request)
	{
		// Check the request type
		$ssl = (!empty($request->server()['HTTPS']) && $request->server()['HTTPS'] != 'off') ? 'https://' : 'http://';

		// Set up the template URL globals
		$this->vars['base_path']   = $request->server()['DOCUMENT_ROOT'];
		$this->vars['base_url']    = $ssl.$request->server()['HTTP_HOST'];
		$this->vars['current_url'] = $this->vars['base_url'].$request->server()['REQUEST_URI'];


		// Process the user globals
		if($this->filesystem->exists($this->config['content_folder'].'/globals.json'))
		{
			// Grab the user globals
			$user_globals = json_decode($this->filesystem->get($this->config['content_folder'].'/globals.json'));

			// Run them through Mustache and add them to the globals array
			foreach($user_globals as $key => $global) $this->vars[$key] = $this->template->render($global, $this->vars);
		}
	}



	/**
	 * Process user defined menus and add them as template variables
	 * 
	 * @return void
	 */
	private function menus()
	{
		if($this->filesystem->exists($this->config['content_folder'].'/menus.json'))
		{
			// Grab the menus
			$menus = array();
			foreach(json_decode($this->filesystem->get($this->config['content_folder'].'/menus.json')) as $key => $menu)
			{
				switch ($menu->type)
				{
					case 'manual':
						foreach($menu->items as $url => $item)
						{
							$processed_url = (in_array(substr($url, 0, 4), array("http", "mail"))) ? $url : $this->vars['base_url'] .'/' .$url;
							$menus[$key][] = array('url' => $processed_url, 'name' => $item);
						}
						break;
					
					default:
						break;
				}
			}
			$this->vars['menus'] = $menus;
		}
	}



	/**
	 * Turns on php error reporting if debug is true
	 * 
	 * @return void
	 */
	private function debug()
	{
		if($this->config['debug'])
		{
			ini_set('display_errors',1);
			ini_set('display_startup_errors',1);
			error_reporting(-1);
		}
	}

}