<?php
require_once('TwitterAPIExchange.php');

class FollowMe{

    private $account;
    private $debug = false;
    private $set;

	public function __construct(array $settings){
		$this->account	= $settings['account'];
		$this->debug	= $settings['debug'];

		unset($settings['account']);
		unset($settings['debug']);
		$this->set = $settings;
	}

    public function getFeedUser($screen_name, $max_id = false){
        $max_id = ($max_id)?'&max_id='.$max_id:'';

        $url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
        $requestMethod = "GET";
        $getfield = '?screen_name='.$screen_name.'&count=200&include_rts=1&trim_user=0&contributor_details=1'.$max_id;

        $twitter = new TwitterAPIExchange($this->set);
        $x = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
        $twits = json_decode($x, $assoc = TRUE);

        //ESPERAMOS 1 SEGUNDOS
        sleep (1);

        return $twits;
    }

    public function getFeed($search, $max_id = false){
        $max_id = ($max_id)?'&max_id='.$max_id:'';

        $url = "https://api.twitter.com/1.1/search/tweets.json";
		$requestMethod = "GET";

        $getfield = '?q='.$search.'&count=100&include_entities=1'.$max_id;

        $twitter = new TwitterAPIExchange($this->set);
        $x = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
        $twits = json_decode($x, $assoc = TRUE);

        //ESPERAMOS 1 SEGUNDOS
        sleep (1);

        return $twits;
    }
}
