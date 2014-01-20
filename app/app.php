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
	public $router, $github, $filesystem, $unzip, $template, $conifg, $globals;
	private $routes, $vars, $categories, $pages, $posts;

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
		$this->template   = new \Handlebars\Handlebars();

		$this->categories = new Category_model();
		$this->posts      = new Post_model();
	}



	public function run()
	{
		// Create global template variables, menus and categories
		$this->router->respond(function($request)
		{
			$this->globals($request);
			$this->menus($request);
			$this->vars['categories'] = $this->categories->get($request);
		});

		// Landing page
		$this->router->respond('/', function($request){ return $this->index($request); });

		$this->router->respond('/generate', function($request){ return $this->generate($request); });
		
		$this->router->respond('/[:category]', function($request)
		{
			if($request->category != 'generate')
			{
				// Check if category exists
				if(array_key_exists($request->category, $this->categories->get($request)))
				{
					return $this->category($request);
				}

				// Check if page exists
				/*elseif(array_key_exists($request->category, $this->pages))
				{
					return $this->page($request);	
				}*/

				// Nothing found return 404
				//return $this->four_oh_four();
			}
		});

		$this->router->respond('/[:category]/[:post]', function($request)
		{

		});

		$this->router->dispatch();
	}



	public function generate()
	{
		$this->categories->generate();
		$this->posts->generate();
	}


	public function menu()
	{

	}


	public function index($request)
	{
		$this->vars['content'] = $this->template->render($this->filesystem->get($this->config['template_folder'].'/index.html'), $this->vars);
		return $this->render();
	}



	public function category($request)
	{
		$this->vars['posts'] = $this->posts->filter('category', 'equals', $request->category)->get();

		$template = ($this->filesystem->exists($this->config['content_folder']."/category-$request->category.html")) ? "category-$request->category" : 'category';
		
		$this->vars['content'] = $this->template->render($this->filesystem->get($this->config['template_folder']."/$template.html"), $this->vars);

		return $this->render();
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
		$this->vars['base_path']   = $this->globals['base_path']   = $request->server()['DOCUMENT_ROOT'];
		$this->vars['base_url']    = $this->globals['base_url']    = $ssl.$request->server()['HTTP_HOST'];
		$this->vars['current_url'] = $this->globals['current_url'] = $this->vars['base_url'].$request->server()['REQUEST_URI'];

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
	private function menus($request)
	{
		if($this->filesystem->exists($this->config['content_folder'].'/menus.json'))
		{
			// Grab the menus
			foreach(json_decode($this->filesystem->get($this->config['content_folder'].'/menus.json')) as $key => $menu)
			{
				switch ($menu->type)
				{
					case 'manual':
						foreach($menu->items as $url => $item)
						{
							$processed_url = (in_array(substr($url, 0, 4), array("http", "mail"))) ? $url : $this->vars['base_url'] .'/' .$url;
							$this->vars['menus'][$key][] = array('url' => $processed_url, 'title' => $item, 'active' => ('/'.$url == $request->uri() ? TRUE: FALSE));
						}
						break;
					
					default:
						break;
				}
			}
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