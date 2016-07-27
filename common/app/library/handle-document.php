<?php
require_once('common/app/library/conf-common.php');

class HandleDocument {
	function detectDocmentEncoding($strSrc) {
		$confCommon = new ConfCommon;

		$encodingOfStr = $confCommon -> defaultCharset;
		if(preg_match($confCommon -> condScrapeCharset, $strSrc)) {
			$encodingOfStr =  preg_replace($confCommon -> condScrapeCharset, '${2}', $strSrc);
		}
		else {
			$encodingOfStr = mb_detect_encoding($strSrc, $confCommon -> charsetDetectionOrder);
		}
		if(empty($encodingOfStr)) { $encodingOfStr = $confCommon -> defaultCharset; }

		// error_log('[' . $encodingOfStr . ']');

		return($encodingOfStr);
	}
}
?>
