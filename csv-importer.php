<?php
/*
Plugin Name: CSV Importer
Plugin URI: https://example.com/
Description: CSVいんぽーたー
Version: 1.0
Author: MATSUDA
*/

// 直接ファイルを実行されることを防ぐためのセキュリティチェック
defined('ABSPATH') or die('直接のアクセスを禁止します！');


add_action('admin_menu', 'csv_importer_menu');
function csv_importer_menu()
{
    add_submenu_page(
        'options-general.php',
        'CSV Importer',
        'CSV Importer',
        'manage_options',
        'csv-importer',
        'csv_importer_settings'
    );
}


// プラグイン設定ページを追加
function csv_importer_settings()
{
    // ユーザーが必要な権限を持っているかどうかを確認
    if (!current_user_can('manage_options')) {
        wp_die('このページにアクセスするための十分な権限がありません。');
    }

    // ファイルのアップロードを処理
    if (isset($_POST['csv_upload'])) {
        // ファイルが正常にアップロードされたかどうかを確認
        if (!empty($_FILES['csv_file']['name']) && $_FILES['csv_file']['error'] == 0) {
            // ファイルが CSV ファイルかどうかを確認
            $allowed_mime_types = array('text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext', 'application/octet-stream', 'application/txt');
            $allowed_extensions = array('csv');
            $file_info = pathinfo($_FILES['csv_file']['name']);

            if (in_array($_FILES['csv_file']['type'], $allowed_mime_types) && in_array($file_info['extension'], $allowed_extensions)) {
                // ファイルを読み取り用に開く
                $file_handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
                // 最初の行を読み込んでスキップする
                fgetcsv($file_handle, 1000, ',');
                // CSVデータを配列に読み込む
                $data = array();
                while (($row = fgetcsv($file_handle, 1000, ',')) !== false) {
                    $data[] = $row;
                }

                // ファイルハンドルを閉じる
                fclose($file_handle);

                define("DB_HOST", "db");
                define("DB_USER", "user");
                define("DB_PASSWORD", "user_pass_Ck6uTvrQ");
                define("DB_NAME", "wordpress_db");
                global $wpdb;

                $table_name = $wpdb->prefix . 'csv_importer_data';
                // データベース接続を開く
                $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                // エラーチェック
                if ($db->connect_errno) {
                    wp_die('データベース接続に失敗しました。');
                }

                // 既存のテーブルが存在する場合はドロップ
                $db->query("DROP TABLE IF EXISTS `" . $db->real_escape_string($table_name) . "`");

                // UTF-8をデフォルト文字セットに設定する
                $db->set_charset("utf8mb4");

                // 新しいテーブルを作成
                $db->query("CREATE TABLE `" . $db->real_escape_string($table_name) . "` (prefectures VARCHAR(255), dealer VARCHAR(255), link VARCHAR(255), address VARCHAR(255), tel VARCHAR(255)) DEFAULT CHARSET=utf8mb4;");

                // // データをテーブルに挿入
                // $stmt = $db->prepare("INSERT INTO `" . $db->real_escape_string($table_name) . "` (prefectures, dealer, link, address, tel) VALUES (?, ?, ?, ?, ?)");
                // foreach ($data as $row) {
                //     $stmt->bind_param('sssss', $row[0], $row[1], $row[2], $row[3], $row[4]);
                //     $stmt->execute();
                // }

                // データをテーブルに挿入
                $stmt = $db->prepare("INSERT INTO `" . $db->real_escape_string($table_name) . "` (prefectures, dealer, link, address, tel) VALUES (?, ?, ?, ?, ?)");
                // 特殊文字や前後の空白をトリム
                foreach ($data as $row) {
                    $prefecture = trim($row[0]);
                    $dealer = trim($row[1]);
                    $link = trim($row[2]);
                    $address = trim($row[3]);
                    $tel = trim($row[4]);
                    $stmt->bind_param('sssss', $prefecture, $dealer, $link, $address, $tel);
                    $stmt->execute();
                }

                // ステートメントとデータベース接続を閉じる
                $stmt->close();
                $db->close();

                // 成功メッセージを表示
                echo '<div id="message" class="updated notice is-dismissible"><p>データが正常にインポートされました。</p></div>';
            } else {
                // ファイルが CSV ファイルでない場合はエラー メッセージを表示
                echo '<div id="message" class="error notice is-dismissible"><p>ファイルの種類または拡張子が無効です。 CSV ファイルをアップロードしてください。</p></div>';
            }
        } else {
            // ファイルが正常にアップロードされなかった場合は、エラー メッセージを表示
            echo '<div id="message" class="error notice is-dismissible"><p>ファイルのアップロードに失敗しました。</p></div>';
        }
    }
?>
    <div class="wrap">
        <h1>CSV Importer</h1>
        <form method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="csv_file">CSV File:</label></th>
                        <td><input type="file" name="csv_file" id="csv_file"></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button('Import', 'primary', 'csv_upload'); ?>
        </form>
    </div>
<?php
}



function custom_shortcode($atts)
{
    global $wpdb;

    // パラメータの処理
    $prefectures = sanitize_text_field($atts['prefectures']);

    // テーブル名のエスケープ
    $table_name = esc_sql($wpdb->prefix . 'csv_importer_data');

    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE prefectures = %s", $prefectures);
    $results = $wpdb->get_results($query);

    $output = ''; // 出力するコンテンツを格納する変数

    // 行ごとにデータを取り出し、HTMLのテーブルに追加する
    foreach ($results as $row) {
        $output .= '<tr><td class="img-center"><a href="' . esc_html($row->link) . '" target="_blank" rel="noopener noreferrer"><img src="/wp-content/uploads/2016/05/home_hp.jpg" alt="HP" title="HP" class="mx-auto d-block img-fluid"></a></td><td><a href="' . esc_html($row->link) . '" target="_blank" rel="noopener noreferrer">' . esc_html($row->dealer) . '</a></td><td>' . esc_html($row->address) . '</td><td>' . esc_html($row->tel) . '</td></tr>';
    }

    // テーブルを作成し、データを挿入する
    $table_output = '<table class="table"><tbody><tr><th class="shop_search-web">Web</th><th class="shop_search-shop">取扱店名</th><th class="shop_search-address">住所</th><th class="tel">TEL</th></tr>' . $output . '</tbody></table>';

    return $table_output; // ショートコードで呼び出されたときに出力される内容
}

add_shortcode('output_dealer', 'custom_shortcode'); // ショートコードを登録する
