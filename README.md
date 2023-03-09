# プラグインについてa
* CSVファイルをWordPressのデータベースにインポートする機能を提供するプラグイン。
* WordPressの管理画面に「CSV Importer」というメニューを追加する。

# CSVファイルについて
* ファイルタイプが「text/csv」または「application/csv」で、拡張子が「csv」である必要がある。
* 2行目から始まるデータ行を含む必要がある。
* 最初の行はフィールド名として解釈され、データベースには含まれない。

# データベースについて
* CSVファイルから読み取られたデータは、新しいテーブルを作成して保存される。#
* テーブル名は、WordPressのプレフィックスと「csv_importer_data」を組み合わせたものになる。

# プラグインの動作について
* MySQLまたはMariaDBが必要。
* データベースに接続するためのユーザー名、パスワード、ホスト名、およびデータベース名を設定する必要がある。
* カラム数が一致しない場合、プラグインはエラーを返す。
* 管理者権限を持つユーザーでログインする必要がある。
* プラグインの設定ページにアクセスするために、「CSV Importer」というメニューを選択する必要がある。

# ファイルのアップロードについて
* WordPressの管理画面に「CSV Importer」という名前のサブメニューを追加する。
* CSVファイルをアップロードするフォームが表示される。
* ファイルがCSVファイルであるかどうかを確認する。
* ファイルがCSVファイルであれば、データをデータベースにインポートする。
* インポートが成功した場合は、成功メッセージが表示される。ファイルがCSVファイルでない場合、またはファイルがアップロードされていない場合は、エラーメッセージが表示される。
* プラグインを使用するには、MySQLまたはMariaDBが必要であり、プラグインのコードにハードコーディングされたデータベース接続情報を適切な値に変更する必要がある。
* CSVファイルに含まれる列の数と、テーブルの列の数が一致していることを確認する必要があり、列の数が一致しない場合はエラーが返される。
* プラグインを使用する前には、WordPressの管理画面にログインし、管理者権限を持つユーザーである必要があり、またプラグインの設定ページにアクセスする必要がある。
* プラグインは日本の都道府県、ディーラー名、リンク、住所、電話番号の5つのフィールドからなるCSVファイルをインポートできる。
* プラグインはCSVファイルを読み取り、WordPressのデータベースに新しいテーブルを作成してデータを保存する。テーブル名はプレフィックスと"csv_importer_data"を組み合わせたものになる。
* ファイルがCSVファイルであれば、インポートが成功した場合は成功メッセージが表示され、CSVファイルでない場合やファイルがアップロードされていない場合はエラーメッセージが表示される。

# セキュリティー対策について
* 直接ファイルを実行されることを防ぐためのセキュリティチェックの実装
* current_user_can() 関数を使用し、管理者権限を持つユーザーのみがアクセスできるようにする
* if (isset($_POST['csv_upload'])) により、ファイルのアップロードが必要な場合にのみファイルの処理を実行する
* アップロードされたファイルがCSVファイルであることを確認するために、ファイルのMIMEタイプと拡張子を検証する
* SQLインジェクションを防止するために、real_escape_string()関数を使用してテーブル名をエスケープする
* mysqliのprepare()関数を使用し、クエリをパラメータ化して、SQLインジェクションを防止する