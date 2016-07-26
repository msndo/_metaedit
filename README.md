#HTML META Editor

##概要
Webサイト上、_metaeditを設置したディレクトリ配下のHTMLの&lt;title&gt;, &lt;meta name="keywords"&gt;, &lt;meta name="description"&gt;を一覧表示し、
また、画面上で編集を行うことができます。

##インストール
- このリポジトリをCLONEまたはダウンロード
- _metaedit/ をDOCUMENT_ROOTに配置
- ブラウザアクセス
- Webスプレッドシート上に配下HTMLファイルの&lt;title&gt;, &lt;meta name="keywords"&gt;, &lt;meta name="description"&gt;を表示
- 編集可能
- 矩形コピー＆ペーストが可能

##動かない場合
- PHP5.2以上が必要
- _metaedit/htaccess を .htaccessとして保存してみてください

##設定
_metaedit/conf/conf-common.ini

- Title, Keywords, Description以外、OGPなども編集対象にできます
- 設定を変更する場合、上記ファイルはgitignoreしてください。
- 閲覧専用モードにできます。上記ファイル最後の項目。

##Powered by
- [jQuery](https://jquery.com/)
- [handsontable](https://github.com/handsontable/handsontable)

##ライセンス
This software is released under the MIT License, see LICENSE.txt.

##免責・注意事項
このソフトウェアを使用したことによって生じたすべての障害・損害・不具
合等に関しては、私と私の関係者および私の所属するいかなる団体・組織とも、
一切の責任を負いません。各自の責任においてご使用ください。「ライセンス」について併せてお読みください

サーバ上のドキュメントをブラウザから編集可能とします。公開領域への配置は推奨しません。
公開領域に配置する場合、認証・セキュリティを十分に施すものとしてください。
