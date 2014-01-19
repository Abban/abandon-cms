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
	private $conifg, $routes, $globals;

	public function __construct()
	{
		$this->config = include_once 'config.php';
		$this->routes = include_once 'routes.php';
		$this->globals = include_once 'globals.php';
		
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
		$this->router->respond(function($request){ $this->globals($request); });

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
		$this->globals['content'] = $this->template->render($this->filesystem->get($this->config['template_folder'].'/index.html'), $this->globals);
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
		return $this->template->render($this->filesystem->get($this->config['template_folder'].'/wrapper.html'), $this->globals);
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
		$this->globals['base_path']   = $request->server()['DOCUMENT_ROOT'];
		$this->globals['base_url']    = $ssl.$request->server()['HTTP_HOST'];
		$this->globals['current_url'] = $this->globals['base_url'].$request->server()['REQUEST_URI'];

		if($this->filesystem->exists($this->config['content_folder'].'/globals.json'))
		{
			// Grab the user globals
			$user_globals = (Array)json_decode($this->filesystem->get($this->config['content_folder'].'/globals.json'));

			// Runt them through Mustache and add them to the globals array
			foreach($user_globals as $key => $global) $this->globals[$key] = $this->template->render($global, $this->globals);

			// Merge them into the global array
			//$this->globals = array_merge($this->globals, $user_globals);
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