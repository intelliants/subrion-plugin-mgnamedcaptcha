<?php

require_once dirname(__FILE__) . '/Abstract.php';

class NamedButtonsCaptcha_KeyStorage_Session extends NamedButtonsCaptcha_KeyStorage_Abstract
{
	protected $_namespace = 'dtc';

	public function setNamespace($namespace)
	{
		$this->_namespace = $namespace;

		return $this;
	}

	public function setOptions(array $options)
	{
		parent::setOptions($options);

		if (isset($options['namespace']))
		{
			$this->_namespace = $options['namespace'];
		}

		return $this;
	}

	public function init()
	{
		if (!isset($_SESSION))
		{
			session_start();
		}
	}

	public function read($key)
	{
		$key = $this->_namespace . '.' . $key;

		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	public function exists($key)
	{
		return isset($_SESSION[$this->_namespace . '.' . $key]);
	}

	public function write($key, $value)
	{
		$_SESSION[$this->_namespace . '.' . $key] = $value;

		return $this;
	}
}