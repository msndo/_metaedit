<?php
class ListFile {

	// 配下全階層にあるファイルの入れ子リスト
	function getFileListRecursive($dir) {
		$files = glob(rtrim($dir, '/') . '/*');
		$list = array();
		foreach ($files as $file) {
			if (is_file($file) && preg_match('/\.html$/', $file)) {
					$list[$file] = $file;
			}
			if (is_dir($file)) {
					$list[$file . '/'] = $this -> getFileListRecursive($file);
			}
		}

		return $list;
	}


	// 配下全階層にあるファイルの直列リスト
	// 負荷注意
	function getFileListLinear($dir) {
		$files = glob(rtrim($dir, '/') . '/*');
		$list = array();
		foreach ($files as $file) {
			if (is_file($file)) {
					$list[] = $file;
			}
			if (is_dir($file)) {
					$list = array_merge($list, $this -> getFileListLinear($file));
			}
		}

		return $list;
	}

	// 直下階層分、単一レベルのファイルリストを返す
	function getFileListSingleLevel($dir) {
		$files = glob(rtrim($dir, '/') . '/*');
		$list = array();
		foreach ($files as $file) {
					$list[] = $file;
		}

		return $list;
	}

	// ディレクトリ正当性チェック
	function isPathUnderDocRoot($path, $siteRoot) {
		if(!preg_match('/^' . str_replace('/', '\/', $siteRoot) . '/', realpath($path) . '/')) {
				return false;
		}

		return true;
	}
}
?>
