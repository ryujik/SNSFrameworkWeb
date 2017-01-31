<?php
/**
 * Usage
 * This version of the Facebook SDK 3.2.3 is DEPRECATED
 */

require_once( ROOT_PATH . '/Facebook_v3/facebook.php' );

/**
 * Class Facebook
 */
class Facebook_v3 
{
	/**
	 * @var string
	 */
	private $appId;
	
	/**
	 * @var string
	 */
	private $secretKey;
	
	/**
	 * @var Facebook
	 */ 
	private $facebook;
	
	/**
	 * @var Array<String>
	 */ 
	private $permission;
	
	/**
	 * @var string
	 */ 
	private $redirectLoginURL;
	
	/**
	 * @var string
	 */ 
	private $redirectLogoutURL;
	
	/**
	 * @var string
	 */ 
	private $versionSDK = "3.2.3";

	/**
	 * @var bool
	 */ 
	private $isConnected = false;

	/**
	 * Constructor Facebook Class. Set appId, secret key and permission.
	 * Initializing Facebool App with appId and secret key. But if saved
	 * access token then create new session from saved access token.
	 *
	 * @param string 			$appId
	 * @param string 			$secretKey
	 * @param string 			$redirectURL
	 * @param array<string> 	$permission
	 */
	public function __construct( $appId, $secretKey, $redirectLoginURL, $redirectLogoutURL, $permission )
	{
		$this->appId 				= $appId;
		$this->secretKey 			= $secretKey;
		$this->redirectLoginURL 	= $redirectLoginURL;
		$this->redirectLogoutURL 	= $redirectLogoutURL;
		$this->permission 			= array( 'scope' => implode(', ', $permission) );
			
		$config = array(
			'appId' => $this->appId,
			'secret' => $this->secretKey,
			'cookie' => true,
			'fileUpload' => true
		);
		
		$this->facebook = new Facebook( $config );
	
		// Create new session from saved access_token
		if ( isset( $_SESSION ) && isset($_SESSION[FB_ACCESS_TOKENS]) ) {
			$this->facebook->setAccessToken($_SESSION[FB_ACCESS_TOKENS]);
		}
		
		if( $this->facebook->getUser() != 0)
		{
			$_SESSION[FB_ACCESS_TOKENS] = $this->facebook->getAccessToken();
			$this->isConnected = true;
		}
	}
	
	/**
	 * Facebook SDK version
	 *
	 * @access public
	 */
	public function getVersionSDK()
	{
		return $this->versionSDK;
	}
	
	/**
	 * Login Facebook. If the user accepts permission to 
	 * interact with the application then redirects to 
	 * the url assigned in the constructor. After login
	 *
	 * @access public
	 */
	public function Login()
	{
		$this->permission["redirect_uri"] = $this->redirectLoginURL;
		if( $this->facebook->getUser() == 0 ) 
			header( "Location: " . $this->facebook->getLoginUrl($this->permission) );
	}
	
	/**
	 * Logout Facebook. 
	 *
	 * @access public
	 */
	public function Logout()
	{
		if( $this->facebook->getUser() != 0 )
			header( "Location: " . $this->facebook->getLogoutUrl( array('next'=>$this->redirectLogoutURL) ) );
	}
	
	/**
	 * Check user if connected in Facebook. Get user and
	 * check if id user isn't zero.
	 * 0 is not login, but different to 0 is logged.
	 *
	 * @access public
	 * @return array(result=>bool, description=>error)
	 */
	public function getIsConnected()
	{
		if ($this->facebook->getUser() != 0) 
		{	
			$this->isConnected = true;
			return array("result"=>$this->isConnected, "accessToken"=>$this->facebook->getAccessToken() );
		}
		else
		{
			$this->isConnected = false;
			return array("result"=>$this->isConnected, "description"=>"Not logged in Facebook.");
		}
	}
	
	/**
	 * This method get access token of session
	 *
	 * @access public
	 */
	public function getAccessToken()
	{
		if( $this->facebook->getUser() != 0 )
			return $this->facebook->getAccessToken();
		
		return NULL;
	}
	
	/**
	 * Get user Facebook. 
	 *
	 * @access public
	 * @return array(result=>bool, description=>NULL | user=>array)
	 */
	public function getUser()
	{
		try 
		{
			if ($this->isConnected) 
			{				
				$user_profile = $this->facebook->api('/me','GET');
	
				$user = array();		
				$user["id"] 			= $user_profile["id"];
				$user["name"] 			= $user_profile["name"];
				$user["email"] 		= $user_profile["email"];
				$user["gender"] 		= $user_profile["gender"];
				$user["link"]	 		= $user_profile["link"];
				$user["birthday"] 		= $user_profile["birthday"];
				$user["photoUser"]		= "https://graph.facebook.com/" . $user_profile["id"] . "/picture";
				$user["result"]		= true;
				return array("result"=>true, "user"=>$user);
			} 
		} 
		catch(FacebookRequestException $e) 
		{
			return array("result"=>false, "description"=>$e->getMessage());
		}
		
		return array("result"=>false, "description"=>"User not logged in facebook or not permission.");
	}
	
	/**
	 * Get list friends of user Facebook. 
	 *
	 * @access public
	 * @return array(result=>bool, description=>NULL | user=>getGraphObject)
	 */
	public function getFriends()
	{
		try 
		{
			if ($this->isConnected) 
			{				
				$friends = $this->facebook->api('/me/taggable_friends','GET');
				return array("result"=>true, "friends"=>$friends);
			} 
		} 
		catch(FacebookRequestException $e) 
		{
			return array("result"=>false, "description"=>$e->getMessage());
		}
		
		return array("result"=>false, "description"=>"User not logged in facebook or not permission.");
	}
	
	/**
	 * Post wall message or photo. First upload image to
	 * folder tmp then post photo to facebook.
	 *
	 * @access public
	 * @param string 	$post_id
	 * @param array 	$parameters
	 * @return array(result=>bool, description)
	 */
	public function postWall($post_id, $parameters) 
	{	
		try {
			
			$ret_obj = $this->facebook->api($post_id, 'POST', $parameters);
			
			if ($ret_obj["id"] != 0)
				return array("result"=>true, "post_id"=>$ret_obj["id"]);
			else
				return array("result"=>false, "description"=>"Post failed");
		} catch(FacebookRequestException $e) {
			return array("result"=>false, "description"=>$e->getMessage());
		}
	}
}
?>