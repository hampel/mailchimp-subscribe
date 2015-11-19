MailChimp Subscribe
===================

A MailChimp API wrapper using Guzzle v6 which implements a simple list subscription interface using the MailChimp v3 
API.

By [Simon Hampel](http://hampelgroup.com/).

Installation
------------

The recommended way of installing MailChimp Subscribe is through [Composer](http://getcomposer.org):

    :::json
    {
        "require": {
            "hampel/mailchimp-subscribe": "~1.0"
        }
    }

Configuration
-------------

To use the MailChimp subscriber, you'll need to generate an API key using the MailChimp admin interface.

Log in to MailChimp and then go to `Account` by clicking on your username/avatar at the top and selecting Account from 
the menu.

Next, go to the `Extras` area and click on `API Keys`.

Click on the `Create a Key` button and give your new key a meaningful label so you know which one it is.

You'll also need to find the list ID of the mailing list you want to subscribe people to.

Click on `Lists` at the top and then click on your list name.

Click on the `Settings` submenu and select the `List name and campaign defaults` option.

Over on the right will be your List ID - make a note of this.

Usage
-----

### Initialisation ###

To start with, you'll need to create a MailChimp object which does the actual communication.

The quick way is to use the factory:

    :::php
    <?php
        use MailChimp\MailChimp;
        
        $apikey = 'YOUR API KEY HERE';
        
        $mc = MailChimp::make($apikey);

Alternatively, if you need more control, you can use the long-hand method:

    :::php
    <?php
        use GuzzleHttp\Client;
        use MailChimp\MailChimp;
        
        $apikey = 'YOU API KEY HERE';
        
        $config = MailChimp::getConfig($apikey);
        $guzzle = new Client($config);
        $mc = new MailChimp($guzzle, $apikey);

You'll also want to create a mailing list object to then work with the list:

    :::php
    <?php
        use MailChimp\MailingList;
        
        $listid = 'YOU LIST ID HERE';
        
        $list = new MailingList($mc, $listid);

We're now ready to work with our list.

### Get Member ###

You can retrieve the details of a member by calling the `getMember` method of the MailingList class:

    :::php
    <?php
        var_dump($list->getMember('user@example.com'));

The getMember function returns `false` if the email address was not found in the list.

### Get Member Status ###

Retrieve the current status of a list member by calling the `getMemberStatus` method:

    :::php
    <?php
        var_dump($list->getMemberStatus('user@example.com'));

Possible return values are: subscribed, unsubscribed, cleaned, pending - or false if the member was not found.  

### Check if Member Exists ###

You can check if a user is a member of a list with the `isMember` method, which returns a boolean result:

    :::php
    <?php
        if ($list->isMember('user@example.com'))
        {
        	echo "user is a member of our list";
        }
        else
        {
        	echo "user is not yet a member of our list";
        }

### Subscribe a Member ###

Subscribe a new email address to your list using the `subscribe` method:

    :::php
    <?php
        var_dump($list->subscribe('user@example.com'));

To have MailChimp send a confirmation to the user before adding them to your list (double opt-in), set the second
parameter to `true`: `$list->subscribe('user@example.com', true)`

You may also send an associative array of merge fields (name-value pairs) as a third parameter:

    :::php
    <?php
        var_dump($list->subscribe('user@example.com', false, ['FNAME' => 'Joe', 'LNAME' => 'Bloggs']));

Details about the subscribed user will be returned.

### Unsubscribe a Member ###

To remove a user from the mailing list, use the `unsubscribe` method:

    :::php
    <?php
        var_dump($list->unsubscribe('user@example.com'));

### Clean a Member ###

To clean an address from the mailing list, use the `clean` method:

    :::php
    <?php
        var_dump($list->clean('user@example.com'));

Cleaned addresses are addresses that have bounced too many times or are now considered invalid.
