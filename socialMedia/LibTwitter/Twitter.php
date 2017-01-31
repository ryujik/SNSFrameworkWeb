<?php

require_once( dirname(__FILE__) . '/autoload.php' );

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class Twitter
 */
class Twitter
{
	/**
	 * @var string
	 */
	private $consumerKey;
	
	/**
	 * @var string
	 */
	private $consumerSecret;
	
	/*
	 * @var TwitterOAuth
	 */
	private $connection;
	
	/*
	 * @var array
	 */
	private $request_token;
	
	/**
	 * Constructor Twitter Class. Set consumerKey and consunmerSecret.
	 * Initializing Twitter App. 
	 *
	 * @param string 			$consumerKey
	 * @param string 			$consumerSecret
	 */
	public function __construct( $consumerKey, $consumerSecret )
	{
		$this->consumerKey 	= $consumerKey;
		$this->consumerSecret	= $consumerSecret;
	
		if ( isset($_SESSION[TW_REQUEST_TOKEN]) )  
		{
			if ( isset($_SESSION[TW_ACCESS_TOKEN]) ) 
			{
				$access_token = $_SESSION[TW_ACCESS_TOKEN];
				self::initWithAccessToken( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
			} 
			else
			{
				if ( isset($_REQUEST['oauth_verifier']) )
					self::initWithOAUTH( $_SESSION[TW_REQUEST_TOKEN], $_SESSION[TW_REQUEST_TOKEN_SECRET] ); 
				else
					self::init();	
			}
		}
		else 
		{
			self::init();
		}
	}
	
	public function init()
	{
		$this->connection 		= new TwitterOAuth( $this->consumerKey, $this->consumerSecret );
		$this->request_token 	= $this->connection->oauth( 'oauth/request_token', array('oauth_callback' =>TW_LOGIN_URL_CALLBACK) );
		
		$_SESSION[TW_REQUEST_TOKEN] = $this->request_token['oauth_token'];
		$_SESSION[TW_REQUEST_TOKEN_SECRET] = $this->request_token['oauth_token_secret'];
	}
	
	public function initWithOAUTH( $oauthToken, $oauthTokenSecret )
	{
		$this->connection 	= new TwitterOAuth( $this->consumerKey, $this->consumerSecret, $oauthToken, $oauthTokenSecret );	
	
		$_SESSION[TW_ACCESS_TOKEN] = $this->connection->oauth( 'oauth/access_token', array('oauth_verifier' => $_REQUEST['oauth_verifier']) );
	}
	
	public function initWithAccessToken( $oauthToken, $oauthTokenSecret )
	{
		
		$this->connection = new TwitterOAuth( $this->consumerKey, $this->consumerSecret, $oauthToken, $oauthTokenSecret );
	}
	
	public function Login()
	{
		header("Location: " . $this->connection->url('oauth/authorize', array('oauth_token'=>$this->request_token['oauth_token'])));
	}
	
	public function Logout()
	{
		if ( isset($_SESSION[TW_ACCESS_TOKEN]) )
		{
			session_destroy();
			header("Location: " . TW_LOGOUT_URL_CALLBACK);
		}
	}
	
	public function getIsConnected()
	{
		if( isset($_SESSION[TW_REQUEST_TOKEN]) && isset($_SESSION[TW_ACCESS_TOKEN]) )
		{
			$this->isConnected = true;
			return array("result"=>$this->isConnected);		
		} else {		
			$this->isConnected = false;
			return array("result"=>$this->isConnected, "description"=>"Not logged in Twitter.");
		}
	}
	
	public function getUser()
	{
		if( isset($_SESSION[TW_ACCESS_TOKEN]) ) 
		{
			$access_token = $_SESSION[TW_ACCESS_TOKEN];
			$this->connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			$user_profile = $this->connection->get("account/verify_credentials");
			
			$user = array();		
			$user["id"] 				= $user_profile->id;
			$user["name"] 				= $user_profile->name;
			$user["username"] 			= $user_profile->screen_name;
			$user["location"]			= $user_profile->location;
			$user["description"] 		= $user_profile->description;
			$user["url"] 				= $user_profile->url;
			$user["photoUser"]			= $user_profile->profile_image_url;
			$user["result"]			= true;
			return array("result"=>true, "user"=>$user);
		}
		else 
			return array("result"=>false, "description"=>"User not initialization session.");
	}
	
	public function postWall($parameters)
	{
		if ( isset($_SESSION[TW_ACCESS_TOKEN]) )
		{
			$access_token = $_SESSION[TW_ACCESS_TOKEN];
			$connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			
			$statues = $connection->post("statuses/update", array("status" => $parameters['message']));
			if ($connection->getLastHttpCode() == 200) {
				// Tweet posted succesfully
				return array( "result"=>true );
			} else {
				// Handle error case
				return array( "result"=>false, "description"=>"This message was publised before. HTTP CODE: " . $connection->getLastHttpCode());
			}
		}
			return array( "result"=>false, "description"=>"User not initialization session. First, you need login. ");
	}
	
	public function postWallWithPhoto($parameters)
	{
		if ( isset($_SESSION[TW_ACCESS_TOKEN]) )
		{
			$access_token = $_SESSION[TW_ACCESS_TOKEN];
			$connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	
			$media = $connection->upload('media/upload', array('media' => $parameters['src']));
			$parameters = array(
				'status' => $parameters['message'],
				'media_ids' => implode(',', array($media->media_id_string)),
			);
			$result = $connection->post('statuses/update', $parameters);		
			if ($connection->getLastHttpCode() == 200) {
				// Tweet posted succesfully
				return array( "result"=>true, "get"=>$result );
			} else {
				// Handle error case
				return array( "result"=>false, "description"=>"Error tweet.", "code"=>$connection->getLastHttpCode());
			}
		}
			return array( "result"=>false, "description"=>"User not initialization session. First, you need login. ");
	}
}
?>