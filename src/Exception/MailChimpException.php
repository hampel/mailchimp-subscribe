<?php namespace MailChimp\Exception;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Exception\RequestException;

class MailChimpException extends RuntimeException
{
	protected $response;

	public function __construct($message = "", $code = 0, \Exception $previous = null)
	{
		if (! is_null($previous))
		{
			if ($previous instanceof RequestException && $previous->hasResponse())
			{
				$this->response = $previous->getResponse();
			}
			elseif ($previous instanceof ParseException)
			{
				$this->response = $previous->getResponse();
			}
		}

		parent::__construct($message, $code, $previous);
	}

	public function getResponse()
	{
		return $this->response;
	}
}
