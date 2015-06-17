<?php namespace MailChimp;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;
use MailChimp\Exception\MailChimpParseException;
use MailChimp\Exception\MailChimpRequestException;

/**
 * The main service interface using Guzzle
 */
class MailChimp
{
	/** @var string base url for API calls */
	protected static $base_uri = 'https://<dc>.api.mailchimp.com/3.0/';

	/** @var Client our Guzzle HTTP Client object */
	protected $client;

	/** @var Response Guzzle Response object representing the last response from Guzzle call to MailChimp API */
	protected $last_response;

	/** @var  string a string description of the last action taken */
	protected $last_action;

	/**
	 * Constructor
	 *
	 * @param Client $client	Guzzle HTTP client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Make - construct a service object
	 *
	 * @param string $api_key API Key
	 *
	 * @return MailChimp a fully hydrated MailChimp Service, ready to run
	 */
	public static function make($api_key)
	{
		return new self(new Client(self::getConfig($api_key)));
	}

	public static function extractDc($api_key)
	{
		if (empty($api_key)) return null;

		$parts = explode('-', $api_key);
		return array_pop($parts); // last part of the key is the datacenter
	}

	public static function buildUri($dc)
	{
		return str_replace('<dc>', $dc, self::$base_uri);
	}

	public static function getConfig($api_key)
	{
		$dc = self::extractDc($api_key);

		return [
			'base_url' => self::buildUri($dc),
			'defaults' => [
				'auth' => ['mailchimp-subscribe', $api_key]
			],
		];
	}

	public function getClient()
	{
		return $this->client;
	}

	public function get($action)
	{
		$this->last_action = "GET {$action}";

		$request = $this->client->createRequest('GET', $action);

		return $this->send($request);
	}

	public function post($action, array $data = [])
	{
		$this->last_action = "POST {$action}";

		$request = $this->client->createRequest('POST', $action, $data);

		return $this->send($request);
	}

	public function patch($action, array $data = [])
	{
		$this->last_action = "PATCH {$action}";

		$request = $this->client->createRequest('PATCH', $action, $data);

		return $this->send($request);
	}

	protected function send(Request $request, array $options = [])
	{
		try
		{
			$response = $this->client->send($request, $options);
		}
		catch (RequestException $e)
		{
			throw new MailChimpRequestException($e->getMessage(), $e->getCode(), $e);
		}

		$this->last_response = $response;

		try
		{
			return $response->json();
		}
		catch (ParseException $e)
		{
			throw new MailChimpParseException("MailChimp " . $e->getMessage() . " - last command [{$this->last_action}]", $e->getCode(), $e);
		}
	}

	/**
	 * Return the response object from the last API call made
	 *
	 * @return Response Guzzle Reponse object
	 */
	public function getLastResponse()
	{
		return $this->last_response;
	}

	/**
	 * Return the status code from the last API call made
	 *
	 * @return number status code
	 */
	public function getLastStatusCode()
	{
		$last_response = $this->getLastResponse();
		if (! is_null($last_response))
		{
			return $last_response->getStatusCode();
		}
	}

	public function getLastQuery()
	{
		$last_response = $this->getLastResponse();
		if (! is_null($last_response))
		{
			return $last_response->getEffectiveUrl();
		}
	}

	public function getLastAction()
	{
		return $this->last_action;
	}
}
