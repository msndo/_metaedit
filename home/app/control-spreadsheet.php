<?php
require_once('common/app/library/conf-common.php');
require_once('common/app/library/list-file.php');
require_once('common/app/library/handle-document.php');

class DataForListFile {
	public function getListFile() {
		$confCommon = new ConfCommon;
		$paramEnv = $confCommon -> getParamEnv();
		$defaultCharset = $confCommon -> defaultCharset;
		$basePath = str_replace(SITE_URL, '', $paramEnv['basePath']);

		$msgElemNotFound = $confCommon -> msgElemNotFound;

		$listFile = new ListFile;
		$listPathFile = $listFile -> getFileListSingleLevel($basePath);

		$counterRecordFile = 0;
		$lineNum = 1;
		$seriesListDocumentInfo = array();

		$listPathIgnore = $confCommon -> listPathIgnore;
		$regexCondScrape = $confCommon -> regexCondScrape;

		foreach($listPathFile as $pathFileSrc) {
			// 無視ファイルリスト分はスキップ
			foreach($confCommon -> listPathIgnore as $pathIgnore) {
				$regExPathIgnore = '/^' .  str_replace('/', '\/', SITE_ROOT . $pathIgnore) . '.*$/';
				if(preg_match($regExPathIgnore, $pathFileSrc)) { continue 2; }
			}

			$listDocumentInfo = array();

			$lineNum = $counterRecordFile + 1;

			$urlFile = SITE_URL . str_replace(SITE_ROOT, '', $pathFileSrc);

			$contentFile = '';

			// ディレクトリ・ファイル拡張子による無視判定
			if((! is_dir($pathFileSrc)) && ! preg_match('/\.(zip|gz|tar|lzh)$/', $pathFileSrc)) {
				$contentFile = file_get_contents($pathFileSrc);
			}
			else { continue; }

			// ファイル内容による無視判定
			$countNoMatch = 0;
			if(!empty($confCommon -> listContentAsTargetDoc)) {
				foreach($confCommon -> listContentAsTargetDoc as $contentAsTargetDoc) {
					if(! preg_match($contentAsTargetDoc, $contentFile)) { $countNoMatch ++; }
				}
				if($countNoMatch > 0) {
					continue;
				}
			}

			// ++++++++++++++++++++++++++++++++++++ 表示項目取得

			// 項目リスト
			$listDocumentInfo = array(
				'listContent' => array(),
				'listProperty' => array()
			);

			array_push(
				$listDocumentInfo['listContent'],
 				'<a href="' . $urlFile . '" target="_blank">' . str_replace(SITE_URL, '/', $urlFile)  . '</a>'
			);

			// スクレイピング実行
			foreach($regexCondScrape as $key => $value) {
				if(preg_match($regexCondScrape[$key]['condition'], $contentFile)) {
					array_push(
					$listDocumentInfo['listContent'],
						preg_replace($regexCondScrape[$key]['condition'], $regexCondScrape[$key]['fieldIndexPickup'], $contentFile)
					);

					array_push(
						$listDocumentInfo['listProperty'],
						array(
							'readOnly' => false
						)
					);
				}
				else {
					array_push(
						$listDocumentInfo['listContent'],
						$msgElemNotFound
					);

					array_push(
						$listDocumentInfo['listProperty'],
						array(
							'readOnly' => true
						)
					);

				}

				// 読み取り専用モード
				if(($confCommon -> spreadsheetReadonly) == true) {
					$listDocumentInfo['listProperty'][count($listDocumentInfo['listProperty']) -1]['readOnly'] = true;
				}
			}

			// UTF-8以外に対応
			$handleDocument = new HandleDocument;
			$encodingDocument = $handleDocument -> detectDocmentEncoding($contentFile);

			for($ix = 0; $ix < count($listDocumentInfo['listContent']); $ix ++) {
				if($encodingDocument != $defaultCharset) {
					$listDocumentInfo['listContent'][$ix] = mb_convert_encoding($listDocumentInfo['listContent'][$ix], $defaultCharset, $encodingDocument);
				}
			}

			if(PROHIBIT_HTMLENTITIES) {
				for($ixDocumentInfo = 0; $ixDocumentInfo < count($listDocumentInfo['listContent']); $ixDocumentInfo ++) {
					if($ixDocumentInfo === 0) { continue; }
					$listDocumentInfo['listContent'][$ixDocumentInfo] = html_entity_decode($listDocumentInfo['listContent'][$ixDocumentInfo], ENT_QUOTES, $defaultCharset);
				}
			}

			$seriesListDocumentInfo[$counterRecordFile] =  $listDocumentInfo;
			$counterRecordFile ++;
		}

		return $seriesListDocumentInfo;
	}
}

class ControlListFile {
	public function getSeries() {
		$confCommon = new ConfCommon;
		$msgRecordNotfound = $confCommon -> msgRecordNotfound;

		$dataForListFile = new DataForListFile;
		$seriesListDocumentInfo = $dataForListFile -> getListFile();

		if(! count($seriesListDocumentInfo)) {
			$listContent = &$seriesListDocumentInfo[0]['listContent'];
			$listContent = array();

			$listProperty = &$seriesListDocumentInfo[0]['listProperty'];
			$listProperty = array();

			$offsetColumn = 1;
			$countColumn = count($confCommon -> regexCondScrape);

			for($ixLoop = 0; $ixLoop < ($countColumn + $offsetColumn); $ixLoop ++) {
				array_push($listContent, $msgRecordNotfound);
			}
			for($ixLoop = 0; $ixLoop < $countColumn; $ixLoop ++) {
				array_push($listProperty, array('readOnly' => true));
			}
		}
		return($seriesListDocumentInfo);
	}
}
?>
