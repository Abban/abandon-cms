<?php
/**
 * Abandon - A flat file blog for lazy people
 *
 * @package  Abandon
 * @author   Abban Dunne <info@abandon.ie>
 */

namespace Abandon;

class Category_model extends App_model
{
	private $name = 'categories';

	private $vars;
	public $categories;

	public function __construct()
	{
	}


	public function init($request)
	{
		if(app()->filesystem->exists(app()->config['content_folder'].'/categories.json'))
		{
			foreach(json_decode(app()->filesystem->get(app()->config['content_folder'].'/categories.json')) as $slug => $category)
			{
				$this->vars[$slug] = array('url' => $this->vars['base_url'] .'/' .$slug, 'title' => $category->title);
				$this->categories[$slug] = $category->title;
			}
		}
		return $this->vars;
	}


	/**
	 * Parse categories and add them as template variables
	 * Also save them as a class variable for easy access
	 * 
	 * @return void
	 */
	public function basic($request)
	{
		return $this->categories;
	}
}