<?php
call_user_func(function() {
	$listContConf = parse_ini_file('conf/conf-common.ini', true);

	if(!empty($listContConf['Environment']['APP_ROOT'])) {
		define("APP_ROOT", $listContConf['Environment']['APP_ROOT']);
	}
	else {
		// windows
		$appRoot = preg_replace('/\\\/', '/', getcwd());
		$appRoot = preg_replace('/[a-z]:\//i', '/', $appRoot);

		define("APP_ROOT", $appRoot);
	}

	if(!empty($listContConf['Environment']['SITE_ROOT'])) {
		define("SITE_ROOT", $listContConf['Environment']['SITE_ROOT']);
	}
	else {
		define("SITE_ROOT", preg_replace('/\/[^\/]*?$/', '/', APP_ROOT));
	}

	if(!empty($listContConf['Environment']['SITE_URL'])) {
		define("SITE_URL", $listContConf['Environment']['SITE_URL']);
	}
	else {
		$namePathInstall = basename(APP_ROOT);
		define("SITE_URL", (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . preg_replace('/\/' . $namePathInstall . '(\/|.*)$/', '/', $_SERVER['REQUEST_URI']));
	}

	if(!empty($listContConf['Apprication-Control']['PROHIBIT_HTMLENTITIES'])) {
		define("PROHIBIT_HTMLENTITIES", $listContConf['Apprication-Control']['PROHIBIT_HTMLENTITIES']);
	}
	else {
		define("PROHIBIT_HTMLENTITIES", false);
	}
});

class ConfCommon {
	private $listContConf;

	private $keyBasePath;

	public $listPathIgnore;
	public $regexCondScrape;

	function __construct() {
		$this -> listContConf = parse_ini_file('conf/conf-common.ini', true);

		// Shortcut as Property
		$this -> listPathIgnore = $this -> listContConf['Apprication-Control']['listPathIgnore'];
		$this -> defaultCharset =  $this -> listContConf['Apprication-Control']['defaultCharset'];
		$this -> condScrapeCharset =  $this -> listContConf['Apprication-Control']['metaCharset']['regexCondScrape'];
		$this -> charsetDetectionOrder =  $this -> listContConf['Apprication-Control']['charsetDetectionOrder'];
		$this -> regexCondScrape = $this -> setRegexCondScrape();
		$this -> msgRecordNotfound = $this -> listContConf['Apprication-Control']['msgRecordNotfound'];
		$this -> msgElemNotFound = $this -> listContConf['Apprication-Control']['msgElemNotFound'];
		$this -> spreadsheetReadonly = $this -> listContConf['Apprication-Control']['spreadsheetReadonly'];
		$this -> listContentAsTargetDoc = $this -> listContConf['Apprication-Control']['listContentAsTargetDoc'];

		$this -> keyBasePath = 'basePath';
		$this -> setParamEnv();
	}

	public function getParamEnv() {
		return $this -> listParamEnv;
	}

	public function setParamEnv() {
		$keyBasePath = $this -> keyBasePath ;
		$this -> listParamEnv[$keyBasePath] = SITE_ROOT;

		// アクセスパラメータでオーバーライド
		if(! empty($_POST[$keyBasePath])) {
			$this -> listParamEnv[$keyBasePath] = SITE_ROOT . $_POST[$keyBasePath];
		}
		else if(! empty($_POST[$keyBasePath])) {
			$this -> listParamEnv[$keyBasePath] = SITE_ROOT . $_GET[$keyBasePath];
		}

		// セキュリティ対応
		if(! empty($this -> listParamEnv[$keyBasePath])) {
			$this -> listParamEnv[$keyBasePath] = (! preg_match('/\.\./', $this -> listParamEnv[$keyBasePath])) ? $this -> listParamEnv[$keyBasePath] : SITE_ROOT;
			$this -> listParamEnv[$keyBasePath] = htmlspecialchars($this -> listParamEnv[$keyBasePath], ENT_QUOTES, 'UTF-8');
		}
	}

	private function getListNameItemEdit() {
		$result = array();
		foreach($this -> listContConf['Item-Edit'] as $nameItemEdit => $listContItemEdit) {

		}
	}

	private function setRegexCondScrape() {
		$result = array();
		foreach($this -> listContConf['Item-Edit'] as $nameItemEdit => $listContItemEdit) {

			$valRegexCond = $listContItemEdit['regexCondScrape'];
			$result[$nameItemEdit]['condition'] = $valRegexCond;
			$result[$nameItemEdit]['fieldIndexPickup'] = $listContItemEdit['fieldIndexPickup'];
			$result[$nameItemEdit]['columnLabel'] = $listContItemEdit['columnLabel'];
		}

		return($result);
	}
}
?>
