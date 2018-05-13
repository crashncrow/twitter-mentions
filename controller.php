<?php
class Controller{
	protected $settings = array();

	public function __construct() {
		Model::build();

		//$this->settings = SETTINGS;
		$this->settings = Model::getTwitterSettings();
	}

	public function index() {
		$view = 'index';

		//Verificamos si las API Keys están configuradas
		if(!Model::hasTwitterKeys()){
			$view = 'config';
		}

		return $view;
	}

	public function processConfig($params){
		Model::persistTwitterKeys($params);
	}

	public function testAPITwitter($params){
		$params['debug'] = DEBUG;
		$params['account'] = '';

		$fm = new FollowMe($params);
		$tweets = $fm->getFeed('test', false);

		if(isset($tweets['errors'])){
			echo 'error';
		}
		else{
			echo 'true';
		}

		die();
	}

	public function processQuery($q, $max_id){
		$ret = '';
		$user = '';

		//TODO: CHEQUEAMOS QUE NO TENGA CARACTERES RAROS
		$q = str_replace('@', '', $q);
		$q = trim($q);

		$valid = Tools::validateUser($q);

		if($valid){
			$fm = new FollowMe($this->settings);

			$tweets = $fm->getFeed($q, $max_id);
			$max_id = Model::persistTweetsQ($q, $tweets);

			$count = Model::getTotalTwitsByQuery($q);

			$ret = array('max_id' => $max_id, 'count' => $count);
		}

		sleep (2);
		echo json_encode($ret);
		die();
	}

	public function processUser($query, $user, $max_id){
		$ret = '';

		//TODO: CHEQUEAMOS QUE NO TENGA CARACTERES RAROS
		$user = str_replace('@', '', $user);
		$user = trim($user);

		$valid = Tools::validateUser($user);

		if($valid){
			$fm = new FollowMe($this->settings);

			$tweets = $fm->getFeedUser($user, $max_id);
			$new_max_id = Model::persistTweets($query, $user, $tweets);

			if(!$new_max_id || $new_max_id == $max_id){
				$user = Model::getNextUser($query);

				$ret = array('user'=> $user, 'max_id' => 0);
			}
			else{
				$ret = array('user'=> $user, 'max_id' => $new_max_id);
			}
		}

		sleep (2);
		echo json_encode($ret);
		die();
	}

	public function processMentions($q){
		$ret = Model::getMentions($q);
		sleep (2);
		echo json_encode($ret);
		die();
	}
}
