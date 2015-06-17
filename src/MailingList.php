<?php  namespace MailChimp; 

use MailChimp\Exception\MailChimpRequestException;

class MailingList
{
	/** @var  MailChimp our MailChimp interface */
	protected $mailchimp;

	/** @var string our mailing list ID  */
	protected $list_id;

	function __construct(MailChimp $mailchimp, $list_id)
	{
		$this->mailchimp = $mailchimp;
		$this->list_id = $list_id;
	}

	public static function all(MailChimp $mailchimp)
	{
		return $mailchimp->get("lists");
	}

	public function getMember($email)
	{
		$action = "lists/{$this->list_id}/members/" . md5($email);

		try
		{
			return $this->mailchimp->get($action);
		}
		catch (MailChimpRequestException $e)
		{
			if ($e->getResponse()->getStatusCode() == '404')
			{
				return false;
			}
			else throw $e;
		}
	}

	public function getMemberStatus($email)
	{
		$member = $this->getMember($email);
		if ($member) return $member['status'];
		else return false;
	}

	public function isMember($email)
	{
		return $this->getMemberStatus($email) !== false;
	}

	public function subscribe($email, $confirm = false, $merge_fields = array())
	{
		$data = [
			'email_address' => $email,
			'status' => ($confirm ? 'pending' : 'subscribed'),
		];
		if (!empty($merge_fields)) $data['merge_fields'] = $merge_fields;

		$action = "lists/{$this->list_id}/members/";

		return $this->mailchimp->post($action, ['json' => $data]);
	}

	public function unsubscribe($email)
	{
		return $this->update($email, ['status' => 'unsubscribed']);
	}

	public function clean($email)
	{
		return $this->update($email, ['status' => 'cleaned']);
	}

	public function update($email, $data)
	{
		$action = "lists/{$this->list_id}/members/" . md5($email);

		return $this->mailchimp->patch($action, ['json' => $data]);
	}

	/**
	 * @return MailChimp
	 */
	public function getMailchimp()
	{
		return $this->mailchimp;
	}

	/**
	 * @return string
	 */
	public function getlist_id()
	{
		return $this->list_id;
	}
}
