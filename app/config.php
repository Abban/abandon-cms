<?php
/**
 * Abandon - A flat file blog for lazy people
 *
 * @package  Abandon
 * @author   Abban Dunne <info@abandon.ie>
 */

return array(
	'debug' => true,

	'content_folder' => __DIR__.'/../public/content',
	'template_folder' => __DIR__.'/../public/templates/abandon',
	
	'posts_source' => 'github',

	'github' => array(
		'username' => 'Abban',
		'repo'     => 'blog',
		'token'    => '4a0b393dfdaac83eff978395a3e3befa10824627',
	),

	'timezone' => 'Europe/Berlin',
);