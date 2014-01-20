<?php
/**
 * Abandon - A flat file blog for lazy people
 *
 * @package  Abandon
 * @author   Abban Dunne <info@abandon.ie>
 */

namespace Abandon;

class App_model
{
	protected $order, $filters, $entity, $data;

	public function __construct()
	{
	}



	/**
	 * Parse variables from the file header into an associative array
	 * 
	 * @param  string $data
	 * @return array
	 */
	protected function parse_header($data)
	{
		$processed = array();

		preg_match("#<!---(.*?)-->#s", $data, $matches);

		if(isset($matches[1]))
		{
			$headers = array_filter(explode("\n", $matches[1]));
			foreach($headers as $header)
			{
				$header = explode(':', $header);
				if(isset($header[0]) && isset($header[1])) $processed[strtolower(trim($header[0]))] = trim($header[1]);
			}
		}

		return $processed;
	}


	public function filter($field, $operator, $value)
	{
		$this->filters[] = array('field' => $field, 'operator' => $operator, 'value' => $value);

		return $this;
	}


	public function order($field, $direction)
	{
		$this->order[$field] = $direction;

		return $this;
	}


	public function limit($limit, $offset = 0)
	{
		$this->limit = $limit;
		$this->offset = $offset;

		return $this;
	}


	protected function equals($field, $value)
	{
		return ($field == $value);
	}


	protected function not($field, $value)
	{
		return ($field == $value);
	}


	protected function like($field, $value)
	{
		return strstr($field, $value);
	}


	protected function before($field, $value)
	{
		return (strtotime($field) < strtotime($value));
	}


	protected function after($field, $value)
	{
		return (strtotime($field) >= strtotime($value));
	}



	public function get()
	{
		$json = app()->config['content_folder']."/$this->entity.json";
		if(app()->filesystem->exists($json) && !$this->{$this->entity})
		{
			$this->{$this->entity} = json_decode(app()->filesystem->get($json), TRUE);
		}

		$data = $this->{$this->entity};

		if($this->filters)
		{
			foreach($data as $key => $post)
			{
				foreach($this->filters as $filter)
				{
					if(method_exists($this, $filter['operator']))
					{
						if(!$this->{$filter['operator']}($post[$filter['field']], $filter['value']) )
						{
							unset($data[$key]);
						}
					}
				}
			}
		}

		return $data;
	}



	/**
	 * Generates a url title from a string
	 * 
	 * @param  string $string
	 * @return string
	 */
	protected function url_title($string)
	{
	    return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
	}
}