<?php namespace MailChimp;

use Hampel\Json\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;
use Hampel\Json\JsonException;
use MailChimp\Exception\MailChimpParseException;
use MailChimp\Exception\MailChimpRequestException;

/**
 * The main service interface using Guzzle
 */
class MailChimp
{
	/** @var string base url for API calls */
	protected static $base_uri = 'https://<dc>.api.mailchimp.com/3.0/';

	/** @var string api key for auth */
	protected $apikey;

	/** @var Client our Guzzle HTTP Client object */
	protected $client;

	/** @var Request Psr7 Request object representing the last request made */
	protected $last_request;

	/** @var Response Guzzle Response object representing the last response from Guzzle call to MailChimp API */
	protected $last_response;

	/** @var  string a string description of the last action taken */
	protected $last_action;

	/**
	 * Constructor
	 *
	 * @param Client $client	Guzzle HTTP client
	 */
	public function __construct(ClientInterface $client, $apikey)
	{
		$this->client = $client;
		$this->apikey = $apikey;
	}

	/**
	 * Make - construct a service object
	 *
	 * @param string $api_key API Key
	 *
	 * @return MailChimp a fully hydrated MailChimp Service, ready to run
	 */
	public static function make($apikey)
	{
		return new self(new Client(self::getConfig($apikey)), $apikey);
	}

	public static function getConfig($apikey)
	{
		$dc = self::extractDc($apikey);

		return ['base_uri' => self::buildUri($dc)];
	}

	public static function buildUri($dc)
	{
		return str_replace('<dc>', $dc, self::$base_uri);
	}

	public static function extractDc($apikey)
	{
		if (empty($apikey)) return null;

		$parts = explode('-', $apikey);
		return array_pop($parts); // last part of the key is the datacenter
	}

	public function getClient()
	{
		return $this->client;
	}

	public function get($action)
	{
		$this->last_action = "GET {$action}";

		return $this->send($action, 'GET');
	}

	public function post($action, array $data = [])
	{
		$this->last_action = "POST {$action}";

		return $this->send($action, 'POST', $data);
	}

	public function patch($action, array $data = [])
	{
		$this->last_action = "PATCH {$action}";

		return $this->send($action, 'PATCH', $data);
	}

	public function send($action, $method = 'GET', $data = [], array $options = [])
	{
		if (empty($this->apikey))
		{
			throw new RuntimeException("API key not yet set");
		}

		if (!array_key_exists('auth', $options))
		{
			$options['auth'] = ['mailchimp-subscribe', $this->apikey];
		}

		$json = null;
		if (!empty($data))
		{
			try
			{
				$json = Json::encode($data);
			}
			catch (JsonException $e)
			{
				throw new RuntimeException("Could not encode JSON payload: " . $e->getMessage(), $e->getCode(), $e);
			}
		}

		$headers = [];

		$request = new Request($method, $action, $headers, $json);

		$this->last_request = $request;

		try
		{
			$response = $this->client->send($request, $options);
		}
		catch (RequestException $e)
		{
			throw new MailChimpRequestException($e->getMessage(), $e->getCode(), $e);
		}

		$this->last_response = $response;

		$body = $response->getBody();

		if ($body->getSize() > 0)
		{
			try
			{
				return Json::decode($body->getContents(), Json::DECODE_ASSOC);
			}
			catch (ParseException $e)
			{
				throw new MailChimpParseException(
					"MailChimp " . $e->getMessage() . " - last command [{$this->last_action}]",
					$e->getCode(),
					$e
				);
			}
		}
	}

	/**
	 * Return the request object from the last API call made
	 *
	 * @return Request Psr7 Request object
	 */
	public function getLastRequest()
	{
		return $this->last_request;
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
		$last_request = $this->getLastRequest();
		if (!is_null($last_request))
		{
//			return strval(\GuzzleHttp\Psr7\Uri::resolve(\GuzzleHttp\Psr7\uri_for($this->client->getConfig('base_uri')), $last_request->getUri()));
			return strval($last_request->getUri());
		}
	}

	public function getLastAction()
	{
		return $this->last_action;
	}
}
