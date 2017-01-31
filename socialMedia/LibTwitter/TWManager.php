<?php
error_reporting(0);

define("ROOT_PATH_TWITTER", dirname(__FILE__));
require_once(ROOT_PATH_TWITTER . "/TWConstant.php");
require_once(ROOT_PATH_TWITTER . "/Twitter.php" );

/**
 * Class TWManager
 */
class TWManager 
{
	/**
	 * @var Twitter
	 */
	private $twitter;
	
	/**
	 * Create new object Twitter Class.
	 */
	public function __construct()
	{
		$this->twitter = new Twitter( TW_CONSUMER_KEY, TW_CONSUMER_SECRET );
	}
	
	/**
	 * Login to Twitter.
	 *
	 */
	public function Login() 
	{ 
		$this->twitter->Login(); 
	}
	
	/**
	 * Logout to Facebook.
	 *
	 */
	public function Logout()
	{
		$this->twitter->Logout();
	}
	
	/**
	 * This method check is Logged in Facebook.
	 *
	 * @return array(result=>bool, description)
	 */
	public function isLogged()
	{
		return $this->twitter->getIsConnected();
	}
	
	/**
	 * This method a get user account Facebook.
	 *
	 * @return array( result=>bool, id, name, username, location, profile_location, description, url, photouser )
	 */
	public function getUser()
	{
		return $this->twitter->getUser();
	}
	
	/**
	 * Post wall to twitter.
	 * - Only Message.
	 * - Message and photo.
	 *
	 * Twitter Error Codes & Responses references 
	 * - https://dev.twitter.com/overview/api/response-codes
	 * 
	 * @param string $message
	 * @param array<string> $src (Optional)
	 * @return array(result=>bool, description)
	 */
	public function postWall($message, $src = NULL)
	{
		$parameters = array('message'=>$message);
	
		if ($message == NULL || $message == "")
			return array("result"=>false, "description"=>"Missing paramenters.");
	
		if ($src !== NULL) 
		{
			$ext = pathinfo( $src["name"], PATHINFO_EXTENSION );
		
			if ($src !== NULL && $src["size"] > 0 && ($ext == "png" || $ext == "jpg" || $ext == "gif" || $ext == "jpeg")) 
			{
				$target_folder = dirname(__FILE__) . "/tmp/";
				$target_path = $target_folder . md5(basename( $src["name"] )); 
				if (move_uploaded_file($src["tmp_name"], $target_path))
					$parameters["src"] = $src = $target_path;
				else
					return array("result"=>false, "description"=>"Error upload file.");	
			} 
			else 
				$src = NULL;
		}
		
		if ($src !== NULL)
			return $this->twitter->postWallWithPhoto($parameters);
		else 
			return $this->twitter->postWall($parameters);
	}
}

?>