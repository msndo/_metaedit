<?php
require_once('common/app/library/conf-common.php');
require_once('common/app/library/list-file.php');

$tmplHtmlListTree = '<ul>__elemsListTree__</ul>';
$tmplHtmlElemListTree = '<li class="__typeFile__"><a class="cont-elem-list" href="__urlFile__" target="_blank">__relativePathFile__</a></li>' . "\n";

$listFile = new ListFile;

$confCommon = new ConfCommon;

$paramEnv = $confCommon -> getParamEnv();
$basePath = realpath(str_replace(SITE_URL, '', $paramEnv['basePath']));

// Avoid Directory Traversal
if(!$basePath || !($listFile -> isPathUnderDocRoot($basePath, SITE_ROOT))) {
	error_log($basePath . ': Out of DOCUMENT_ROOT. Operation Terminated');
	return;
}

$listTreeFile = $listFile -> getFileListSingleLevel($basePath);
$elemsHtmlListTree = '';


foreach($listTreeFile as $elemTreeFile) {
	// 無視ファイルリスト分はスキップ
	foreach($confCommon -> listPathIgnore as $pathIgnore) {
		$regExPathIgnore = '/^' .  str_replace('/', '\/', SITE_ROOT . $pathIgnore) . '.*$/';
		if(preg_match($regExPathIgnore, $elemTreeFile)) { continue 2; }
	}

	$htmlElemListTree = $tmplHtmlElemListTree;

	$urlElemTreeFile = str_replace(SITE_ROOT, SITE_URL, $elemTreeFile);
	$relativePathElemTreeFile = basename(str_replace(SITE_ROOT, '', $elemTreeFile));

	if(! preg_match('/\/$/', $urlElemTreeFile)) { $urlElemTreeFile = $urlElemTreeFile . '/'; }

	$typeFile = 'type-file';
	if(is_dir($elemTreeFile)) {
		$typeFile = 'type-dir';
	}
	else { continue; }

	$htmlElemListTree = str_replace('__urlFile__', $urlElemTreeFile, $htmlElemListTree);
	$htmlElemListTree = str_replace('__typeFile__', $typeFile, $htmlElemListTree);
	$htmlElemListTree = str_replace('__relativePathFile__', $relativePathElemTreeFile, $htmlElemListTree);

	$elemsHtmlListTree = $elemsHtmlListTree . $htmlElemListTree;
}

$htmlListTree = '';

if(!empty($elemsHtmlListTree)) {
	$htmlListTree = str_replace('__elemsListTree__', $elemsHtmlListTree, $tmplHtmlListTree) . "\n";
}

echo $htmlListTree;
?>
