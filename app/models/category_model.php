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
	protected $entity = 'categories';
	public $categories;

	public function __construct()
	{
	}



	/**
	 * Looks through all posts and creates a category json
	 * 
	 * @return void
	 */
	public function generate()
	{
		$categories = array();
		foreach(app()->filesystem->files(app()->config['content_folder'].'/posts') as $post)
		{
			//$postname = basename($post, '.md');
			$header = $this->parse_header(app()->filesystem->get($post));
			if(isset($header['category']))
			{
				$url_title = $this->url_title($header['category']);
				$categories[$url_title]['title'] = $header['category'];
				$categories[$url_title]['url_title'] = $url_title;
				//$categories[$url_title]['posts'] = isset($categories[$url_title]['posts']) && isset($categories[$url_title]['status']) && $categories[$url_title]['status'] == 'Published' ? ++$categories[$url_title]['posts'] : 0;
			}
		}

		ksort($categories);

		app()->filesystem->put(app()->config['content_folder'].'/categories.json', json_encode($categories, JSON_PRETTY_PRINT));
	}



}