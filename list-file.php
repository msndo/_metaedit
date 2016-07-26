<?php
require_once('common/app/library/list-file.php');
require_once('home/app/control-spreadsheet.php');

if((! isset($_SERVER['HTTP_X_REQUESTED_WITH'])) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) {
	// error_log('[' . $_SERVER['SCRIPT_NAME'] . ']: ' . 'Non-Ajax Access. Operation Stopeed');
	exit(1);
}

$controlListFile = new ControlListFile;

// 表示データ本体 + セル設定取得
$seriesListDocumentInfo = $controlListFile -> getSeries();

// レスポンス出力
header('Content-type: application/json; charset=UTF-8');
echo(json_encode($seriesListDocumentInfo, 256));
?>
