<?php 
error_reporting(0);

define("ROOT_PATH", dirname(__FILE__));

require_once(ROOT_PATH . "/FBConstant.php");

// PHP 5.3.3
require_once(ROOT_PATH . "/Facebook_v3.php" );

// PHP 5.4 or greater
//require_once(ROOT_PATH . "/Facebook_v4.php" );

/**
 * Class FBManager
 */
class FBManager 
{
	/**
	 * @var Facebook
	 */
	private $facebook;
	
	/**
	 * Create new object FBManager Class.
	 */
	public function __construct()
	{
		$permission = unserialize(FB_PERMISSION);
	
		// PHP 5.3.3 	
		
		$this->facebook = new Facebook_v3( 
			FB_APP_ID, 
			FB_SECRET_KEY, 
			FB_LOGIN_URL_CALLBACK,
			FB_LOGOUT_URL_CALLBACK,
			$permission
		);
		
		
		// PHP 5.4 or greater
		/*
		$this->facebook = new Facebook( 
			FB_APP_ID, 
			FB_SECRET_KEY, 
			FB_LOGIN_URL_CALLBACK,
			FB_LOGOUT_URL_CALLBACK,
			$permission
		);
		*/
	}
	
	/**
	 * Prepare parameters for post wall.
	 *
	 * @access private 
	 * @param Array<string> 	$parameters 
	 * [
	 * 		"message" 	=> string (Required)
	 * 		"link" 		=> string (Optional)
	 * 		"source" 	=> string (optional)
	 * ]
	 */
	private function prepareParameters($paramters)
	{
		$newParameters = array();
		
		if ($paramters["message"] != NULL) $newParameters["message"] = $paramters["message"];
		else return NULL;
		
		if ($paramters["link"] != NULL ) 
			$newParameters["link"] = $paramters["link"];
			
		if ($paramters["source"] != NULL) 
		{
			$ext = pathinfo($paramters["source"], PATHINFO_EXTENSION);
			if ($ext == "png" || $ext == "jpg" || $ext == "gif" || $ext == "jpeg")
				$newParameters["source"] = new CURLFile($paramters["source"], "image/{$ext}");
		}
		return $newParameters;
	}
	
	/**
	 * Login to Facebook.
	 *
	 * @return array(result=>bool, description)
	 */
	public function Login() 
	{ 
		return $this->facebook->Login(); 
	}
	
	/**
	 * Logout to Facebook.
	 *
	 * @return array(result=>bool, description)	 
	 */
	public function Logout() 
	{ 
		session_destroy();
		return $this->facebook->Logout(); 
	}
	
	/**
	 * Logout to Facebook.
	 *
	 * @return array(result=>bool, description)	 
	 */
	public function getAccessToken() 
	{ 
		$token = $this->facebook->getAccessToken();
		if ($token !== NULL && $token !== "")
			return array("result"=>true, "accessToken"=>$token);
		else
			return array("result"=>false, "description"=>"Not initialize session, not logged in Facebook.");
	}
	
	/**
	 * Logout to Facebook.
	 *
	 * @return array(result=>bool, description)	 
	 */
	public function getVersionFacebookSDK()
	{
		return $this->facebook->getVersionSDK();	
	}
	
	/**
	 * Post wall to Facebook. This methods can post
	 * different away.
	 * - Only Message.
	 * - Message and, between photo or link.
	 * 
	 * @param string $message
	 * @param string $link (Optional)
	 * @param array<string> (Optional)
	 * @return array(result=>bool, description)
	 */
	public function postWall($message, $link = NULL, $src = NULL) 
	{ 
		if ($message == NULL || $message == "")
			return array("result"=>false, "description"=>"Missing paramenters.");
	
		$post_id = ( $src == NULL || $src["size"] == 0 ) ? '/me/feed' : '/me/photos';
		$ext = pathinfo( $src["name"], PATHINFO_EXTENSION );
		
		if ($src !== NULL && $src["size"] > 0 && ($ext == "png" || $ext == "jpg" || $ext == "gif" || $ext == "jpeg")) {
			$target_folder = dirname(__FILE__) . "/tmp/";
			$target_path = $target_folder . basename( $src["name"] ); 
			if (move_uploaded_file($src["tmp_name"], $target_path))
				$src = $target_path;
			else
				return array("result"=>false, "description"=>"Error upload file.");	
		} else {
			$src = NULL;
		}
		
		$parameters = $this->prepareParameters(
			array(	'message'=>$message, 
					'link'=>$link, 
					'source'=>$src)
		);
		
		return $this->facebook->postWall($post_id, $parameters);
	}
	
	/**
	 * This method check is Logged in Facebook.
	 *
	 * @return array(result=>bool, description)
	 */
	public function isLogged() 
	{ 
		return $this->facebook->getIsConnected(); 
	}
	
	/**
	 * This method a get user account Facebook.
	 *
	 * @return array(result=>bool, id, name, email, gender, link, birthday, photouser, description)
	 */
	public function getUser()
	{
		return $this->facebook->getUser();
	}
	
	/**
	 * This method a get friends list of user Facebook.
	 *
	 * @return array(result=>bool, array)
	 * array:
	 * [
	 *		[id, name, photo],
	 *		[id, name, photo],
	 *		...
	 * ]
	 */
	public function getFriends()
	{
		$list = array();
		$arg = $this->facebook->getFriends();
		
		if ($arg["result"]) 
		{
			if ($this->facebook->getVersionSDK() == "4.0.0")
			{
				$arr = $arg["friends"]["data"];
				foreach ($arr as $data) 
				{
					$friend = array("id"=>$data->id, "name"=>$data->name, "photo"=>$data->picture->data->url);
					$list[] = $friend;				
				}
				
				return array("result"=>$arg["result"], "list"=>$list);	
			}
			elseif ($this->facebook->getVersionSDK()  == "3.2.3")
			{
				$arr = $arg["friends"]["data"];
				foreach ($arr as $data) 
				{
					$friend = array("id"=>$data["id"], "name"=>$data["name"], "photo"=>$data["picture"]["data"]["url"]);
					$list[] = $friend;				
				}
				return array("result"=>$arg["result"], "list"=>$list);	
			}		
		} 
		else 
			return $arg;
	}	
}
?>