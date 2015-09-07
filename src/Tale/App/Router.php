<?php

namespace Tale\App;

class Router
{

	private $_routes;

	public function __construct(array $routes = null)
	{

		$this->_routes = $routes ? $routes : [];
	}

	public function setRoute($route, $handler)
	{

		if (!is_callable($handler))
			throw new \InvalidArgumentException(
				"Argument 2 passed to Router->setRoute needs to be"
				."a valid PHP-style callback"
			);

		$this->_routes[$route] = $handler;

		return $this;
	}

	public function route($string)
	{

		foreach ($this->_routes as $route => $handler) {

            if ($result = $this->match($route, $string)) {

                if (($result = call_user_func($handler, $result)) !== false) {

                    return $result;
                }
            }
        }

		return null;
	}

	protected function getRegExFromRoute($route)
	{

		return '/^'.str_replace('/', '\\/', preg_replace_callback('#(.)?:([a-z\_]\w*)(\?)?#i', function ($m) {

			$key = $m[2];
			$initiator = '';
			$optional = '';

			if (!empty($m[1])) {

				$initiator = '(?<'.$key.'Initiator>'.preg_quote($m[1]).')';
			}

			if (!empty($m[3]))
				$optional = '?';

			return '(?:'.$initiator.'(?<'.$key.'>[a-z0-9\_\-]*?))'.$optional;

		}, $route)).'$/i';
	}

	protected function match($route, $string)
	{

		$matches = [];
		$regEx = $this->getRegExFromRoute($route);
		$isMatch = preg_match($regEx, $string, $matches);

		if (!$isMatch)
			return false;

		$vars = [];
		if (!empty($matches))
			foreach ($matches as $name => $value)
				if (is_string($name) && !empty($value))
					$vars[$name] = $value;

		return $vars;
	}
}