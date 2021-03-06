<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_default_contentContentWidgetContainer.php 5893 2013-04-01 13:27:22Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/admin_default_contentBaseWidgetContainer.php');
require_once($gEnvManager->getLibPath()			. '/qqFileUploader/fileuploader.php');

class admin_default_contentContentWidgetContainer extends admin_default_contentBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $selectedItem = array();	// 選択中の項目
	private $attachFileInfoArray;		// 添付ファイルの情報
	private $fieldValueArray;		// ユーザ定義フィールド入力値
	private $completed;			// データ登録完了かどうか
	private $isExistsContent;		// コンテンツ項目が存在するかどうか
	private $isMultiLang;			// 多言語対応画面かどうか
	private $isOpenOptionArea;		// 拡張エリアを開くかどうか
	private $pluginIdArray = array();		// jQueryプラグインのID
	private $selectedPlugin = array();			// 選択しているjQueryプラグイン
	private $addLib = array();		// 追加スクリプトライブラリ
	private $templateId;	// テンプレートID
	const ICON_SIZE = 16;		// アイコンのサイズ
	const PANEL_BUTTON_SIZE = 32;	// 拡張エリア制御ボタンサイズ
	const INC_INDEX = 1;		// メニュー項目表示順の増加分
	const ADMIN_WIDGET_ID = 'admin_main';		// 管理ウィジェットのウィジェットID
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const ACTIVE_ICON_FILE = '/images/system/active.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive.png';		// 非公開アイコン
	const ADD_TO_MENU_ICON_FILE = '/images/system/addtomenu.png';		// メニューに追加用アイコン
	const PREVIEW_ICON_FILE = '/images/system/preview.png';		// プレビュー用アイコン
	const OPEN_PANEL_ICON_FILE = '/images/system/plus32.png';		// 拡張エリア表示用アイコン
	const CLOSE_PANEL_ICON_FILE = '/images/system/minus32.png';		// 拡張エリア非表示用アイコン
	const LANG_ICON_PATH = '/images/system/flag/';		// 言語アイコンパス
	const MSG_UPDATE_CONTENT = 'コンテンツを更新しました';			// コンテンツ更新メッセージ
	const DEFAULT_SEARCH_KEY = '1';			// デフォルトの検索キー(更新日時)
	const DEFAULT_SEARCH_ORDER = '1';			// デフォルトの検索ソート順(降順)
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	const LOG_MSG_ADD_CONTENT = '汎用コンテンツ(%s)を追加しました。タイトル: %s';
	const LOG_MSG_UPDATE_CONTENT = '汎用コンテンツ(%s)を更新しました。タイトル: %s';
	const LOG_MSG_DEL_CONTENT = '汎用コンテンツ(%s)を削除しました。タイトル: %s';
	const FIELD_HEAD = 'item_';			// フィールド名の先頭文字列
	const LIB_ITEM_HEAD = 'item_lib_';			// 選択ライブラリの項目名ヘッダ
	const LIB_CODEMIRROR_JAVASCRIPT	= 'codemirror.javascript';		// CodeMirror Javascript
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->isMultiLang = $this->gEnv->isMultiLanguageSite();			// 多言語対応画面かどうか
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{
		$filename = '';
		$task = $request->trimValueOf('task');
		switch ($task){
			case self::TASK_CONTENT:		// コンテンツ管理
			default:
				$filename = 'admin_main.tmpl.html';
				break;
			case self::TASK_CONTENT_DETAIL:		// 詳細画面
				$filename = 'admin_main_detail.tmpl.html';
				break;
			case self::TASK_ADD_TO_MENU:		// コンテンツへのリンクをメニュー項目に追加
				$filename = 'admin_list.tmpl.html';
				break;
		}
		return $filename;
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$task = $request->trimValueOf('task');
		switch ($task){
			case self::TASK_CONTENT:		// コンテンツ管理
			default:
				$this->createList($request);
				break;
			case self::TASK_CONTENT_DETAIL:		// 詳細画面
				$this->createDetail($request);
				break;
			case self::TASK_ADD_TO_MENU:		// コンテンツへのリンクをメニュー項目に追加
				$this->createAddToMenu($request);
				break;
		}
	}
	/**
	 * コンテンツ一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ユーザ情報、表示言語
		$userId = $this->gEnv->getCurrentUserId();
		$this->langId = $this->gEnv->getDefaultLanguage();
		
		// 一覧表示数
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$searchKeyword = $request->trimValueOf('search_keyword');			// 検索キーワード
		$searchKey = $request->trimValueOf('search_key');			// 検索ソートキー
		if ($searchKey == '') $searchKey = self::DEFAULT_SEARCH_KEY;
		$searchOrder = $request->trimValueOf('search_order');			// 検索ソート順
		if ($searchOrder == '') $searchOrder = self::DEFAULT_SEARCH_ORDER;

		$act = $request->trimValueOf('act');
		if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				// 削除するコンテンツの情報を取得
				$delContentInfo = array();
				for ($i = 0; $i < count($delItems); $i++){
					$ret = self::$_mainDb->getContentBySerial($delItems[$i], $row);
					if ($ret){
						$newInfoObj = new stdClass;
						$newInfoObj->contentId = $row['cn_id'];		// コンテンツID
						$newInfoObj->name = $row['cn_name'];		// コンテンツ名前
						$newInfoObj->thumb = $row['cn_thumb_filename'];		// サムネール
						$delContentInfo[] = $newInfoObj;
					}
				}
				
				// 多言語対応状態に関わらずコンテンツIDで削除
				//if ($this->isMultiLang){		// 多言語対応のとき
					$ret = self::$_mainDb->delContentItemById($delItems);
				//} else {
				//	$ret = self::$_mainDb->delContentItem($delItems);
				//}
				
				if ($ret){
					for ($i = 0; $i < count($delContentInfo); $i++){
						$infoObj = $delContentInfo[$i];
						
						// サムネールを削除
						if (!empty($infoObj->thumb)){
							$oldFiles = explode(';', $infoObj->thumb);
							$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $oldFiles);
						}
						
						// 添付ファイルをコンテンツIDで削除
						$ret = $this->gInstance->getFileManager()->delAttachFileInfoByContentId(default_contentCommonDef::$_viewContentType,
																								$infoObj->contentId, default_contentCommonDef::getAttachFileDir());
						if (!$ret) break;
					}
				}

				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// キャッシュデータのクリア
					for ($i = 0; $i < count($delItems); $i++){
						$this->clearCacheBySerial($delItems[$i]);
					}
					
					// 運用ログを残す
					for ($i = 0; $i < count($delContentInfo); $i++){
						$infoObj = $delContentInfo[$i];
						$contentAttr = default_contentCommonDef::$_deviceTypeName;
						$this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_DEL_CONTENT, $contentAttr, $infoObj->name), 2100, 'ID=' . $infoObj->contentId);
					}
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			$pageNo = 1;		// ページ番号初期化
		} else if ($act == 'selpage'){			// ページ選択
		}
		// コンテンツ総数を取得
		$totalCount = self::$_mainDb->getContentCount(default_contentCommonDef::$_contentType, $this->langId, $searchKeyword, $searchKey, $searchOrder);

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		
		// 一覧の表示タイプを設定
		if ($this->isMultiLang){		// 多言語対応の場合
			$this->tmpl->setAttribute('show_multilang', 'visibility', 'visible');
		} else {
			$this->tmpl->setAttribute('show_singlelang', 'visibility', 'visible');
		}
		
		// コンテンツリストを取得
		self::$_mainDb->searchContent(default_contentCommonDef::$_contentType, $this->langId, $maxListCount, $pageNo, $searchKeyword, $searchKey, $searchOrder, array($this, 'itemListLoop'));
		if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// コンテンツ項目がないときは、一覧を表示しない
		
		// 画面にデータを埋め込む
		// 検索条件
		if (empty($searchKey)){			// コンテンツIDをキーにする場合
			$this->tmpl->addVar('_widget', 'search_content_id_checked', 'checked');
		} else {
			$this->tmpl->addVar('_widget', 'search_update_dt_checked', 'checked');
		}
		if (empty($searchOrder)){			// 昇順にソートするとき
			$this->tmpl->addVar('_widget', 'search_asc_checked', 'checked');
		} else {
			$this->tmpl->addVar('_widget', 'search_desc_checked', 'checked');
		}
		
		// 検索結果
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("_widget", "search_keyword", $searchKeyword);	// 検索キーワード
		
		// その他
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "target_widget", $this->gEnv->getCurrentWidgetId());// メニュー選択ウィンドウ表示用
		$this->tmpl->addVar("_widget", "device_type", default_contentCommonDef::$_deviceType);		// デバイスタイプ
	}
	/**
	 * コンテンツ詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ユーザ情報、表示言語
		$this->langId = $this->gEnv->getDefaultLanguage();
		
		// ウィンドウ表示状態
		$openby = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);
		
		// 言語を取得
		if ($this->isMultiLang){		// 多言語対応の場合
			$langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
			if (!empty($langId)) $this->langId = $langId;
		}
		// コンテンツレイアウトを取得
		$contentLayout = self::$_configArray[default_contentCommonDef::$CF_LAYOUT_VIEW_DETAIL];
		$fieldInfoArray = default_contentCommonDef::parseUserMacro($contentLayout);

		// jQueryプラグインのIDを取得
		$useJQuery = self::$_configArray[default_contentCommonDef::$CF_USE_JQUERY];		// jQueryスクリプト作成するかどうか
		if ($useJQuery){		// jQueryスクリプト作成機能を使用する場合
			$this->pluginIdArray = $this->gPage->getScriptLibId(2/*jQueryプラグインのみ*/);
			
			$pluginCount = count($this->pluginIdArray);
			for ($i = 0; $i < $pluginCount; $i++){
				$itemPluginId = str_replace('.', '_', $this->pluginIdArray[$i]);
				$itemName = self::LIB_ITEM_HEAD . $itemPluginId;
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				if ($itemValue) $this->selectedPlugin[] = $this->pluginIdArray[$i];
			}
		}
		$useContentTemplate = self::$_configArray[default_contentCommonDef::$CF_USE_CONTENT_TEMPLATE];// コンテンツ単位のテンプレート設定を行うかどうか
		
		// 入力値を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$contentId = $request->trimValueOf('contentid');		// コンテンツID
		$name = $request->trimValueOf('item_name');
		$html = $request->valueOf('item_html');		// HTMLタグを可能とする
		$desc = $request->trimValueOf('item_desc');		// 簡易説明
		$key = $request->trimValueOf('item_key');		// 外部参照用キー
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// チェックボックス
		$limited = ($request->trimValueOf('item_limited') == 'on') ? 1 : 0;		// チェックボックス
		$default = ($request->trimValueOf('item_default') == 'on') ? 1 : 0;		// チェックボックス
		$metaTitle = $request->trimValueOf('item_meta_title');		// ページタイトル名
		$metaDesc = $request->trimValueOf('item_meta_desc');			// ページ要約
		$metaKeyword = $request->trimValueOf('item_meta_keyword');	// ページキーワード
		$password = $request->trimValueOf('password');
		$relatedContent = $request->trimValueOf('item_related_content');	// 関連コンテンツ
		$jQueryScript = $request->valueOf('item_jquery_script');	// jQueryスクリプト
		$this->templateId	= $request->trimValueOf('item_template_id');	// テンプレートID
		
		$start_date = $request->trimValueOf('item_start_date');		// 公開期間開始日付
		if (!empty($start_date)) $start_date = $this->convertToProperDate($start_date);
		$start_time = $request->trimValueOf('item_start_time');		// 公開期間開始時間
		if (empty($start_date)){
			$start_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($start_time)) $start_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		if (!empty($start_time)) $start_time = $this->convertToProperTime($start_time, 1/*時分フォーマット*/);
		
		$end_date = $request->trimValueOf('item_end_date');		// 公開期間終了日付
		if (!empty($end_date)) $end_date = $this->convertToProperDate($end_date);
		$end_time = $request->trimValueOf('item_end_time');		// 公開期間終了時間
		if (empty($end_date)){
			$end_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($end_time)) $end_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		if (!empty($end_time)) $end_time = $this->convertToProperTime($end_time, 1/*時分フォーマット*/);
		
		// ユーザ定義フィールド入力値取得
		$this->fieldValueArray = array();		// ユーザ定義フィールド入力値
		$fieldKeys = array_keys($fieldInfoArray);
		for ($i = 0; $i < count($fieldKeys); $i++){
			$fieldKey = $fieldKeys[$i];
			$itemName = self::FIELD_HEAD . $fieldKey;
			$itemValue = $this->cleanMacroValue($request->trimValueOf($itemName));
			if (!empty($itemValue)) $this->fieldValueArray[$fieldKey] = $itemValue;
		}
		
		// 添付ファイルリスト取得
		$attachFileCount = $request->trimValueOf('attachfilecount');		// 添付ファイル数
		$fileTitles	= $request->trimValueOf('item_filetitle');		// 添付ファイルタイトル
		$filenames	= $request->trimValueOf('item_filename');		// 添付ファイルファイル名
		$fileIds	= $request->trimValueOf('item_fileid');		// 添付ファイルファイルID
		$this->attachFileInfoArray = array();
		for ($i = 0; $i < $attachFileCount; $i++){
			$newInfoObj = new stdClass;
			$newInfoObj->title		= $fileTitles[$i];
			$newInfoObj->filename	= $filenames[$i];
			$newInfoObj->fileId		= $fileIds[$i];
			$this->attachFileInfoArray[] = $newInfoObj;
		}

		$reloadData = false;		// データの再読み込み
		$hasPassword = false;		// パスワードが設定されているかどうか
		$historyIndex = -1;	// 履歴番号
		if ($act == 'add'){		// 項目追加の場合。多言語対応の場合はデフォルト言語が最初に追加される。
			// 入力チェック
			$this->checkInput($name, '名前');

			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('公開期間が不正です');
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				if (empty($start_date)){
					$startDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$startDt = $start_date . ' ' . $start_time;
				}
				if (empty($end_date)){
					$endDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$endDt = $end_date . ' ' . $end_time;
				}

				// サムネール画像を取得
				$thumbFilename = '';
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					// 次のコンテンツIDを取得
					$nextContentId = self::$_mainDb->getNextContentId(default_contentCommonDef::$_contentType);
				
					$thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
					if (!empty($thumbPath)){
						$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $nextContentId, $thumbPath, $destFilename);
						if ($ret) $thumbFilename = implode(';', $destFilename);
					}
				}
				
				// 追加パラメータ
				$otherParams = array(	'cn_thumb_filename'		=> $thumbFilename,		// サムネールファイル名
										'cn_related_content'	=> $relatedContent,		// 関連コンテンツ
										'cn_option_fields'		=> $this->serializeArray($this->fieldValueArray),		// ユーザ定義フィールド値
										'cn_script'				=> $jQueryScript,	// jQueryスクリプト
										'cn_script_lib'			=> implode(',', $this->selectedPlugin),// jQueryプラグイン
										'cn_template_id'		=> $this->templateId);		// テンプレートID
				
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// 多言語でデフォルト言語、または単一言語のとき
					$ret = self::$_mainDb->addContentItem(default_contentCommonDef::$_contentType, $nextContentId * (-1)/*次のコンテンツIDのチェック*/,
														$this->langId, $name, $desc, $html, $visible, $default, $limited, $key, $password, 
														$metaTitle, $metaDesc, $metaKeyword, $startDt, $endDt, $newSerial, $otherParams);
				} else {
					$ret = self::$_mainDb->addContentItem(default_contentCommonDef::$_contentType, $contentId, 
														$this->langId, $name, $desc, $html, $visible, $default, $limited, $key, $password, 
														$metaTitle, $metaDesc, $metaKeyword, $startDt, $endDt, $newSerial, $otherParams);
				}
				// ##### 添付ファイル情報を更新 #####
				if ($ret){
					$ret = self::$_mainDb->getContentBySerial($newSerial, $row);
					if ($ret){
						$contentId = $row['cn_id'];		// コンテンツID
						$name = $row['cn_name'];		// コンテンツ名前
						$attachFileDir = default_contentCommonDef::getAttachFileDir();
					
						$ret = $this->gInstance->getFileManager()->updateAttachFileInfo(default_contentCommonDef::$_viewContentType, $contentId, 0, $newSerial,
																															$this->attachFileInfoArray, $attachFileDir);
					}
				}

				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
					
					// コンテンツ更新情報をサーバへ登録
					$ret = $this->registContentInfoBySerial($this->serialNo, true/*新規*/);
					if ($ret) $this->setGuidanceMsg('更新情報をサーバへアップしました');
					
					// 運用ログを残す
/*					$ret = self::$_mainDb->getContentBySerial($this->serialNo, $row);
					if ($ret){
						$contentId = $row['cn_id'];		// コンテンツID
						$name = $row['cn_name'];		// コンテンツ名前
					}*/
					$contentAttr = default_contentCommonDef::$_deviceTypeName;
					if ($this->isMultiLang) $contentAttr .= $this->getLangName($this->langId);		// 多言語対応の場合
					$this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_ADD_CONTENT, $contentAttr, $name), 2100, 'ID=' . $contentId);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg('公開期間が不正です');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				if (empty($start_date)){
					$startDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$startDt = $start_date . ' ' . $start_time;
				}
				if (empty($end_date)){
					$endDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$endDt = $end_date . ' ' . $end_time;
				}
				
				// サムネール画像を取得
				$thumbFilename = '';
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){		// // 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
					$thumbPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
					if (!empty($thumbPath)){
						$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $contentId, $thumbPath, $destFilename);
						if ($ret) $thumbFilename = implode(';', $destFilename);
					}
				}

				// 追加パラメータ
				$otherParams = array(	'cn_thumb_filename'		=> $thumbFilename,		// サムネールファイル名
										'cn_related_content'	=> $relatedContent,		// 関連コンテンツ
										'cn_option_fields'		=> $this->serializeArray($this->fieldValueArray),	// ユーザ定義フィールド値
										'cn_script'				=> $jQueryScript,	// jQueryスクリプト
										'cn_script_lib'			=> implode(',', $this->selectedPlugin),			// jQueryプラグイン
										'cn_template_id'		=> $this->templateId);		// テンプレートID
										
				$ret = self::$_mainDb->updateContentItem($this->serialNo, $name, $desc, $html, $visible, $default, $limited, $key, $password, 
															$metaTitle, $metaDesc, $metaKeyword, $startDt, $endDt, $newSerial, $oldRecord, $otherParams);
				if ($ret){
					// コンテンツに画像がなくなった場合は、サムネールを削除
					if (empty($thumbFilename) && !empty($oldRecord['cn_thumb_filename'])){
						$oldFiles = explode(';', $oldRecord['cn_thumb_filename']);
						$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $oldFiles);
					}
				
					// ##### 添付ファイル情報を更新 #####
					$ret = self::$_mainDb->getContentBySerial($newSerial, $row);
					if ($ret){
						$contentId = $row['cn_id'];		// コンテンツID
						$name = $row['cn_name'];		// コンテンツ名前
						$attachFileDir = default_contentCommonDef::getAttachFileDir();
					
						$ret = $this->gInstance->getFileManager()->updateAttachFileInfo(default_contentCommonDef::$_viewContentType, $contentId, $this->serialNo, $newSerial,
																															$this->attachFileInfoArray, $attachFileDir);
					}
				}
				
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
					
					// コンテンツ更新情報をサーバへ登録
					$ret = $this->registContentInfoBySerial($this->serialNo);
					if ($ret) $this->setGuidanceMsg('更新情報をサーバへアップしました');
					
					// 運用ログを残す
/*					$ret = self::$_mainDb->getContentBySerial($this->serialNo, $row);
					if ($ret){
						$contentId = $row['cn_id'];		// コンテンツID
						$name = $row['cn_name'];		// コンテンツ名前
					}*/
					$contentAttr = default_contentCommonDef::$_deviceTypeName;
					if ($this->isMultiLang) $contentAttr .= $this->getLangName($this->langId);		// 多言語対応の場合
					$this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_UPDATE_CONTENT, $contentAttr, $name), 2100, 'ID=' . $contentId);
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}				
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				// 削除するコンテンツの情報を取得
				$ret = self::$_mainDb->getContentBySerial($this->serialNo, $row);
				if ($ret){
					$contentId = $row['cn_id'];		// コンテンツID
					$name = $row['cn_name'];		// コンテンツ名前
				}

				$delByContentId = true;		// コンテンツをコンテンツIDで削除するかどうか
				if ($this->isMultiLang){		// 多言語対応のとき
					if ($this->langId == $this->gEnv->getDefaultLanguage()){		// デフォルト言語のときは全削除
						$ret = self::$_mainDb->delContentItemById(array($this->serialNo));
					} else {
						$ret = self::$_mainDb->delContentItem(array($this->serialNo));
						$delByContentId = false;		// コンテンツをコンテンツIDで削除するかどうか
					}
				} else {
					// 多言語対応状態に関わらずコンテンツIDで削除
					$ret = self::$_mainDb->delContentItemById(array($this->serialNo));
				}
			
				if ($ret){
					if ($delByContentId){		// コンテンツIDで削除のとき
						// サムネールを削除
						if (!empty($row['cn_thumb_filename'])){
							$oldFiles = explode(';', $row['cn_thumb_filename']);
							$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $oldFiles);
						}
						
						// 添付ファイルを削除
						$ret = $this->gInstance->getFileManager()->delAttachFileInfoByContentId(default_contentCommonDef::$_viewContentType,
																								$contentId, default_contentCommonDef::getAttachFileDir());
					} else {
						// 添付ファイルを削除
						$ret = $this->gInstance->getFileManager()->delAttachFileInfo(default_contentCommonDef::$_viewContentType,
																						$this->serialNo, default_contentCommonDef::getAttachFileDir());
					}
				}
				
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$reloadData = true;		// データの再読み込み
					
					// キャッシュデータのクリア
					$this->clearCacheBySerial($this->serialNo);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
					
					// 運用ログを残す
					$contentAttr = default_contentCommonDef::$_deviceTypeName;
					if ($this->isMultiLang && $this->langId != $this->gEnv->getDefaultLanguage()) $contentAttr .= $this->getLangName($this->langId);		// 多言語対応の場合
					$this->gOpeLog->writeUserInfo(__METHOD__, sprintf(self::LOG_MSG_DEL_CONTENT, $contentAttr, $name), 2100, 'ID=' . $contentId);
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'get_history'){		// 履歴データの取得のとき
			$reloadData = true;		// データの再読み込み
		} else if ($act == 'selectlang'){		// 言語選択
			// 多言語対応の場合の処理
			//$contentId = $request->trimValueOf('contentid');		// コンテンツID
			
			// コンテンツを取得
			$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->langId, $row);
			if ($ret){
				$this->serialNo = $row['cn_serial'];		// コンテンツシリアル番号
				$reloadData = true;		// データの再読み込み
			} else {
				$this->serialNo = 0;
			}
		} else if ($act == 'uploadfile'){		// 添付ファイルアップロード
			$uploader = new qqFileUploader(array());
			$resultObj = $uploader->handleUpload(default_contentCommonDef::getAttachFileDir());
			
			if ($resultObj['success']){
				$fileInfo = $resultObj['file'];
				$ret = $this->gInstance->getFileManager()->addAttachFileInfo(default_contentCommonDef::$_viewContentType, $fileInfo['fileid'], $fileInfo['path'], $fileInfo['filename']);
				if (!$ret){			// エラーの場合はファイルを添付ファイルを削除
					unlink($fileInfo['path']);
					$resultObj = array('error' => 'Could not create file information.');
				}
			}
			// ##### 添付ファイルアップロード結果を返す #####
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// 添付ファイルの登録データを返す
			if (function_exists('json_encode')){
				$destStr = json_encode($resultObj);
			} else {
				$destStr = $this->gInstance->getAjaxManager()->createJsonString($resultObj);
			}
			//$destStr = htmlspecialchars($destStr, ENT_NOQUOTES);// 「&」が「&amp;」に変換されるので使用しない
			//header('Content-type: application/json; charset=utf-8');
			header('Content-Type: text/html; charset=UTF-8');		// JSONタイプを指定するとIE8で動作しないのでHTMLタイプを指定
			echo $destStr;
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else {
			// ##### コンテンツIDが設定されているとき(他ウィジェットからの表示)は、データを取得 #####
			if (empty($contentId)){
				if (empty($this->serialNo)){		// 新規項目追加のとき
					$visible = 1;		// 非公開で登録されてしまうので、初期状態は表示に設定
				} else {
					$reloadData = true;		// データの再読み込み
				}
			} else {
				// 多言語対応の場合は、言語を取得
				if ($this->isMultiLang){		// 多言語対応の場合
					$langId = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);		// lang値を取得
					if (!empty($langId)) $this->langId = $langId;
				}
		
				// コンテンツを取得
				$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->langId, $row);
				if ($ret){
					$this->serialNo = $row['cn_serial'];		// コンテンツシリアル番号
					$reloadData = true;		// データの再読み込み
				} else {
					$visible = 1;		// 非公開で登録されてしまうので、初期状態は表示に設定
					$this->serialNo = 0;
				}
			}
			// ##### 初期表示時は仮登録の添付ファイルを削除 #####
			$this->gInstance->getFileManager()->cleanAttachFileInfo(default_contentCommonDef::$_viewContentType, default_contentCommonDef::getAttachFileDir());
		}
		if ($reloadData){		// データの再読み込み
			$ret = self::$_mainDb->getContentBySerial($this->serialNo, $row);
			if ($ret){
				$contentId = $row['cn_id'];		// コンテンツID
				$name = $row['cn_name'];		// コンテンツ名前
				$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $row['cn_html']);				// HTML
				$desc = $row['cn_description'];		// 簡易説明
				$key = $row['cn_key'];					// 外部参照用キー
				$update_user = $row['lu_name'];// 更新者
				$update_dt = $row['cn_create_dt'];
			
				// 項目表示、デフォルト値チェックボックス
				$visible = $row['cn_visible'];
				$default = $row['cn_default'];
				$limited = $row['cn_user_limited'];		// ユーザ制限
				$metaTitle = $row['cn_meta_title'];		// ページタイトル名(METAタグ)
				$metaDesc = $row['cn_meta_description'];		// ページ要約(METAタグ)
				$metaKeyword = $row['cn_meta_keywords'];		// ページキーワード(METAタグ)
				$start_date = $row['cn_active_start_dt'];	// 公開期間開始日
				$start_time = $row['cn_active_start_dt'];	// 公開期間開始時間
				$end_date = $row['cn_active_end_dt'];	// 公開期間終了日
				$end_time = $row['cn_active_end_dt'];	// 公開期間終了時間
				$relatedContent = $row['cn_related_content'];		// 関連コンテンツ
				$jQueryScript = $row['cn_script'];	// jQueryスクリプト
				if (!empty($row['cn_script_lib'])) $this->selectedPlugin = explode(',', $row['cn_script_lib']);		// jQueryプラグイン
				$this->templateId	= $row['cn_template_id'];	// テンプレートID
				
				// パスワード
				if (!empty($row['cn_password'])) $hasPassword = true;		// パスワードが設定されている
				
				// 履歴番号
				if ($row['cn_deleted']) $historyIndex = $row['cn_history_index'];
				
				// ユーザ定義フィールド
				$this->fieldValueArray = $this->unserializeArray($row['cn_option_fields']);

				// ##### 添付ファイル情報を取得 #####
				// 多言語対応の場合はデフォルト言語が選択されている場合のみ処理を行う
				if (($this->isMultiLang && $this->langId == $this->gEnv->getDefaultLanguage()) || !$this->isMultiLang){
					$ret = $this->gInstance->getFileManager()->getAttachFileInfo(default_contentCommonDef::$_viewContentType, $this->serialNo, $rows);
					if ($ret){
						$this->attachFileInfoArray = array();
						for ($i = 0; $i < count($rows); $i++){
							$attachFileRow = $rows[$i];
							$newInfoObj = new stdClass;
							$newInfoObj->title		= $attachFileRow['af_title'];
							$newInfoObj->filename	= $attachFileRow['af_filename'];
							$newInfoObj->fileId		= $attachFileRow['af_file_id'];
							$this->attachFileInfoArray[] = $newInfoObj;
						}
					}
				}
				
				// 拡張エリアの状態を設定
				if ($hasPassword || !empty($key) || !empty($relatedContent) || count($this->attachFileInfoArray) > 0) $this->isOpenOptionArea = true;
			} else {
				$this->serialNo = 0;
			}
		}
		// 一覧の表示タイプを設定
		if ($this->isMultiLang){		// 多言語対応の場合
			$this->tmpl->setAttribute('show_multilang', 'visibility', 'visible');
			$this->tmpl->addVar("show_multilang", "sel_item_name", $name);		// 名前
			
			if (empty($contentId)){		// 新規追加の場合
				$defaultLangName = $this->gEnv->getDefaultLanguageNameByCurrentLanguage();		// デフォルト言語の現在の表示名を取得
				$this->tmpl->addVar("default_lang", "default_lang", $defaultLangName);
				$this->tmpl->setAttribute('default_lang', 'visibility', 'visible');
			} else {		// コンテンツが選択されているとき
				// 言語選択メニュー作成
				self::$_mainDb->getAvailableLang(array($this, 'langLoop'));
				$this->tmpl->setAttribute('select_lang', 'visibility', 'visible');
				
				// デフォルト言語のみ入力可能フィールド
				if ($this->langId != $this->gEnv->getDefaultLanguage()){
					$this->tmpl->addVar("_widget", "password_disabled", "disabled");// パスワード
					$this->tmpl->addVar("_widget", "key_disabled", "disabled");		// 外部参照用キー
					$this->tmpl->addVar("_widget", "related_content_disabled", "disabled");// 関連コンテンツ
				}
			}
			
			// 言語イメージ
			$langImage = $this->createLangImage($contentId);
			$this->tmpl->addVar("show_multilang", "lang", $langImage);
		} else {
			$this->tmpl->setAttribute('show_singlelang', 'visibility', 'visible');
			$this->tmpl->addVar("show_singlelang", "sel_item_name", $name);		// 名前
		}
		// ユーザ定義フィールドを作成
		$this->createUserFields($fieldInfoArray);
		
		// 添付ファイル一覧作成
		$this->createAttachFileList();
		if (count($this->attachFileInfoArray) == 0) $this->tmpl->setAttribute('attach_file_list', 'visibility', 'hidden');// 添付ファイル一覧を表示
		
		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . 'content_detail';
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadfile';
//		$uploadUrl .= '&path=' . $this->adaptWindowsPath($path);					// アップロードディレクトリ
		$this->tmpl->addVar("_widget", "upload_url", $this->getUrl($uploadUrl));
		
		// jQueryスクリプト、プラグイン一覧作成
		if ($useJQuery){
			// デフォルト言語のみ入力可能
			if ($this->langId == $this->gEnv->getDefaultLanguage()){
				$this->tmpl->setAttribute('show_jquery', 'visibility', 'visible');
				$this->createpluginIdList();
			}
			// ライブラリ追加
			$this->addLib[] = self::LIB_CODEMIRROR_JAVASCRIPT;		// CodeMirror Javascript
		}
		// コンテンツ単位のテンプレート設定を行うかどうか
		if ($useContentTemplate){
			// デフォルト言語のみ入力可能
			if ($this->langId == $this->gEnv->getDefaultLanguage()){
				$this->tmpl->setAttribute('show_template', 'visibility', 'visible');
				
				// テンプレート選択メニュー作成
				self::$_mainDb->getAllTemplateList(default_contentCommonDef::$_deviceType, array($this, 'templateIdLoop'));
			}
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "sel_item_html", $this->convertToDispString($html));		// HTML
		$this->tmpl->addVar("_widget", "desc", $this->convertToDispString($desc));		// 簡易説明
		$this->tmpl->addVar("_widget", "sel_item_key", $this->convertToDispString($key));		// 外部参照用キー
		$this->tmpl->addVar("_widget", "meta_title", $this->convertToDispString($metaTitle));		// ページタイトル名(METAタグ)
		$this->tmpl->addVar("_widget", "meta_desc", $this->convertToDispString($metaDesc));		// ページ要約(METAタグ)
		$this->tmpl->addVar("_widget", "meta_keyword", $this->convertToDispString($metaKeyword));		// ページキーワード(METAタグ)
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($update_user));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($update_dt));	// 更新日時
		$this->tmpl->addVar("_widget", "start_date", $this->convertToDispDate($start_date));	// 公開期間開始日
		$this->tmpl->addVar("_widget", "start_time", $this->convertToDispTime($start_time, 1/*時分*/));	// 公開期間開始時間
		$this->tmpl->addVar("_widget", "end_date", $this->convertToDispDate($end_date));	// 公開期間終了日
		$this->tmpl->addVar("_widget", "end_time", $this->convertToDispTime($end_time, 1/*時分*/));	// 公開期間終了時間
		if ($hasPassword) $this->tmpl->addVar("_widget", "password", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
		$this->tmpl->addVar("_widget", "related_content", $this->convertToDispString($relatedContent));	// 関連コンテンツ
		$this->tmpl->addVar("show_jquery", "jquery_script", $this->convertToDispString($jQueryScript));	// jQueryスクリプト

		// 項目表示、項目利用可否チェックボックス
		$visibleStr = '';
		if ($visible) $visibleStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_visible", $visibleStr);
		$defaultStr = '';
		if ($default) $defaultStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_default", $defaultStr);
		$limitedStr = '';
		if ($limited) $limitedStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_limited", $limitedStr);
	
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);		// 選択中のシリアル番号
		$this->tmpl->addVar("_widget", "target_widget", $this->gEnv->getCurrentWidgetId());// メニュー選択ウィンドウ表示用
		$this->tmpl->addVar("_widget", "device_type", default_contentCommonDef::$_deviceType);		// デバイスタイプ
			
		// パスの設定
		$this->tmpl->addVar('_widget', 'admin_url', $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理者URL

		// プレビュー用URL
		switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
			case 0:		// PC
			default:
				$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
			case 1:		// 携帯
				$previewUrl = $this->gEnv->getDefaultMobileUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
			case 2:		// スマートフォン
				$previewUrl = $this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
		}
		if ($historyIndex >= 0) $previewUrl .= '&' . M3_REQUEST_PARAM_HISTORY . '=' . $historyIndex;
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
		if ($this->isMultiLang) $previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->langId;		// 多言語対応の場合は言語IDを付加
		$this->tmpl->addVar('_widget', 'preview_url', $previewUrl);// プレビュー用URL(一般画面)
		
		$this->tmpl->addVar('_widget', 'custom_value_task', 'usercustom');		// ユーザ定義値参照用
		$this->tmpl->addVar('_widget', 'admin_widget_id', self::ADMIN_WIDGET_ID);// ユーザ定義値参照用(管理ウィジェットのウィジェットID)
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
		
		// 拡張エリア制御
		if ($this->isOpenOptionArea){
			$this->tmpl->addVar('_widget', 'option_area_open', 'true');
		} else {
			$this->tmpl->addVar('_widget', 'option_area_open', 'false');
		}
		$iconUrl = $this->gEnv->getRootUrl() . self::OPEN_PANEL_ICON_FILE;		// 拡張エリア表示用アイコン
		$iconTitle = 'オプション表示';
		$openButton = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::PANEL_BUTTON_SIZE . '" height="' . self::PANEL_BUTTON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$this->tmpl->addVar('_widget', 'open_button', $openButton);
		$iconUrl = $this->gEnv->getRootUrl() . self::CLOSE_PANEL_ICON_FILE;		// 拡張エリア非表示用アイコン
		$iconTitle = 'オプション非表示';
		$closeButton = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::PANEL_BUTTON_SIZE . '" height="' . self::PANEL_BUTTON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$this->tmpl->addVar('_widget', 'close_button', $closeButton);
	
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			if ($this->isMultiLang){		// 多言語対応の場合
				if (empty($contentId)){
					$this->tmpl->addVar("_widget", "sel_item_id", '');			// コンテンツID
					$this->tmpl->addVar("_widget", "item_id", '新規');			// コンテンツID
				} else {
					$this->tmpl->addVar("_widget", "sel_item_id", $contentId);			// コンテンツID
					$this->tmpl->addVar("_widget", "item_id", $contentId);			// コンテンツID
				}
			} else {
				$this->tmpl->addVar("_widget", "sel_item_id", '');			// コンテンツID
				$this->tmpl->addVar("_widget", "item_id", '新規');			// コンテンツID
			}
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
			$this->tmpl->addVar('_widget', 'preview_btn_disabled', 'disabled');// プレビューボタン使用不可
			$this->tmpl->addVar('_widget', 'history_btn_disabled', 'disabled');// 履歴ボタン使用不可
		} else {
			// 履歴番号
			$itemId = $contentId;			// コンテンツID
			if ($historyIndex >= 0) $itemId .= '(' . ($historyIndex +1) . ')';

			$this->tmpl->addVar("_widget", "sel_item_id", $contentId);			// コンテンツID
			$this->tmpl->addVar("_widget", "item_id", $itemId);			// コンテンツID
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
		}
		// 「戻る」ボタンの表示
		if ($openby == 'simple' || $openby == 'tabs') $this->tmpl->setAttribute('cancel_button', 'visibility', 'hidden');		// 詳細画面のみの表示またはタブ表示のときは戻るボタンを隠す
	}
	/**
	 * JavascriptライブラリをHTMLヘッダ部に設定
	 *
	 * JavascriptライブラリをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string,array 				Javascriptライブラリ。出力しない場合は空文字列を設定。
	 */
	function _addScriptLibToHead($request, &$param)
	//function _addAdminScriptLibToHead($request, &$param)
	{
		return $this->addLib;
	}
	/**
	 * コンテンツへのリンクをメニューに追加するための画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createAddToMenu($request)
	{
		// ユーザ情報、表示言語
		$userId = $this->gEnv->getCurrentUserId();
		$this->langId = $this->gEnv->getDefaultLanguage();

		$act = $request->trimValueOf('act');
		$contentId = $request->trimValueOf('contentid');		// コンテンツID
		$serialList = $request->trimValueOf('seriallist');
		if (!empty($serialList)){
			$listedItem = explode(',', $serialList);
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				if ($itemValue) $this->selectedItem[] = $listedItem[$i];// チェック項目
			}
		}
		if ($act == 'addtomenu'){			// メニューに項目を追加
			// URLの作成
			switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
				case 0:		// PC
				default:
					$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/' . $this->gEnv->getDefaultPageId() . '.php?contentid=' . $contentId;
					break;
				case 1:		// 携帯
					$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/' . M3_DIR_NAME_MOBILE . '/' . $this->gEnv->getDefaultPageId() . '.php?contentid=' . $contentId;
					break;
				case 2:		// スマートフォン
					$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/' . M3_DIR_NAME_SMARTPHONE . '/' . $this->gEnv->getDefaultPageId() . '.php?contentid=' . $contentId;
					break;
			}

			// コンテンツ名を取得
			$menutItemName = '';
			$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->langId, $row);
			if ($ret) $menutItemName = $row['cn_name'];		// 名前は取得値を設定

			// メニュー項目追加
			for ($i = 0; $i < count($this->selectedItem); $i++){
				$ret = self::$_mainDb->addMenuItem($this->selectedItem[$i], $menutItemName, $url);
				if (!$ret) break;
			}
			if ($ret){
				// 親ウィンドウを更新
				//$this->gPage->updateParentWindow();		// 設定画面からの小ウィンドウ表示なので親ウィンドウ(設定画面)は更新しない
					
				$this->setGuidanceMsg('メニューにリンクを追加しました');
				$this->completed = true;			// データ登録完了かどうか
			} else {
				$this->setAppErrorMsg('メニューのリンク追加に失敗しました');
			}
		}

		// メニューID選択メニュー作成
		self::$_mainDb->getMenuIdList(default_contentCommonDef::$_deviceType, array($this, 'menuIdListLoop'));
		
		$this->tmpl->addVar("_widget", "content_id", $contentId);// コンテンツID
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// 一覧項目がないときは、一覧を表示しない
		if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
		
		if ($this->completed){// 	データ追加完了のとき
			$this->tmpl->addVar('_widget', 'add_disabled', 'disabled');// 「追加」ボタン
		}
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$serial = $this->convertToDispString($fetchedRow['cn_serial']);
		$contentId = $fetchedRow['cn_id'];		// コンテンツID
		
		// 表示状態
		$visible = '';
		if ($fetchedRow['cn_visible']) $visible = 'checked';

		// ユーザ制限
		$limited = '';
		if ($fetchedRow['cn_user_limited']) $limited = 'checked';

		// デフォルト時の項目かどうか
		$default = '';
		if ($fetchedRow['cn_default']) $default = 'checked';
		
		// 対応言語を取得
		$lang = '';
		if ($this->isMultiLang){		// 多言語対応の場合
			$lang = $this->createLangImage($contentId);
		}
		// 総参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(default_contentCommonDef::$_viewContentType, $serial);
		
		// 公開状況の設定
		$now = date("Y/m/d H:i:s");	// 現在日時
		$startDt = $fetchedRow['cn_active_start_dt'];
		$endDt = $fetchedRow['cn_active_end_dt'];
		
		$isActive = false;		// 公開状態
		if ($fetchedRow['cn_visible']) $isActive = $this->isActive($startDt, $endDt, $now);// 表示可能
		
		if ($isActive){		// コンテンツが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// 操作用ボタン
		$addToMenuImg = $this->getUrl($this->gEnv->getRootUrl() . self::ADD_TO_MENU_ICON_FILE);		// メニューに追加用アイコン
		$addToMenuStr = 'メニューに追加';
		$statusUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;// 現在の表示画面用URL
		
		// プレビュー用URL
		switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
			case 0:		// PC
			default:
				$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
			case 1:		// 携帯
				$previewUrl = $this->gEnv->getDefaultMobileUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
			case 2:		// スマートフォン
				$previewUrl = $this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
		}
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;// プレビュー用URL
//		if ($this->isMultiLang) $previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->langId;		// 多言語対応の場合は言語IDを付加
		$previewImg = $this->getUrl($this->gEnv->getRootUrl() . self::PREVIEW_ICON_FILE);
		$previewStr = 'プレビュー';
		
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($contentId),			// ID
			'name' => $this->convertToDispString($fetchedRow['cn_name']),		// 名前
			'lang' => $lang,													// 対応言語
			'view_count' => $totalViewCount,									// 総参照数
			'status' => $statusImg,												// 公開状況
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_dt' => $this->convertToDispDateTime($fetchedRow['cn_create_dt']),	// 更新日時
			'visible' => $visible,											// メニュー項目表示制御
			'limited' => $limited,											// ユーザ制限
			'default' => $default,											// デフォルト項目
			'add_to_menu_img' => $addToMenuImg,											// メニューに追加用の画像
			'add_to_menu_str' => $addToMenuStr,											// メニューに追加用の文字列
			'status_url' => $statusUrl,											// 現在の表示画面用URL
			'preview_url' => $previewUrl,											// プレビュー用のURL
			'preview_img' => $previewImg,											// プレビュー用の画像
			'preview_str' => $previewStr									// プレビュー文字列
		);
		if ($this->isMultiLang){		// 多言語対応のとき
			$this->tmpl->addVars('itemlist2', $row);
			$this->tmpl->parseTemplate('itemlist2', 'a');
		} else {
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		}
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['cn_serial'];
		
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
	/**
	 * 取得した言語をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function langLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['ln_id'] == $this->langId){
			$selected = 'selected';
		}
		if ($this->gEnv->getCurrentLanguage() == 'ja'){		// 日本語表示の場合
			$name = $this->convertToDispString($fetchedRow['ln_name']);
		} else {
			$name = $this->convertToDispString($fetchedRow['ln_name_en']);
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['ln_id']),			// 言語ID
			'name'     => $name,			// 言語名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('lang_list', $row);
		$this->tmpl->parseTemplate('lang_list', 'a');
		return true;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function menuIdListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['mn_id'];
		
		$checkStr = '';
		if (in_array($id, $this->selectedItem)) $checkStr = 'checked ';
		if ($this->completed) $checkStr .= 'disabled ';			// 追加完了のとき
		
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $this->convertToDispString($id),			// シリアル番号
			'id' => $this->convertToDispString($id),			// ID
			'name' => $this->convertToDispString($fetchedRow['mn_name']),		// 名前
			'check' => $checkStr		// チェック状態
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目IDを保存
		$this->serialArray[] = $id;
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
	/**
	 * キャッシュデータをクリア
	 *
	 * @param int $serial		削除対象のコンテンツシリアル番号
	 * @return					なし
	 */
	function clearCacheBySerial($serial)
	{
		$ret = self::$_mainDb->getContentBySerial($serial, $row);		// コンテンツID取得
		if ($ret){
			$contentId = $row['cn_id'];		// コンテンツID
			$urlParam = array();
			$urlParam[] = M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;		// コンテンツID
			$urlParam[] = M3_REQUEST_PARAM_CONTENT_ID_SHORT . '=' . $contentId;		// コンテンツID略式
			$this->clearCache($urlParam);
		}
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function isActive($startDt, $endDt, $now)
	{
		$isActive = false;		// 公開状態

		if ($startDt == $this->gEnv->getInitValueOfTimestamp() && $endDt == $this->gEnv->getInitValueOfTimestamp()){
			$isActive = true;		// 公開状態
		} else if ($startDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		} else if ($endDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) >= strtotime($startDt)) $isActive = true;		// 公開状態
		} else {
			if (strtotime($startDt) <= strtotime($now) && strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		}
		return $isActive;
	}
	/**
	 * コンテンツ更新情報をサーバへ登録
	 *
	 * @param int $serial		コンテンツシリアル番号
	 * @param bool $isNew		新規または更新
	 * @return					なし
	 */
	function registContentInfoBySerial($serial, $isNew=false)
	{
		$ret = self::$_mainDb->getContentBySerial($serial, $row);		// コンテンツID取得
		if ($ret){
			$contentId = $row['cn_id'];		// コンテンツID
			$contentTitle = $row['cn_name'];		// コンテンツタイトル
			$contentLink = $url = $this->gEnv->getDefaultUrl() . '?contentid=' . $contentId;		// コンテンツリンク先
			$contentDt = $row['cn_create_dt'];		// コンテンツ更新日時
			
			$ret = $this->gInstance->getConnectManager()->registUpdateInfo(''/*汎用コンテンツ*/, ''/*送信先はデフォルトURL*/, ''/*デフォルトウィジェットに送信*/, 
								$this->gEnv->getCurrentWidgetId(), self::MSG_UPDATE_CONTENT, $contentTitle, $contentLink, $contentDt);
		}
		return $ret;
	}
	/**
	 * 言語アイコンを作成
	 *
	 * @param int $contentId	コンテンツID
	 * @return atring			言語アイコンタグ
	 */
	function createLangImage($contentId)
	{
		$imageTag = '';
		$ret = self::$_mainDb->getLangByContentId(default_contentCommonDef::$_contentType, $contentId, $rows);
		if ($ret){
			$count = count($rows);
			for ($i = 0; $i < $count; $i++){
				if ($this->gEnv->getCurrentLanguage() == 'ja'){	// 日本語の場合
					$langName = $rows[$i]['ln_name'];
				} else {
					$langName = $rows[$i]['ln_name_en'];
				}
				// 言語アイコン
				$iconTitle = $langName;
				$iconUrl = $this->gEnv->getRootUrl() . self::LANG_ICON_PATH . $rows[$i]['ln_image_filename'];		// 画像ファイル
				$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
				$imageTag .= $iconTag;
			}
		}
		return $imageTag;
	}
	/**
	 * 言語表示名を取得
	 *
	 * @param string $langId	言語ID
	 * @return atring			表示名
	 */
	function getLangName($langId)
	{
		$ret = self::$_mainDb->getLang($langId, $row);
		if ($this->gEnv->getCurrentLanguage() == 'ja'){		// 日本語表示の場合
			$name = $this->convertToDispString($row['ln_name']);
		} else {
			$name = $this->convertToDispString($row['ln_name_en']);
		}
		return $name;
	}
	/**
	 * 添付ファイル一覧を作成
	 *
	 * @return なし						
	 */
	function createAttachFileList()
	{
		$fileCount = count($this->attachFileInfoArray);
		for ($i = 0; $i < $fileCount; $i++){
			$infoObj = $this->attachFileInfoArray[$i];
			$title = $infoObj->title;		// タイトル
			$filename = $infoObj->filename;		// ファイル名
			$fileId = $infoObj->fileId;		// ファイルID
			
			$row = array(
				'title' => $this->convertToDispString($title),
				'filename' => $this->convertToDispString($filename),
				'file_id' => $this->convertToDispString($fileId),
				'root_url' => $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl()))
			);
			$this->tmpl->addVars('attach_file_list', $row);
			$this->tmpl->parseTemplate('attach_file_list', 'a');
		}
	}
	/**
	 * ユーザ定義フィールドを作成
	 *
	 * @param array $fields			フィールドID
	 * @return bool					true=成功、false=失敗
	 */
	function createUserFields($fields)
	{
		if (count($fields) == 0) return true;
		
		$this->tmpl->setAttribute('user_fields', 'visibility', 'visible');
		$keys = array_keys($fields);
		$fieldCount = count($keys);
		for ($i = 0; $i < $fieldCount; $i++){
			if ($i == 0) $this->tmpl->addVar('user_fields', 'type', 'first');		// 最初の行の場合
			
			// 入力値を取得
			$key = $keys[$i];
			$value = $this->fieldValueArray[$key];
			if (!isset($value)) $value = '';
			
			$row = array(
				'row_count'	=> $fieldCount,
				'field_id'	=> $this->convertToDispString($key),
				'value'		=> $this->convertToDispString($value)
			);
			$this->tmpl->addVars('user_fields', $row);
			$this->tmpl->parseTemplate('user_fields', 'a');
		}
		return true;
	}
	/**
	 * jQueryプラグイン一覧作成
	 *
	 * @return なし
	 */
	function createpluginIdList()
	{
		for ($i = 0; $i < count($this->pluginIdArray); $i++){
			$itemPluginId = str_replace('.', '_', $this->pluginIdArray[$i]);
			$id = $this->pluginIdArray[$i];
			$name = $id;
			
			// ライブラリ情報取得
			$libInfo = $this->gPage->getScriptLibInfo($id);
			if (!empty($libInfo)){
				$url = $libInfo['url'];
				if (!empty($url)) $name = '<a href="' . $this->convertUrlToHtmlEntity($url) . '" target="_blank">' . $name . '</a>';
			}
			
			$checked = '';
			if (in_array($id, $this->selectedPlugin)) $checked = 'checked';

			$row = array(
				'id'    => $itemPluginId,			// 値
				'name'     => $name,			// 名前
				'id_checked' => $checked			// 選択中かどうか
			);
			$this->tmpl->addVars('plugin_list', $row);
			$this->tmpl->parseTemplate('plugin_list', 'a');
		}
	}
	/**
	 * テンプレート一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function templateIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['tm_id'] == $this->templateId) $selected = 'selected';

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['tm_id']),			// テンプレートID
			'name'     => $this->convertToDispString($fetchedRow['tm_name']),			// テンプレート名名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('template_list', $row);
		$this->tmpl->parseTemplate('template_list', 'a');
		return true;
	}
}
?>
