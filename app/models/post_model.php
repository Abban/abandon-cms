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
		$total_posts = 0;
		$total_published = 0;

		foreach(app()->filesystem->files(app()->config['content_folder'].'/posts') as $post)
		{
			$postname = basename($post, '.md');
			$header = $this->parse_header(app()->filesystem->get($post));
			$content = app()->filesystem->get($post);

			$posts[$postname] = array_merge($header, array(
				'timestamp' => strtotime($header['date']),
				'date'      => Helpers::relative_time(strtotime($header['date'])),
				'url_title' => $postname,
				'url'       => '/' .$header['category'] .'/' .$postname,
				'content'   => $content,
				'wordcount' => Helpers::count_words($content),
			));
		}

		// Order them by timestamp
		$this->entity_sort($posts, 'date', 'ASC', TRUE);

		// Re loop and add numbering
		foreach($posts as $key => $post)
		{
			$total_posts++;
			if($post['status'] == 'Published') $posts[$key]['number'] = ++$total_published;
		}

		// Save posts as json file
		app()->filesystem->put(app()->config['content_folder'].'/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

		// Add post totals to globals
		$json = app()->config['content_folder'].'/globals.json';
		$globals = (app()->filesystem->exists($json)) ? json_decode(app()->filesystem->get($json)) : new stdClass();
		$globals->total_posts = $total_posts;
		$globals->total_published = $total_published;
		app()->filesystem->put($json, json_encode($globals, JSON_PRETTY_PRINT));
	}
}