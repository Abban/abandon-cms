<?php
/**
 * Abandon - A flat file blog for lazy people
 *
 * @package  Abandon
 * @author   Abban Dunne <info@abandon.ie>
 */

namespace Abandon;

class Post_model extends App_model
{
	protected $entity = 'posts';
	protected $posts = FALSE;



	/**
	 * Get all posts files and save them into a json file
	 * 
	 * @return void
	 */
	public function generate()
	{
		$posts = array();
		foreach(app()->filesystem->files(app()->config['content_folder'].'/posts') as $post)
		{
			$postname = basename($post, '.md');
			$header = $this->parse_header(app()->filesystem->get($post));
			$posts[$postname] = array_merge($header, array(
				'date'      => Helpers::relative_time(strtotime($header['date'])),
				'url_title' => $postname,
				'url'       => '/' .$header['category'] .'/' .$postname,
				'content'   => app()->filesystem->get($post),
			));
		}

		app()->filesystem->put(app()->config['content_folder'].'/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
	}
}