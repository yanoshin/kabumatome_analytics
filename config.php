<?php
/**
 * config.php
 */

date_default_timezone_set('Asia/Tokyo');

//一覧ページのURLテンプレート
define('TMPL_URL_INDEX', 'http://kabumatome.doorblog.jp/?p=%d');

//集計する範囲
define('DATETIME_START', strtotime('2020-01-01 00:00:00'));
define('DATETIME_END', strtotime('2020-12-31 23:59:59'));



