<?php
class MobileLoader 
{
	public $preferedView = null;
	public $deviceScreenSize = null;
	public $deviceTypeID = null;
	public $userAgent = null;

	const MOBILE_REG_EXP = '/iphone|ipod|ipad|android|mobile/';
	
	public function __construct()
	{
		$this->preferedView = cookie('preferedView');
		$this->deviceScreenSize = cookie('deviceScreenSize');
		$this->deviceTypeID = cookie('DeviceTypeID');
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
	}
	
	public function isMobileDevice()
	{	
		if($this->deviceTypeID) {
			return $this->deviceTypeID != DeviceType::Computer;
		}
		
		return preg_match(self::MOBILE_REG_EXP, strtolower($this->userAgent));
		
	}
	
	public function isDesktop()
	{
	
		return !$this->isMobileDevice();	
		
	}
	
	public function getPreferableView()
	{
		if($this->preferedView)
			return $this->preferedView;

		if($this->deviceTypeID)
			return $this->deviceTypeID;
		
		return DeviceType::Computer;		
		
	}
	
	public function getMobileUrl($keepGET = true, $addPeelingParams = true )
	{
		if(!getSkinProperty('MobileURL')) {
			return null;
		}
		
		$url = getServerProtocol() . getSkinProperty('MobileURL');

		if ($_SERVER['QUERY_STRING'] && $keepGET) {
			$url .= '?' . $_SERVER['QUERY_STRING'];
		}
		
		if ($addPeelingParams) {
			$url = $this->addPeelingParamsFromCookies($url);
		}
				
		return $url;		
		
	}
	
	public function isLandingPage()
	{	
		
		if ( !getSkinProperty('MobileLP') ) return false;
		
		return stripos($_SERVER['REQUEST_URI'], getSkinProperty('MobileLP')) !== false;	
		
	}
	
	public function addPeelingParamsFromCookies($url)
	{
		
		if($this->getPeelingParamsFromCookies()) {
			$separator = (parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&';
			$url .= $separator . $this->getPeelingParamsFromCookies();
		}
		
		return $url;
	}	
	
	public function getPeelingParamsFromCookies()
	{
	
		if(!cookie('queryParams')) {
			return false;
		}

		$queryParams = urldecode(cookie('queryParams'));
		$queryParams = explode('__', $queryParams);
		$peelingParams = array();
		
		foreach($queryParams as $param) {
			$param = explode('**', $param);
			if (isset($param[0], $param[1])){
				$peelingParams[$param[0]] = $param[1];
			}
		}
		
		if ($peelingParams)
			return http_build_query($peelingParams);
		else 
			return false;
			
	}
	
	public function isRedirectAllowed($deviceTypeID)
	{
		if($deviceTypeID == DeviceType::Smartphone) {
			return $this->isRedirectEnabledForPhone();
		}	
		
		if($deviceTypeID == DeviceType::Tablet) {
			return $this->isRedirectEnabledForTablet();
		}	
	
		/* For desktop redirections will be disallowed anyway	*/	
		return false;	
		
	}
	
	public function isRedirectEnabledForTablet()
	{
		$cashFileName = 'isRedirectEnabledForTablet.xml';
		$isPerSkin = false;
		$cashValidTime = 60 * 60 * 24;
		$status;

		if (ShouldTakeFromCashing($cashFileName, $cashValidTime, $isPerSkin)) {
		   LoadValuesFromCashingFile($status, $cashFileName, $isPerSkin);
		} else {
		   $status = isFeatureEnabled(FeatureID::RedirectEnabledForTablet);
		   CashTheArray($cashFileName, $status, $isPerSkin);
		}

		return $status;
	}

	private function isRedirectEnabledForPhone()
	{
		$cashFileName = 'isRedirectEnabledForPhone.xml';
		$isPerSkin = false;
		$cashValidTime = 60 * 60 * 24;
		$status;

		if (ShouldTakeFromCashing($cashFileName, $cashValidTime, $isPerSkin)) {
		   LoadValuesFromCashingFile($status, $cashFileName, $isPerSkin);
		} else {
		   $status = isFeatureEnabled(FeatureID::RedirectEnabledForPhone);
		   CashTheArray($cashFileName, $status, $isPerSkin);
		}

		return $status;
	}
	
	/* Make unconditional redirect to the mobile URL*/
	public function mobileRedirect()
	{	
		$mobileUrl = $this->getMobileUrl();
		
		if($mobileUrl) {
			header('Location: ' . $mobileUrl) ;
			die() ;
		} else {
			return false;
		}
	}
	

	public function landingPageRedirect()
	{
		$landingPageURL = getSkinProperty('MobileLP');

		if(!$landingPageURL) {
			$this->mobileRedirect();
		}
		
		$landingPageURL = getServerProtocol() . $_SERVER['HTTP_HOST'] . '/skin/' . $landingPageURL;
		$returnUrl = getServerProtocol() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$landingPageURL .= '?ru=' . urlencode($returnUrl) ;
		$landingPageURL .= '&mobile_url=' . urlencode($this->addPeelingParamsFromCookies($this->getMobileUrl())) ;

		header('Location: ' . $landingPageURL) ;
		die() ;
	}
	
	public function getDeviceType($deviceScreenSize = null)
	{ 	
		if(!$deviceScreenSize) {
			return DeviceType::Computer;
		}
		
		$wsController = new wsController();
		$networkID = getSkinProperty('networkID');
		$skinID = getSkinProperty('id');
		$userAgent = $this->userAgent;
		
		$result = $wsController->wsGetDeviceConfigurationRequest($networkID, $skinID, (int) $deviceScreenSize, $userAgent);

		if (!is_object($result) && $result == -1) {
			SetErrorMessage($UI["Unknown registration result"]);
			$skin["status"] = RegistrationStatus::UnknownRegistrationStatus;
			return DeviceType::Computer;
		}

		if (is_null($result)) {
			SetErrorMessage($UI["General Error contact support"]);
			return DeviceType::Computer;
		}

		if(isset($result->DeviceScreenSize)) {
			setcookie("DeviceTypeID", (int)$result->DeviceTypeID);
			return $result->DeviceTypeID;
		}
		
		return DeviceType::Computer;
	}
	
	private function getRedirectUrl($returnUrl)
	{
		$skinID = getSkinProperty('id');
		switch($skinID)
		{
			case 901:
			case 12001:
			case 13001:
			case 13003:
			case 21001:
			case 22001:
			case 28001:
			case 31001:
			case 31002:
				$url = getServerProtocol() . $_SERVER['HTTP_HOST'] . '/common/members/devicesizecalculator.php?ru=' . urlencode($returnUrl);
				break;
			default:
				$url = getServerProtocol() . $_SERVER['HTTP_HOST'] . '/members/devicesizecalculator.php?ru=' . urlencode($returnUrl);
				break;
				
		}
		error_log ('$returnUrl = '. $returnUrl);
		error_log ('$url = '. $url);
		return $url;
	}
	
	public function getDeviceScreenSize()
	{
		error_log ('getDeviceScreenSize start');
		$returnUrl = getServerProtocol() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
		$url = $this->getRedirectUrl($returnUrl);
		header('Location: ' . $url) ;
		die() ;
	}
	
}