<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3168 2010-05-28 06:48:48Z fishbone $
 * @link       http://www.m-media.co.jp
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## コンテンツ一覧 ##########
$HELP['content']['title'] = 'コンテンツ一覧';
$HELP['content']['body'] = 'コンテンツの一覧です。';
$HELP['content_check']['title'] = '選択用チェックボックス';
$HELP['content_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['content_id']['title'] = 'コンテンツID';
$HELP['content_id']['body'] = 'コンテンツを識別するためのIDです。新規追加時に自動的に設定されます。';
$HELP['content_name']['title'] = '名前';
$HELP['content_name']['body'] = 'コンテンツの名前です。コンテンツのタイトルとして表示されます。';
$HELP['content_visible']['title'] = '公開';
$HELP['content_visible']['body'] = 'コンテンツをユーザに公開するかどうかを制御します。非公開に設定の場合はユーザから参照することはできません。';
$HELP['content_limited']['title'] = 'ユーザ制限';
$HELP['content_limited']['body'] = 'コンテンツの参照をログインしたユーザに限定するかどうかを設定します。チェックが入っているコンテンツはログインユーザだけが参照可能です。';
$HELP['content_active_term']['title'] = '公開期間';
$HELP['content_active_term']['body'] = 'コンテンツをユーザに公開する期間を設定します。空の場合は制限なしを示します。';
$HELP['content_default']['title'] = 'デフォルト項目';
$HELP['content_default']['body'] = 'URLのパラメータでコンテンツIDが指定されていない場合に表示されるコンテンツを指定します。1つだけ設定可能です。';
$HELP['content_update_user']['title'] = '更新者';
$HELP['content_update_user']['body'] = 'コンテンツを更新したユーザです。';
$HELP['content_update_dt']['title'] = '更新日時';
$HELP['content_update_dt']['body'] = 'コンテンツを更新した日時です。';
$HELP['content_view_count']['title'] = '閲覧数';
$HELP['content_view_count']['body'] = 'コンテンツがユーザに閲覧された回数です。管理者の閲覧はカウントされません。';
$HELP['content_act']['title'] = '操作';
$HELP['content_act']['body'] = '各種操作を行います。<br />●メニューに追加<br />「メインメニュー」ウィジェットにコンテンツを表示するメニュー項目を追加します。';
$HELP['content_html']['title'] = 'HTML';
$HELP['content_html']['body'] = 'コンテンツの内容となるHTMLです。';
$HELP['content_ref_custom']['title'] = '置換文字列を参照';
$HELP['content_ref_custom']['body'] = 'コンテンツに埋め込んだ置換文字列はコンテンツ表示時に設定文字列に変換します。置換文字列の設定値を参照します。';
$HELP['content_key']['title'] = '外部参照用キー';
$HELP['content_key']['body'] = '外部ウィジェットからの取得用キーです。';
$HELP['other_show_title']['title'] = 'コンテンツタイトルの表示';
$HELP['other_show_title']['body'] = 'コンテンツのタイトルの表示制御を行います。';
$HELP['other_show_message_deny']['title'] = '参照不可の場合はメッセージを表示';
$HELP['other_show_message_deny']['body'] = 'ユーザが参照不可のコンテンツにアクセスした場合のメッセージを設定します。';
$HELP['content_meta_title']['title'] = 'タイトル名';
$HELP['content_meta_title']['body'] = 'ヘッダ部のtitleタグに設定される文字列です。Webブラウザの画面タイトルとして表示されます。';
$HELP['content_meta_description']['title'] = 'ページ要約';
$HELP['content_meta_description']['body'] = 'ヘッダ部のdescriptionタグに設定される文字列です。120文字程度で記述します。';
$HELP['content_meta_keywords']['title'] = '検索キーワード';
$HELP['content_meta_keywords']['body'] = 'ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。';

$HELP['content_new_btn']['title'] = '新規ボタン';
$HELP['content_new_btn']['body'] = '新規コンテンツを追加します。';
$HELP['content_edit_btn']['title'] = '編集ボタン';
$HELP['content_edit_btn']['body'] = '選択されているコンテンツを編集します。<br />コンテンツを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['content_del_btn']['title'] = '削除ボタン';
$HELP['content_del_btn']['body'] = '選択されているコンテンツを削除します。<br />コンテンツを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['content_ret_btn']['title'] = '戻るボタン';
$HELP['content_ret_btn']['body'] = 'コンテンツ一覧へ戻ります。';
$HELP['content_preview_btn']['title'] = 'プレビューボタン';
$HELP['content_preview_btn']['body'] = 'コンテンツを表示した実際の画面です。';
?>
