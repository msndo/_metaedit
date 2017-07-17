<?php
require_once('common/app/library/conf-common.php');
require_once('common/app/library/list-file.php');
require_once('common/app/library/handle-document.php');

if((! isset($_SERVER['HTTP_X_REQUESTED_WITH'])) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) {
	error_log('[' . $_SERVER['SCRIPT_NAME'] . ']: ' . 'Non-Ajax Access. Operation Stopeed');
	exit(1);
}

$confCommon = new ConfCommon;
$listFile = new ListFile;

if($confCommon -> spreadsheetReadonly == true) {
	exit(1);
}
$defaultCharset = $confCommon -> defaultCharset;

$strRcv = file_get_contents("php://input");
//error_log($strRcv);

$listFileTarget = json_decode($strRcv, true);
//error_log(json_encode($listFileTarget, 256));

$listResult = array();

foreach($listFileTarget as $pathTarget => $infoFileTarget) {
	$listEdit = $infoFileTarget['listEdit'];

	// OS Path to Target File
	$filenameTarget = realpath(SITE_ROOT . $pathTarget);

	// Check Existance of target file
	if(!file_exists($filenameTarget)) {
		error_log($filenameTarget . ': Does not exist. Skip');
		$listResult[$filenameTarget]['status'] = 'error';
		continue;
	}

	// Avoid Directory Traversal
	if(!$filenameTarget || !($listFile -> isPathUnderDocRoot($filenameTarget, SITE_ROOT))) {
		error_log($filenameTarget . ': Out of DOCUMENT_ROOT. Operation Terminated');
		$listResult[$filenameTarget]['status'] = 'error';
		exit();
	}

	// Hold Status of Process
	$listResult[$filenameTarget] = array(
		'ixRow' => $infoFileTarget['ixRow'],
		'status' => 'success'
	);

	//error_log(json_encode($listEdit, 256));

	$contentTarget = file_get_contents($filenameTarget);

	// UTF-8以外に対応
	$handleDocument = new HandleDocument;
	$encodingDocument = $handleDocument -> detectDocmentEncoding($contentTarget);

	foreach($listEdit as $keyInfo => $valueInfo) {
		$regexCondScrape = $confCommon -> regexCondScrape[$keyInfo]['condition'];
		$ixFieldPickup = preg_replace('/^.*?([0-9]+?).*?$/', '${1}', $confCommon -> regexCondScrape[$keyInfo]['fieldIndexPickup']);

		$contentTarget = preg_replace_callback(
			$regexCondScrape,
			function($matches) use ($ixFieldPickup) {
				$result = '';
				$ix = 0;
				foreach($matches as $match) {
					if($ix == 0) { $ix ++; continue; }
					else if($ix == $ixFieldPickup) {
						$result = $result . '__REP__';
					}
					else {
						$result = $result .  $match;
					}

					$ix ++;
				}

				return $result;
			},
			$contentTarget
		);

		$valueInfo = mb_convert_encoding($valueInfo, $encodingDocument, $defaultCharset);

		if(PROHIBIT_HTMLENTITIES) {
			$valueInfo = htmlspecialchars($valueInfo, ENT_QUOTES, $encodingDocument);
		}
		$contentTarget = str_replace('__REP__', $valueInfo, $contentTarget);
	}

	$listResult[$filenameTarget]['status'] = updateTargetFile($filenameTarget, $contentTarget);
}

// レスポンス標準出力
echo(json_encode($listResult, 256));

function updateTargetFile($filenameTarget, $contentTarget) {
	$tmpDir = 'app-tmp/';
	$prefixTempFile = '__editresult-';

	// ++++++++++++++++++++++++++++++++++++++++++ 更新結果を保存
	if(! file_exists($tmpDir)) { mkdir($tmpDir); }

	$filenameTemp = tempnam($tmpDir, $prefixTempFile);

	$msgResult = 'error';

	if(! file_put_contents($filenameTemp, $contentTarget)) { return $msgResult; };
	if(! rename($filenameTemp, $filenameTarget)) { return $msgResult; };
	if(! chmod($filenameTarget, 0664)) { return $msgResult; };

	$msgResult = 'success';
	return $msgResult;
}
?>
