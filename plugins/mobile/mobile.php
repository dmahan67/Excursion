<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=global
[END_PLUGIN]
==================== */

class Mobile_Detect {
    
    protected $detectionRules;
    protected $userAgent = null;
    protected $accept = null;

    protected $isMobile = false;
    protected $isTablet = false;
    protected $phoneDeviceName = null;
    protected $tabletDevicename = null;
    protected $operatingSystemName = null;
    protected $userAgentName = null;

    protected $phoneDevices = array(     
            'iPhone' => '(iPhone.*Mobile|iPod|iTunes)',            
            'BlackBerry' => 'BlackBerry|rim[0-9]+',
            'HTC' => 'HTC|Desire',
            'Nexus' => 'Nexus One|Nexus S',
            'DellStreak' => 'Dell Streak',
            'Motorola' => '\bDroid\b.*Build|HRI39|MOT\-',
            'Samsung' => 'Samsung|GT\-P1000|SGH\-T959D|GT\-I9100|GT\-I9000',
            'Sony' => 'E10i',
            'Asus' => 'Asus.*Galaxy',
            'Palm' => 'PalmSource|Palm',
            'GenericPhone' => '(mmp|pocket|psp|symbian|Smartphone|smartfon|treo|up.browser|up.link|vodafone|wap|nokia|Series40|Series60|S60|SonyEricsson|N900|\bPPC\b|MAUI.*WAP.*Browser|LG\-P500)'
    );

    protected $tabletDevices = array(
        'BlackBerryTablet' => 'PlayBook|RIM Tablet',
        'iPad' => 'iPad.*Mobile',
        'Kindle' => 'Kindle|Silk.*Accelerated',
        'SamsungTablet' => 'SCH\-I800|GT\-P1000|Galaxy.*Tab',
        'MotorolaTablet' => 'xoom|sholest',
        'AsusTablet' => 'Transformer|TF101',
        'GenericTablet' => 'Tablet|ViewPad7|LG\-V909|MID7015|BNTV250A|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b',
    );

    protected $operatingSystems = array(
        'AndroidOS' => '(android.*mobile|android(?!.*mobile))',
        'BlackBerryOS' => '(blackberry|rim tablet os)',
        'PalmOS' => '(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)',
        'SymbianOS' => 'Symbian|SymbOS|Series60|Series40|\bS60\b',
        'WindowsMobileOS' => 'IEMobile|Windows Phone|Windows CE.*(PPC|Smartphone)|MSIEMobile|Window Mobile|XBLWP7',
        'iOS' => '(iphone|ipod|ipad)',
        'FlashLiteOS' => '',
        'JavaOS' => '',
        'NokiaOS' => '',
        'webOS' => '',
        'badaOS' => '\bBada\b',
        'BREWOS' => '',
    );

    protected $userAgents = array(      
      'Chrome' => '\bCrMo\b',
      'Dolfin' => '\bDolfin\b',
      'Opera' => '(Opera.*Mini|Opera.*Mobi)',  
      'Skyfire' => 'skyfire',      
      'IE' => 'ie*mobile',
      'Firefox' => 'fennec|firefox.*maemo',
      'Bolt' => 'bolt',
      'TeaShark' => 'teashark',
      'Blazer' => 'Blazer',
      'Safari' => 'Mobile*Safari',
      'Midori' => 'midori',
      'GenericBrowser' => 'NokiaBrowser|OviBrowser'
    );
    
    function __construct(){
        
        $this->detectionRules = array_merge(
                                            $this->phoneDevices, 
                                            $this->tabletDevices, 
                                            $this->operatingSystems, 
                                            $this->userAgents
                                            );
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->accept = $_SERVER['HTTP_ACCEPT'];  
        
        if (
                isset($_SERVER['HTTP_X_WAP_PROFILE']) ||
                isset($_SERVER['HTTP_X_WAP_CLIENTID']) ||
                isset($_SERVER['HTTP_WAP_CONNECTION']) ||
                isset($_SERVER['HTTP_PROFILE']) ||
                isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ||
                isset($_SERVER['HTTP_X_NOKIA_IPADDRESS']) ||
                isset($_SERVER['HTTP_X_NOKIA_GATEWAY_ID']) ||
                isset($_SERVER['HTTP_X_ORANGE_ID']) ||
                isset($_SERVER['HTTP_X_VODAFONE_3GPDPCONTEXT']) ||
                isset($_SERVER['HTTP_X_HUAWEI_USERID']) ||
                isset($_SERVER['HTTP_UA_OS']) ||
                (isset($_SERVER['HTTP_UA_CPU']) && $_SERVER['HTTP_UA_CPU'] == 'ARM') 
                ) {
                $this->isMobile = true;
        } elseif (!empty($this->accept) && (strpos($this->accept, 'text/vnd.wap.wml') !== false || strpos($this->accept, 'application/vnd.wap.xhtml+xml') !== false)) {
                $this->isMobile = true;
        } else {
                $this->_detect();
        }        
        
    }
        
    public function getRules()
    {
        return $this->detectionRules;
    }
    
    public function __call($name, $arguments)
    {
                
        $key = substr($name, 2);
        return $this->_detect($key);
        
    }
        
    private function _detect($key='')
    {

        if(empty($key)){ 

            foreach($this->detectionRules as $_key => $_regex){
                if(empty($_regex)){ continue; }
                if(preg_match('/'.$_regex.'/is', $this->userAgent)){
                    $this->isMobile = true;
                    return true;
                } 
            }
            return false;

        } else {
            
            $key = strtolower($key);
            $_rules = array_change_key_case($this->detectionRules);
            
            if(array_key_exists($key, $_rules)){
                if(empty($_rules[$key])){ return null; }
                if(preg_match('/'.$_rules[$key].'/is', $this->userAgent)){
                    $this->isMobile = true;
                    return true;
                } else {
                    return false;
                }           
            } else {
                trigger_error("Method $key is not defined", E_USER_WARNING);
            }
            
            return false;

        }

    }
        
    public function isMobile()
    {
            return $this->isMobile;
    } 
    
    public function isTablet()
    {
        
        foreach($this->tabletDevices as $_key => $_regex){
            if(preg_match('/'.$_regex.'/is', $this->userAgent)){
                $this->isTablet = true;
                return true;
            }
        }
        
        return false;        
            
    }
    
}

$detect = new Mobile_Detect();

if ($detect->isMobile()) {
    $user['theme'] = 'mobile';
}

?>