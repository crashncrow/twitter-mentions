<?php
class Model{
	private static $instance = NULL;

	private function __construct() {}

    private function __clone() {}

	// SINGLETON CONNECTION
	public static function getInstance() {
		if (!isset(self::$instance)) {
			$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
			self::$instance = new PDO('mysql:host='.SERVER.';dbname='.DBNAME, USER, PASS, $pdo_options);
		}

		return self::$instance;
    }

	public static function build(){
		//Tabla de tweets
		$sql = 'SELECT 1 FROM statuses LIMIT 1;';

		try {
			$stmt = Model::getInstance()->prepare($sql);
	    	$stmt->execute();
		}
		catch(PDOException $e) {
			if(DEBUG){
				echo $sql . "<br>" . $e->getMessage();
			}

			$sql = 'CREATE TABLE `statuses` (
				  `id_twit` varchar(45) NOT NULL,
				  `query` varchar(45) NOT NULL,
				  `screen_name` varchar(45) NOT NULL,
				  `text` varchar(400) DEFAULT NULL,
				  `text_raw` varchar(400) NOT NULL,
				  `source` varchar(45) DEFAULT NULL,
				  `geo` varchar(400) DEFAULT NULL,
				  `coordinates` varchar(2000) DEFAULT NULL,
				  `place` varchar(2000) DEFAULT NULL,
				  `retweeted_status` varchar(140) DEFAULT NULL,
				  `retweeted_status_user` varchar(45) DEFAULT NULL,
				  `is_quote_status` varchar(45) DEFAULT NULL,
				  `retweet_count` varchar(45) DEFAULT NULL,
				  `favorite_count` varchar(45) DEFAULT NULL,
				  `hashtags` varchar(2000) DEFAULT NULL,
				  `mentions` varchar(2000) DEFAULT NULL,
				  `lang` varchar(45) DEFAULT NULL,
				  `created_at` datetime DEFAULT NULL,
				  PRIMARY KEY (`id_twit`,`query`),
				  UNIQUE KEY `id_twit_UNIQUE` (`id_twit`),
				  UNIQUE KEY `created_at_UNIQUE` (`created_at`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1';

			Model::getInstance()->exec($sql);
		}

		//Tabla de configuraciones
		$sql = 'SELECT 1 FROM configuration LIMIT 1;';
		try {
			$stmt = Model::getInstance()->prepare($sql);
	    	$stmt->execute();
		}
		catch(PDOException $e) {
			if(DEBUG){
				echo $sql . "<br>" . $e->getMessage();
			}

			$sql = 'CREATE TABLE `configuration` (
				  `id` INT NOT NULL AUTO_INCREMENT,
				  `key` VARCHAR(45) NOT NULL,
				  `value` VARCHAR(255) NOT NULL,
				  PRIMARY KEY (`id`))';
		  	Model::getInstance()->exec($sql);
		}
	}

	public static function getNextUser($q){
		$exclude = array();

		$sql = 'SELECT `screen_name` FROM statuses WHERE query = "'.$q.'"
				GROUP BY screen_name
				HAVING count(`screen_name`) > 10';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {
			$exclude[] = $row['screen_name'];
		}

		$mentions = Model::getMentions($q, $exclude);

		$user = false;
		$weight = 0;
		foreach($mentions as $m){
			if((int)$m['weight'] > $weight) {

				$weight = $m['weight'];
				$user = $m['text'];
			}
		}

		return $user;
	}

	public static function getMentions($q, $exclude = false){
		$ret = array();

		$sql = 'SELECT `mentions` FROM statuses WHERE `mentions` <> "" AND query = "'.$q.'"';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {

			$mentions = explode(',', $row['mentions']);

			foreach ($mentions as $m){
				if( !$exclude || ($exclude && !in_array($m, $exclude)) ){
					$count = isset($ret[$m])? $ret[$m]['weight']+1:0;
					$ret[$m] = array('text' => $m, 'weight' => $count);;
				}
			}
		}

		$result = array();

		foreach($ret as $k => $v){
			$result[] = $v;
		}

		return $result;
	}

	public static function getTotalTwitsByUser($u){
		$count = 0;
		$sql = 'SELECT count(*) `c` FROM statuses where screen_name = "'.$u.'"';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {
			$count = $row['c'];
			break;
		}

		return $count;
	}

	public static function getTotalTwitsByQuery($q){
		$count = 0;
		$sql = 'SELECT count(*) `c` FROM statuses where query = "'.$q.'"';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {
			$count = $row['c'];
			break;
		}

		return $count;
	}

	public static function getDataForCalendar($screen_name){
		$sql = 'SELECT screen_name, SUBSTRING(created_at,  1, 10) tweet_date, count(screen_name) tweet_cant
				FROM bdunlp.statuses
				WHERE created_at > "2014-01-01" and screen_name = "' . $screen_name . '"
				GROUP BY screen_name, SUBSTRING(created_at,  1, 10)
				ORDER BY tweet_date';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		$result = array();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {
			$r = array();
			$r['date']  = $row['tweet_date'];
			$r['year']  = substr($row['tweet_date'], 0, 4);
			$r['month'] = substr($row['tweet_date'], 5, 2);
			$r['day']   = substr($row['tweet_date'], 8, 2);
			$r['count'] = $row['tweet_cant'];

			$result[] = $r;
		}

		//echo "<pre>";print_r($result);die();

		return $result;
	}

	public static function getDataForKeywords($screen_name){
		$sql = 'SELECT screen_name, SUBSTRING(created_at,  1, 10) tweet_date, count(screen_name) tweet_cant, GROUP_CONCAT(text SEPARATOR " ") keywords
				FROM bdunlp.statuses
				WHERE screen_name = "' . $screen_name . '"
				GROUP BY screen_name, SUBSTRING(created_at,  1, 10)
				ORDER BY tweet_date';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		$result = array();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {

			$kw = array();
			$keywords = explode(' ', cleanText($row['keywords']));
			foreach($keywords as $k){
				$kw[$k] = isset($kw[$k])?$kw[$k]+1:1;
			}

			arsort($kw);

			$result[$row['tweet_date']] = $kw;
		}
		echo "<pre>";
		print_r($result);
		die();

		return $result;
	}

	public static function getDataForKeywordsTotal($screen_name, $limit = 10, $year = false, $month = false){
		$year_where  = ($year)?' AND SUBSTRING(created_at,  1, 4) = "'.$year.'"':'';
		$month_where = ($month)?' AND SUBSTRING(created_at,  6, 2) = "'.$month.'"':'';

		$sql = 'SELECT screen_name, SUBSTRING(created_at,  1, 10) tweet_date, count(screen_name) tweet_cant, GROUP_CONCAT(text SEPARATOR " ") keywords
				FROM bdunlp.statuses
				WHERE created_at > "2016-04-19" and created_at < "2016-05-20" and screen_name = "' . $screen_name . '"'. $year_where . $month_where .'
				GROUP BY screen_name, SUBSTRING(created_at,  1, 10)
				ORDER BY tweet_date';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		$result = array();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {
			$keywords = explode(' ', cleanText($row['keywords']));
			foreach($keywords as $k){
				$result[$k] = isset($result[$k])?$result[$k]+1:1;
			}
		}

		arsort($result);

		$result = array_slice($result, 0, $limit);

		//

		return $result;
	}

	public static function persistTweets($query, $screen_name, $twits){
		$max_id = false;

		if(is_array($twits) && count($twits) > 0 && !isset($twits['errors']) && !isset($twits['error'])){

            foreach($twits as $s){

                $s['id'] = utf8_decode($s['id']);

                $max_id = $s['id'];

                $text_raw = addslashes(utf8_decode($s['text']));

                $s['text'] = Tools::cleanText($s['text']);

                //si arranca con RT, es un retweet
                if(substr( $s['text'], 0, 3 ) === "RT "){
                    $s['retweeted_status'] = 1;
                    $s['retweeted_status_user'] = utf8_decode($s['entities']['user_mentions'][0]['screen_name']);
                }
                else{
                    $s['retweeted_status'] = 0;
                    $s['retweeted_status_user'] = '';
                }

                $s['source'] = strip_tags(utf8_decode($s['source']));
                $s['geo'] = utf8_decode(json_encode($s['geo']));
                $s['coordinates'] = utf8_decode(json_encode($s['coordinates']));
                $s['place'] = utf8_decode(json_encode($s['place']));

                $s['is_quote_status'] = utf8_decode($s['is_quote_status']);

                $s['quoted_status'] = isset($s['quoted_status'])?$s['quoted_status']:0;

                if($s['is_quote_status'] == 1){
                    $s['retweeted_status'] = 1;
                    $s['retweeted_status_user'] = utf8_decode($s['quoted_status']['user']['screen_name']);
                }
                else{
                    $s['is_quote_status'] = 0;
                }

                $s['retweet_count'] = utf8_decode($s['retweet_count']);
                $s['favorite_count'] = utf8_decode($s['favorite_count']);

                //hashtags
                $hashtags = array();
                foreach($s['entities']['hashtags'] as $ht){
                    $hashtags[] = $ht['text'];
                }
                $hashtags = implode(',',$hashtags);

                $mentions = array();
                foreach($s['entities']['user_mentions'] as $u){
                    $mentions[] = $u['screen_name'];
                }
                $mentions = implode(',',$mentions);

                $s['lang'] = utf8_decode($s['lang']);
                $s['created_at'] = (isset($s['created_at']))?gmdate('Y-m-d H:i:s', strtotime($s['created_at'])):'0000-00-00';

                $sql = "REPLACE INTO `statuses`
                        (
                        `id_twit`, `query`, `screen_name`,
                        `text`, `text_raw`, `source`, `geo`, `coordinates`,
                        `place`, `retweeted_status`, `retweeted_status_user`,
                        `is_quote_status`, `retweet_count`, `favorite_count`,
                        `hashtags`, `mentions`, `lang`, `created_at`
                        )
                    VALUES
                        (
                        '".$s['id']."', '".$query."','".$screen_name."', '".
                        $s['text']."', '".$text_raw."', '".$s['source']."', '".$s['geo']."', '".$s['coordinates']."', '".
                        $s['place']."', '".$s['retweeted_status']."', '".$s['retweeted_status_user']."', '".
                        $s['is_quote_status']."', '".$s['retweet_count']."', '".$s['favorite_count']."', '".
                        $hashtags."', '".$mentions."', '".$s['lang']."', '".$s['created_at']."'
                        )
                    ";

				try {
					Model::getInstance()->exec($sql);
				}
				catch(PDOException $e) {
					if(DEBUG){
						echo $sql . "<br>" . $e->getMessage();
					}
				}
            }
		}

		return $max_id;
	}

	public static function persistTweetsQ($q, $twits){
		$max_id = false;

		if(is_array($twits) && count($twits) > 0 && !isset($twits['errors']) && !isset($twits['error'])){

            foreach($twits['statuses'] as $s){
				$screen_name = $s['user']['screen_name'];

                $s['id'] = utf8_decode($s['id']);

                $text_raw = addslashes(utf8_decode($s['text']));

                $s['text'] = Tools::cleanText($s['text']);

                //si arranca con RT, es un retweet
                if(substr( $s['text'], 0, 3 ) === "RT "){
                    $s['retweeted_status'] = 1;
                    $s['retweeted_status_user'] = utf8_decode($s['entities']['user_mentions'][0]['screen_name']);
                }
                else{
                    $s['retweeted_status'] = 0;
                    $s['retweeted_status_user'] = '';
                }

                $s['source'] = strip_tags(utf8_decode($s['source']));
                $s['geo'] = utf8_decode(json_encode($s['geo']));
                $s['coordinates'] = utf8_decode(json_encode($s['coordinates']));
                $s['place'] = utf8_decode(json_encode($s['place']));

                $s['is_quote_status'] = utf8_decode($s['is_quote_status']);

                $s['quoted_status'] = isset($s['quoted_status'])?$s['quoted_status']:0;

                if($s['is_quote_status'] == 1){
                    $s['retweeted_status'] = 1;
                    $s['retweeted_status_user'] = utf8_decode($s['quoted_status']['user']['screen_name']);
                }
                else{
                    $s['is_quote_status'] = 0;
                }

                $s['retweet_count'] = utf8_decode($s['retweet_count']);
                $s['favorite_count'] = utf8_decode($s['favorite_count']);

                //hashtags
                $hashtags = array();
                foreach($s['entities']['hashtags'] as $ht){
                    $hashtags[] = $ht['text'];
                }
                $hashtags = implode(',',$hashtags);

                $mentions = array();
                foreach($s['entities']['user_mentions'] as $u){
                    $mentions[] = $u['screen_name'];
                }
                $mentions = implode(',',$mentions);

                $s['lang'] = utf8_decode($s['lang']);
                $s['created_at'] = (isset($s['created_at']))?gmdate('Y-m-d H:i:s', strtotime($s['created_at'])):'0000-00-00';

                $sql = "REPLACE INTO `statuses`
                        (
                        `id_twit`, `query`, `screen_name`,
                        `text`, `text_raw`, `source`, `geo`, `coordinates`,
                        `place`, `retweeted_status`, `retweeted_status_user`,
                        `is_quote_status`, `retweet_count`, `favorite_count`,
                        `hashtags`, `mentions`, `lang`, `created_at`
                        )
                    VALUES
                        (
                        '".$s['id']."', '".$q."', '".$screen_name."', '".
                        $s['text']."', '".$text_raw."', '".$s['source']."', '".$s['geo']."', '".$s['coordinates']."', '".
                        $s['place']."', '".$s['retweeted_status']."', '".$s['retweeted_status_user']."', '".
                        $s['is_quote_status']."', '".$s['retweet_count']."', '".$s['favorite_count']."', '".
                        $hashtags."', '".$mentions."', '".$s['lang']."', '".$s['created_at']."'
                        )
                    ";

				try {
					Model::getInstance()->exec($sql);
				}
				catch(PDOException $e) {
					if(DEBUG){
						echo $sql . "<br>" . $e->getMessage();
					}
				}
            }
			if(isset($twits['search_metadata']['next_results'])){
				$str = $twits['search_metadata']['next_results'];
	            $max_id = Tools::get_string_between($str, 'max_id=', '&');
			}

            //ESPERAMOS 1 SEGUNDOS
			//return $str;
		}

		return $max_id;
	}

	public static function hasTwitterKeys(){
		$keys = array(
		    'oauth_access_token',
		    'oauth_access_token_secret',
		    'consumer_key',
		    'consumer_secret',
		);

		$sql = 'SELECT count(*) FROM configuration WHERE `key` IN ("'. implode('","', $keys) .'") and value <>""';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		return (count($keys) == $stmt->fetch()[0]);
	}

	public static function persistTwitterKeys($params){
		Model::cleanTwitterKeys();

		$sql = 'INSERT INTO configuration (`key`, `value`) VALUES';

		foreach($params as $k => $v){
			$sql .= '("'.$k.'", "'.$v.'"),';
		}

		$sql = rtrim($sql, ',');

		try {
			Model::getInstance()->exec($sql);
		}
		catch(PDOException $e) {
			if(DEBUG){
				echo $sql . "<br>" . $e->getMessage();
			}
		}
	}

	public static function getTwitterSettings(){
		$settings = array();

		$keys = array(
		    'oauth_access_token',
		    'oauth_access_token_secret',
		    'consumer_key',
		    'consumer_secret',
		);

		$sql = 'SELECT `key`, `value` FROM configuration WHERE `key` IN ("'. implode('","', $keys) .'") and value <>""';

		$stmt = Model::getInstance()->prepare($sql);
    	$stmt->execute();

		while ($row = $stmt->fetch( PDO::FETCH_ASSOC )) {
			$settings[$row['key']] = $row['value'];
		}

		$settings['debug'] = DEBUG;
		$settings['account'] = '';

		return $settings;
	}

	public static function cleanTwitterKeys(){
		$sql = 'TRUNCATE configuration';

		try {
			Model::getInstance()->exec($sql);
		}
		catch(PDOException $e) {
			if(DEBUG){
				echo $sql . "<br>" . $e->getMessage();
			}
		}
	}
}
