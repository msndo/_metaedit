(function($) {
	// ================================================== Main Routine
	$(function() {
		$.updateTableFiles(dataTableListFile);

		$('#section-list-tree-file').draggable();
		$('#section-list-tree-file li .cont-elem-list').bindMovementToggleChildDir().bindMovementShowSpreadsheet();
	});

	// ================================================== Sub Routines
	$.extend({
		updateTableFiles: function(data) {

			var $container = $('#section-table-list-file');
			var container = $container.get(0);

			// スプレッドシート表示データ
			var listContent = (function() { var listResult = []; $.each(data, function(ix, value) { listResult.push(value['listContent']) }); return listResult; })();
			// スプレッドシート セル表示設定
			var listCellProperty = (function() {
				var listResult = [];
				var offsetCol = 1;

				$.each(data, function(ixRow, contData) {
					$.each(contData['listProperty'], function(ixCol, dataElemCell) {
						dataElemCell['row'] = ixRow;
						dataElemCell['col'] = ixCol + offsetCol;

						listResult.push(dataElemCell);
					});
				});

				return listResult;
			})();

			// スプレッドシート表示実行
			var grid = new Handsontable(container, {
	        	data: listContent,
				colHeaders: $.listColHeaders,
				width: function() { return $('#main-contents').width(); },
				autoWrapCol: true,
				autoColumnSize: true,
				stretchH: 'all',
				columns: $.listColumns,
				cell: listCellProperty,
				beforeChange: function(changes, source) {
					var scopeTable = this;
					if(! changes) { return; }

					var $tableContainer = $container.find('.htCore');
					var $rowRecord = $tableContainer.find('tr');

					$.each(changes, function(ix) {
						// 該当行を送信中にマーク。更新がない状態だったら何もしない
						var dataRow = this;

						var rowTarget = $rowRecord.eq(changes[ix][0] + 1).get(0);
						var statusRow = rowTarget.getAttribute('status-update');

						if((dataRow[2] != dataRow[3])) {
							rowTarget.setAttribute('status-update', 'send');
						}
					});
				},

				// 更新リクエスト サーバ送信
				afterChange: function(changes, source) {
					var scopeTable = this;
					if(!changes) { return; }

					var indexColumnForUrl = 0;
					var listPairColumnAndProp = $.listPairColumnAndProp;
					var listInfoUpdate = {};

					// ++++++++++++++++++++++++++++++++ 更新情報生成
					$.each(changes, function() {
						var dataRow = this;

						if(!dataRow) { return true; }
						if(dataRow[2] === dataRow[3]) { return true; }

						var urlTargetUpdate = scopeTable.getDataAtCell(dataRow[0], indexColumnForUrl).replace(/(^.*>)(.*?)(<.*$)/, "$2").replace(/^\//, '');
						var keyList = listPairColumnAndProp[dataRow[1]];

						if(! listInfoUpdate[urlTargetUpdate]) {
							listInfoUpdate[urlTargetUpdate] = {};
						}
						if(!listInfoUpdate[urlTargetUpdate]['listEdit']) { listInfoUpdate[urlTargetUpdate]['listEdit'] = {}; }
						listInfoUpdate[urlTargetUpdate]['listEdit'][keyList] = dataRow[3];

						listInfoUpdate[urlTargetUpdate]['ixRow'] = dataRow[0];
					});

					// console.log(listInfoUpdate)

					// ++++++++++++++++++++++++++++++++ 更新実行
					$.ajax({
						type: "post",
						url: "apply-edit.php",
						data: JSON.stringify(listInfoUpdate),
						contentType: 'application/json',
						dataType: 'text',
						success: function(json_data) {
							if(! $('[status-update=send]').length) { return true; }

							var listResult = JSON.parse(json_data);
							$.each(listResult, function(ixResult, contResult) {
								var ixRow = contResult['ixRow'];
								var status = contResult['status'];

								var $elemRow = $('#section-table-list-file .htCore tr').eq(ixRow + 1);
								$elemRow.get(0).setAttribute('status-update', status);
							});
						},
						error: function() {
							if(! $('[status-update=send]').length) { return true; }
							$('[status-update=send]').each(function() {
								$(this).get(0).setAttribute('status-update', 'error');
							});
						}
					});
				}
			});
		}
	});

	$.fn.bindMovementShowSpreadsheet = function() {
		var $listElemCtrl = this;

		$listElemCtrl.on('click.showSpreadSheet', function(ev) {
			ev.stopPropagation();

			var $elemCtrl = $(this);
			var $elemContainer = $elemCtrl.parent();

			var pathTarg = $elemCtrl.attr('href');

			$.ajax({
				type: "post",
				url: "list-file.php",
				data: { "basePath": pathTarg },
				dataType: 'json',
				success: function(dataTableListFile) {
					if(dataTableListFile) {
						$('#section-table-list-file').fadeOut(430, function() {
							$(this).html('').css('display', 'block');
							$.updateTableFiles(dataTableListFile);
						});
					}
				}
			});

			$('#list-tree-file').find('.current-select').each(function() { $(this).removeClass('current-select'); })
			$elemContainer.addClass('current-select');
			$('#title-article-spreadsheet a').attr('href', pathTarg).text(pathTarg);
		});

		return this;
	};

	$.fn.bindMovementToggleChildDir = function() {
		var $listElemCtrl = this;

		$listElemCtrl.on('click.togglechild', function(ev) {
			ev.stopPropagation();

			var $elemCtrl = $(this);
			var $elemContainer = $elemCtrl.parent();
			var $elemChildDir = $elemContainer.children('ul');

			if($elemContainer.hasClass('type-top-dir')) { return false; }

			if(! $elemChildDir.length) {
				$elemCtrl.displayChildDir();
				return false;
			}
			else if($elemChildDir.css('display') == 'none') {
				$elemChildDir.slideDown(100, function() { $('#section-list-tree-file').fixHeightWithInner($('#list-tree-file')); });
			}
			else if($elemChildDir.css('display') != 'none') {
				$elemChildDir.slideUp(100, function() { $('#section-list-tree-file').fixHeightWithInner($('#list-tree-file')); });
			}


			return false;
		});

		return this;
	};

	$.fn.displayChildDir = function(options) {
		var $elemCtrl = this;
		var $elemContainer = $elemCtrl.parent();

		var $elemAppend;

		var pathTarg = $elemCtrl.attr('href');
		$.ajax({
			type: "post",
			url: "list-dir.php",
			data: { "basePath": pathTarg },
			success: function(dataHtml) {
				if(dataHtml) {
					$elemAppend = $(dataHtml).hide();
					$elemContainer.append($elemAppend);
					$elemAppend.slideDown(100, function() { $('#section-list-tree-file').fixHeightWithInner($('#list-tree-file')); });
					$elemAppend.find('.cont-elem-list').bindMovementToggleChildDir().bindMovementShowSpreadsheet();
				}
			}
		});
	};

	$.fn.fixHeightWithInner = function($elemInner) {
		var $elemTarg = this;
		var $elemInner = $elemInner;

		var heightInner = $elemInner.height();
		var offsetHeight = 30;

		var heightWindow = window.innerHeight;

		if(heightInner > heightWindow) {
			$elemTarg.height(heightWindow + offsetHeight);
		}
		else {
			$elemTarg.height(heightInner + offsetHeight);
		}
	};
})(jQuery);
