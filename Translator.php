<?php
/*  Class for filling cache and retrieveng translations from DB
*   or cache file. Supports 'no changes' answer from DB
* 
*   Uses variables:
*   $config['MsnCacheExpiration']   // Expiration time in minutes
*   $config['MsnCachePath'] // Path to cache files
*   $config['MsnDefaultLang']   // Default language in ISO3 format
*   $skin['languageIso3']   // ISO3 code of skin's language
*   $skin['networkID']    // Skin's Network ID
*   $network[Network]['networkID']  // Skin's Network ID on Secure Server
*
*   How to use:
*   $trans = Translator::getInstance() // To get an instance of class
*   echo $trans->getTranslation('Player Class', 'Class Name', 4) // To get Translation for component's key's ID (id is optional)
*   FALSE is returned if translation was not found
*/
class Translator
{
    protected static $_instance;// Instance of a class
    private $translations;      // Array of translation data
	private $translationsDefault; //Array of default translation data
    private $expiry;            // Expiration time in minutes
    private $network;           // Network ID
    private $cachePath;         // Path to cache files
    private $language;          // ISO3 code of skin's language
    private $application;       // Operation Source Application ID (11 - Web, 12 - Secure)
    private $msnEnabled;         // Is MSN Project supported on current Network

    private $webAppId = 11;       // Application ID for Web Server
    private $secureAppId = 12;    // Application ID for Decure Server

    private $cacheAbsent = 404;    // Cache file not found
    private $cacheExpired = 408;   // Cach file is expired
    private $noChanges = 304;      // No new changes in DB for requested translations
	
	private $translationsFromDB= array();		//Array of translation data taken from DB
	private $defaultFomDB= array();				//Array of default data taken from DB
	private $DBtranslationsLoaded=14; //Translations already loaded from DB
	private $defaultLanguageId='default'; //Language Id to get default translations from DB
	private $loadDbTranslCounter=0;  //Counter of calls load translations from DB
	private $loadDbResult;			//Result of loading translations from DB - translations already loaded/ no changes in DB
	private $TranslatorId=0;

    // Set all variables (including Translations)
	private function __construct() {
		global $CONFIG;
		$this->TranslatorId=uniqid("");
		$this->application = $CONFIG["ThisApplicationID"];
        $this->network = $this->skinGetNetwork();
        $this->language = $this->skinGetLanguage();
        $this->expiry = $this->configGetExpiration();
        $this->cachePath = $this->configGetCachePath();
        $this->msnEnabled = $this->networkGetMsn();
        $this->translations = $this->loadTranslations(false);
		$this->translationsDefault = $this->loadTranslations(true);
		//echo "Translator created!";
    }

    // Prevent from cloning
    private function  __clone() { }

    // Returns an instance of class and creates one if needed
	public static function getInstance() {
        if ( self::$_instance == null ) {
			self::$_instance = new self();
        }
        return( self::$_instance );
    }

    private function networkGetMsn() {
        global $skin;
        global $network;
        if (isset($network[strtolower($skin['network'])]['msn']))
                return $network[strtolower($skin['network'])]['msn'];
        return FALSE;
    }
    
    private function configGetExpiration() {
        global $config;
        return $config['MsnCacheExpiration'];
    }

    private function configGetCachePath() {
        global $config;
        return $config['MsnCachePath'];
    }

    private function skinGetNetwork() {
        global $skin;
        if ( isset($skin['networkID']) ) {            
            $nId = $skin['networkID'];
        } else {
            global $network;
            $nId = $network[strtolower($skin['network'])]['networkID'];            
            if (!isset($nId)) {                
                return 0;
            }
        }
        return $nId;
    }

    private function skinGetLanguage() {
        global $skin;

        if (isset($skin['languageIso3'])) {
            return $skin['languageIso3'];
        } else {
            global $config;
            return $config['MsnDefaultLang'];
        }
    }

    // Retrieve an item from translations array
    public function getTranslation($component, $key, $id = FALSE) {
        if ($id === FALSE) {
            if (isset($this->translations[$component][$key])) {
				return $this->translations[$component][$key];
            }
			elseif(isset($this->translationsDefault[$component][$key])){
				return $this->translationsDefault[$component][$key];
			}
        } else {
            if (isset($this->translations[$component][$key][$id])) {
				return $this->translations[$component][$key][$id];
            }
			elseif(isset($this->translationsDefault[$component][$key][$id])){
				return $this->translationsDefault[$component][$key][$id];
			}
        }
        return FALSE;
    }

    // Handle Loading of translations from file or DB
    private function loadTranslations($loadDefaultTranslations) {
        if (!$this->msnEnabled) return NULL;
		$file = $this->getCacheFile($loadDefaultTranslations? true:false);
        $cache = $this->loadTranslationsFile($file);
		$languageCodeList=$this->language.','.$this->defaultLanguageId;
        if ( $cache == $this->cacheAbsent ) {
			$cache = $this->loadTranslationsDB(0,$languageCodeList);
			if($cache==$this->DBtranslationsLoaded){
				$cache=$loadDefaultTranslations? $this->defaultFomDB:$this->translationsFromDB;		
			}
			if (is_array($cache)) {
                $this->saveTranslationsFile($file, $cache);
            }
        } elseif ( $cache == $this->cacheExpired ) {
			$cache = $this->loadTranslationsDB($this->getIntervalSinceLastModifiedInMinutes($file),$languageCodeList);
			if($cache==$this->DBtranslationsLoaded){
				$cache=$loadDefaultTranslations? $this->defaultFomDB:$this->translationsFromDB;		
			}
			if ($cache == $this->noChanges) {
				$this->updateCacheDates($file);
                $cache = $this->loadTranslationsFile($file);
            }
			 else {
                if (is_array($cache)) {
					$this->saveTranslationsFile($file, $cache);
                }
            }
        }
        return $cache;
    }
	

    // Returns Error codes:
    // 404 - file not found
    // 408 - cache outdated
    private function loadTranslationsFile($filename) {
        clearstatcache(); // for is_readable and filemtime
		if (!is_readable($filename)) {
			return $this->cacheAbsent;}
        $lastUpdate = $this->getCacheDates($filename);
		$nowTime=time();
		if ($lastUpdate < $nowTime - $this->expiry * 60) {
			return $this->cacheExpired;}  
        $data = include($filename);
		if (!is_array($data)) {
			return $this->cacheAbsent;
		}
        return $data;
    }

    // Returns Error codes:
    // 304 - no new changes
    private function loadTranslationsDB($expiry,$languageCodeList) {
		if (dbInitCommon()) {
    		$aParams = array(
                'NetworkIDList' => array($this->network, 'varchar', 1000),
                'OperationSourceApplicationID' => array($this->application, 'int', 0),
				'LanguageCodeList' => array($languageCodeList, 'varchar', 1000),
					'Interval' => array($expiry, 'int', 0)
            );
			$r = NULL;
			if($this->loadDbTranslCounter==0){
				$r = dbExecSprocSimple('wGetTranslations',$aParams); //or die('SQL error: ' . print_r(sqlsrv_errors(),true));
			}
			$this->loadDbTranslCounter++;
			if($this->loadDbTranslCounter==2){
				$this->loadDbTranslCounter==0;
				return $this->loadDbResult;
			}
            
			if ( is_array($r) and $r[0][0] !== NULL ) {
			
				if($this->fillDBTranslationArrays($r)>0){
					$this->loadDbResult = $this->DBtranslationsLoaded;
				}else{
					$this->loadDbResult = $this->noChanges;
				}
				
				//return $r;
    		} else {
				$this->loadDbResult = $this->noChanges;
            }
			return $this->loadDbResult;
    	}
        return FALSE;
    }

	private function fillDBTranslationArrays(array $translations){
		$count=0;
		foreach (  $translations[0] as $translation) {
			if (is_array($translation)) {
				$component = $translation['TranslationComponentTypeID'];
				$key = $translation['TranslationKeyID'];
				$id = $translation['ComponentID'];
				if($translation['LanguageID']==$this->defaultLanguageId){
					$this->defaultFomDB[$component][$key][$id] = $translation['Value'];
				}
				else{
					$this->translationsFromDB[$component][$key][$id] = $translation['Value'];
				}
				$count++;
			}
		}
		return	$count;
	}
    
    // Generates Cache file name with full path
    private function getCacheFile($default) {
		$fileName = $default?"default" : $this->language;
		return "{$this->cachePath}/{$this->application}-{$this->network}_{$fileName}.php";
    }

    // Updates Last Modification date of file to NOW
    private function updateCacheDates($filename) {
		$retVal=touch($filename);
		return $retVal;
    }

    // Returns Last Modification date of file
    private function getCacheDates($filename) {
		$retVal=filemtime($filename);
		return $retVal;
    }
	
	//Returns the time in minutes since the cache file was last modified
	private function getIntervalSinceLastModifiedInMinutes($filename){
		$retVal=(int)((time() - $this->getCacheDates($filename))/60);
		return $retVal; 
	}
	

    // Uses generateArrayCode to get PHP code and stores it in file
    private function saveTranslationsFile($file, $data) {
        if (is_array($data)) {
            $output = "<?php\n" . 'return ' .
                    $this->generateArrayCode($data, 0) . ";\n?>";
            file_put_contents($file, $output);
        }
    }

    // Generates PHP code like array('one' => array(...) ...)
    private function generateArrayCode(array $array, $shift) {
        $outShift = str_repeat (' ', 4 * $shift );
        $output = "array(\n";
        $count = count($array);
        $i = 0;
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $output .= $outShift . $key . ' => ';
            } else {
                $output .= "{$outShift}'{$key}' => ";
            }
            if (is_array($value)) {
                $output .= $this->generateArrayCode($value, ++$shift);
            } elseif (is_numeric($value)) {
                $output .= $value;
            } else {
                $value = str_replace('\\', '\\\\', $value);
                $value = str_replace("'", "\'", $value);
                $output .= "'{$value}'";
            }
            ++$i;
            if ( $i < $count ) $output .= ',';
            $output .= "\n";
        }
		$output .= $outShift . ')';
		return $output;
	}
}

?>
