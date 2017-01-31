<?php
/**
 * Usage
 * This version of the Facebook SDK for PHP requires PHP 5.4 or greater
 */
require_once( ROOT_PATH . '/Facebook_v4/FacebookSession.php' );
require_once( ROOT_PATH . '/Facebook_v4/FacebookRedirectLoginHelper.php' );
require_once( ROOT_PATH . '/Facebook_v4/FacebookRequest.php' );
require_once( ROOT_PATH . '/Facebook_v4/FacebookResponse.php' );
require_once( ROOT_PATH . '/Facebook_v4/FacebookSDKException.php' );
require_once( ROOT_PATH . '/Facebook_v4/FacebookRequestException.php' );
require_once( ROOT_PATH . '/Facebook_v4/FacebookAuthorizationException.php' );
require_once( ROOT_PATH . '/Facebook_v4/GraphObject.php' );
require_once( ROOT_PATH . '/Facebook_v4/GraphUser.php' );
require_once( ROOT_PATH . '/Facebook_v4/GraphSessionInfo.php' );
require_once( ROOT_PATH . '/Facebook_v4/Entities/AccessToken.php' );
require_once( ROOT_PATH . '/Facebook_v4/HttpClients/FacebookCurl.php' );
require_once( ROOT_PATH . '/Facebook_v4/HttpClients/FacebookHttpable.php' );
require_once( ROOT_PATH . '/Facebook_v4/HttpClients/FacebookCurlHttpClient.php' );

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurl;
use Facebook\HttpClients\FacebookHttpable;
use Facebook\HttpClients\FacebookCurlHttpClient;


/**
 * Class Facebook
 */
class Facebook 
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
	 * @var FacebookRedirectLoginHelper
	 */ 
	private $helper;
	
	/**
	 * @var FacebookSession
	 */ 
	private $session;
	
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
	private $versionSDK = "4.0.0";

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
		$this->permission 			= $permission;
		$this->redirectLoginURL 	= $redirectLoginURL;
		$this->redirectLogoutURL 	= $redirectLogoutURL;
		
		FacebookSession::setDefaultApplication( $this->appId, $this->secretKey );	
		$this->helper = new FacebookRedirectLoginHelper( $this->redirectLoginURL );
	
		// Create new session from saved access_token
		if ( isset( $_SESSION ) && isset($_SESSION[FB_ACCESS_TOKENS]) ) {
			
			$this->session = new FacebookSession( $_SESSION[FB_ACCESS_TOKENS]);
			try {
				if (!$this->session->validate()) {
					$this->session = NULL;
					$_SESSION[FB_ACCESS_TOKENS] = NULL;
				}
			} catch( Exception $e ) {
				
				$this->session = NULL;
				$_SESSION[FB_ACCESS_TOKENS] = NULL;
			}
		}
		
		// If not exist session then create new session
		if ( !isset($this->session) || $this->session === NULL ) {
		
			try {
				$this->session = $this->helper->getSessionFromRedirect();					
			} catch( FacebookRequestException $ex ) {
				print_r( $ex );
			} catch( Exception $ex ) {
				print_r( $ex );
			}
		}
	
		// Create new session from saved access_token
		if ( isset($this->session) ) {
							
			$_SESSION[FB_ACCESS_TOKENS] = $this->session->getToken();
			$this->session = new FacebookSession( $this->session->getToken() );		
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
		if( !isset($this->session) ) 
			header( "Location:".$this->helper->getLoginUrl($this->permission) );
	}
	
	/**
	 * Logout Facebook. 
	 *
	 * @access public
	 */
	public function Logout()
	{
		if( isset($this->session) )
			header( "Location:".$this->helper->getLogoutUrl($this->session, $this->redirectLogoutURL) );
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
		$tmpId = 0;
		if (isset($_SESSION[FB_ACCESS_TOKENS])) 
		{	
			$this->session = new FacebookSession($_SESSION[FB_ACCESS_TOKENS]);
			try 
			{	
				if (!$this->session->validate()) 
				{
					$this->session = NULL;
					$_SESSION[FB_ACCESS_TOKENS] = NULL;
					$this->isConnected = false;
					return array("result"=>$this->isConnected, "description"=>"Facebook session invalidate.");
				}
			} 
			catch( Exception $e ) 
			{	
				$this->session = NULL;
				$_SESSION[FB_ACCESS_TOKENS] = NULL;
				$this->isConnected = false;
				return array("result"=>$this->isConnected, "description"=>$e->__toString());
			}	
			
			$request = new FacebookRequest($this->session, 'GET', '/me');
			$response = $request->execute();
			$graphObject = $response->getGraphObject();
			$tmpId = $graphObject->getProperty("id");
		}
		
		$this->isConnected = ($tmpId != 0)? true : false;
		if ($this->isConnected)
			return array("result"=>$this->isConnected, "accessToken"=>$_SESSION[FB_ACCESS_TOKENS] );
		else
			return array("result"=>$this->isConnected, "description"=>"Not logged in Facebook.");
	}
	
	/**
	 * This method get access token of session
	 *
	 * @access public
	 */
	public function getAccessToken()
	{
		if( isset($this->session) )
			return $this->session->getToken();
		
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
				$request = new FacebookRequest($this->session, 'GET', '/me');
				$response = $request->execute();
				$graphUser = $response->getGraphObject(GraphUser::className());		
	
				$user = array();		
				$user["id"] 			= $graphUser->getId();
				$user["name"] 			= $graphUser->getName();
				$user["email"] 		= $graphUser->getEmail();
				$user["gender"] 		= $graphUser->getGender();
				$user["link"]	 		= $graphUser->getLink();
				$user["birthday"] 		= $graphUser->getBirthday()->format('Y-m-d');
				$user["photoUser"]		= "https://graph.facebook.com/" . $user["id"] . "/picture";
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
				$request = new FacebookRequest($this->session, 'GET', '/me/taggable_friends');
				$response = $request->execute();
				$friends = $response->getGraphObject()->asArray();		
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
			$request = (new FacebookRequest($this->session, 'POST', $post_id, $parameters));
			$response = $request->execute();
			$graphObject = $response->getGraphObject();
			
			if ($graphObject->getProperty("id")!=0)
				return array("result"=>true);
			else
				return array("result"=>false, "description"=>"Post failed");
		} catch(FacebookRequestException $e) {
			return array("result"=>false, "description"=>$e->getMessage());
		}
	}
	
}
?>