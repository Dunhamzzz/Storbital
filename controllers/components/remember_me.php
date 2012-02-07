<?php
class RememberMeComponent extends Object
{
	var $components = array('Auth', 'Cookie');
	var $controller = null;

	/**
	 * Cookie retention period.
	 *
	 * @var string
	 */
	var $period = '+2 weeks';
	var $cookieName = 'User';

	function startup(&$controller)
	{
		$this->controller =& $controller;
	}

	function remember($username, $password)
	{
		$cookie = array();
		$cookie[$this->Auth->fields['username']] = $username;
		$cookie[$this->Auth->fields['password']] = $password;
		$this->Cookie->write($this->cookieName, $cookie, true, $this->period);
	}

	function check()
	{
		$cookie = $this->Cookie->read($this->cookieName);

		if (!is_array($cookie) || $this->Auth->user())
			return;

		if ($this->Auth->login($cookie))
		{
			$this->Cookie->write($this->cookieName, $cookie, true, $this->period);
		}
		else
		{
			$this->delete();
		}
	}

	function delete()
	{
		$this->Cookie->delete($this->cookieName);
	}
}