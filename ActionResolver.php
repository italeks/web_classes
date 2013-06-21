<?php

class ActionResolver
{

	private $request;

	public function __construct(Http\Request $request)
	{
		$this->request = $request;
	}

	public function isFormSubmited()
	{
		return $this->request->query->get('form_action') || $this->request->request->get('form_action');
	}

	public function getAction()
	{
		$action = pathinfo($this->request->getRequestUri(), PATHINFO_FILENAME);
		return strtoupper($action);
	}
}