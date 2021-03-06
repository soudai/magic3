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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainWidgetlistWidgetContainer.php 5175 2012-09-07 05:10:38Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath()				. '/pcl/pclzip.lib.php' );
require_once($gEnvManager->getCurrentWidgetContainerPath()		. '/admin_mainDef.php');			// 定義クラス

class admin_mainWidgetlistWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $newWidget = array();		// 新規追加ウィジェット
	private $widgetTypeArray;		// ウィジェットタイプ
	private $widgetType;			// 現在のウィジェットタイプ
	private $showDetail;			// 詳細表示するかどうか
	private $defaultImageSize = 32;		// ウィジェット画像サイズ
	private $isExistsWidgetList;		// ウィジェットが存在するかどうか
	const SCRIPT_FILE_EXT = 'js';		// JavaScriptファイル拡張子
	const CSS_FILE_EXT = 'css';		// cssファイル拡張子
	const PHP_FILE_EXT = 'php';		// phpファイル拡張子
	const NOT_FOUND_WIDGET_ICON_FILE = '/images/system/notfound32.png';		// ウィジェットが見つからないアイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		// ウィジェットタイプメニュー項目
		$this->widgetTypeArray = array(	array(	'name' => $this->_('For PC'),			'value' => '0'),	// PC用
										array(	'name' => $this->_('For Mobile'),		'value' => '1'),	// 携帯用
										array(	'name' => $this->_('For Smartphone'),	'value' => '2'));	// スマートフォン用
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
		return 'widgetlist.tmpl.html';
	}
	/**
	 * ヘルプデータを設定
	 *
	 * ヘルプの設定を行う場合はヘルプIDを返す。
	 * ヘルプデータの読み込むディレクトリは「自ウィジェットディレクトリ/include/help」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ヘルプID。ヘルプデータはファイル名「help_[ヘルプID].php」で作成。ヘルプを使用しない場合は空文字列「''」を返す。
	 */
	function _setHelp($request, &$param)
	{	
		return 'widgetlist';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
		$selectedItemNo = $request->trimValueOf('no');		// 処理対象の項目番号
		$serial = $request->trimValueOf('serial');		// シリアルNo
		$widgetId = $request->trimValueOf('widget');		// 処理対象のウィジェット
		$this->widgetType = $request->trimValueOf('item_type');// 現在のウィジェットタイプ
		if ($this->widgetType == '') $this->widgetType = '0';		// デフォルトはPC用ウィジェット
		$this->showDetail = ($request->trimValueOf('item_show_detail') == 'on') ? 1 : 0;		// 詳細表示するかどうか
				
		if ($act == 'readnew'){		// ウィジェット再読み込みのとき
			$addWidgetCount = 0;
			// ウィジェット一覧取得
			if ($this->db->getAllWidgetIdList($rows)){
				// ウィジェットディレクトリチェック
				switch ($this->widgetType){
					case '0':		// PC用テンプレート
					default:
						$searchPath = $this->gEnv->getWidgetsPath();
						break;
					case '1':		// 携帯用テンプレート
						$searchPath = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_MOBILE;
						break;
					case '2':		// スマートフォン用テンプレート
						$searchPath = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_SMARTPHONE;
						break;
				}
				
				if (is_dir($searchPath)){
					$dir = dir($searchPath);
					while (($file = $dir->read()) !== false){
						$filePath = $searchPath . '/' . $file;
						// ディレクトリかどうかチェック
						if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath)
								&& strncmp($file, '_', 1) != 0		// 「_」で始まる名前のディレクトリは読み込まない
								&& strlen($file) > 1){		// 1文字のディレクトリは読み込まない
							// ウィジェットIDを作成
							switch ($this->widgetType){
								case '0':		// PC用テンプレート
								default:
									$widgetId = $file;
									break;
								case '1':		// 携帯用テンプレート
									$widgetId = M3_DIR_NAME_MOBILE . '/' . $file;
									break;
								case '2':		// スマートフォン用テンプレート
									$widgetId = M3_DIR_NAME_SMARTPHONE . '/' . $file;
									break;
							}
							
							// DBに登録されていない場合は登録
							for ($i = 0; $i < count($rows); $i++){
								if ($widgetId == $rows[$i]['wd_id']) break;
							}
							if ($i == count($rows)){
								// ディレクトリ内のスクリプト、CSSの状況を取得
								$ret = $this->getDirStatus($filePath, $hasScripts, $hasCss, $hasAdmin);
								
								// ウィジェットを登録
								if ($ret){
									$this->db->addNewWidget($widgetId, $file, intval($this->widgetType), $hasScripts, $hasCss, $hasAdmin);
								} else {
									$this->db->addNewWidget($widgetId, $file, intval($this->widgetType));
								}
								$this->newWidget[] = $widgetId;			// 新規ウィジェットID保存
								$addWidgetCount++;		// ウィジェット追加
							}
						}
					}
					$dir->close();
				}
			} else {
			}
			// 終了メッセージを表示
			if ($addWidgetCount > 0){
				//$msg = '新規ウィジェットを追加しました(追加数=' . $addWidgetCount . ')';
				$msg = sprintf($this->_('New widgets added. (widgets count=%d)'), $addWidgetCount);			// 新規ウィジェットを追加しました(追加数=%d)
			} else {
				//$msg = '新規ウィジェットはありません';
				$msg = $this->_('No new widgets added.');		// 新規ウィジェットはありません
			}
			$this->setMsg(self::MSG_GUIDANCE, $msg);
		} else if ($act == 'updateline'){		// 行更新のとき
			// 変更可能値
			$updateName = $request->trimValueOf('item' . $selectedItemNo . '_name');				// 名前
			$updateAvailable = ($request->trimValueOf('item' . $selectedItemNo . '_available') == 'on') ? 1 : 0;		// 利用可能かどうか
			$updateActive = ($request->trimValueOf('item' . $selectedItemNo . '_active') == 'on') ? 1 : 0;		// ウィジェット実行可能かどうか
			
			$ret = $this->db->updateWidget($serial, $updateName, $updateAvailable, $updateActive);
			if ($ret){		// データ更新成功のとき
				//$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Line updated.'));		// データを更新しました
			} else {
				//$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating line.'));	// データ更新に失敗しました
			}
		} else if ($act == 'deleteline'){		// ウィジェット削除のとき
			// ウィジェットが参照状況をチェック、参照されている場合は削除できない
			if ($this->db->getWidget($serial, $row)){
				// ウィジェットディレクトリ取得
				$widgetId = $row['wd_id'];
				$widgetPath = $this->gEnv->getWidgetsPath() . '/' . $widgetId;
				
				// ウィジェットディレクトリの削除権限のチェック
				if (is_writable($widgetPath) || !is_dir($widgetPath)){		// 削除可能か、すでにディレクトリがないとき
					// インストール初期化フラグを一旦リセット
					$this->db->updateIsWidgetInitialized($widgetId, false/*未初期化*/);
											
					// ウィジェットのアンインストール処理
					$saveWidgetId = $this->gEnv->getCurrentWidgetId();// ウィジェットID保存
					$this->gEnv->setCurrentWidgetId($widgetId);// ウィジェットID一時設定
					$this->gLaunch->goInstallWidget(1);		// アンインストール
					$this->gEnv->setCurrentWidgetId($saveWidgetId);// ウィジェットID戻す

					// エラーメッセージを取得
					$this->addMsg($this->gInstance->getMessageManager()->getErrorMessage(), 
									$this->gInstance->getMessageManager()->getWarningMessage(), $this->gInstance->getMessageManager()->getGuidanceMessage());
														
					// ディレクトリ削除
					if ((is_dir($widgetPath) && rmDirectory($widgetPath)) || !is_dir($widgetPath)){// 削除成功か、ディレクトリが存在しないとき
						$ret = $this->db->deleteWidget($serial);
						//$this->setMsg(self::MSG_GUIDANCE, 'ウィジェットを削除しました(ウィジェットID：' . $widgetId . ')');
						$this->setMsg(self::MSG_GUIDANCE, sprintf($this->_('Widget deleted. (widget ID: %s)'), $widgetId));	// ウィジェットを削除しました(ウィジェットID：%s)
					} else {
						//$this->setMsg(self::MSG_APP_ERR, 'ウィジェットのディレクトリが削除できませんでした(ディレクトリ：' . $widgetPath . ')');
						$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting widget directory. (directory: %s)'), $widgetPath));	// ウィジェットのディレクトリが削除できませんでした(ディレクトリ：%s)
					}
				} else {
					//$this->setMsg(self::MSG_APP_ERR, 'ウィジェットのディレクトリの削除権限がありません(ディレクトリ：' . $widgetPath . ')');
					$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('You are not allowed to delete widget directory. (directory: %s)'), $widgetPath));		// ウィジェットのディレクトリの削除権限がありません(ディレクトリ：%s)
				}
			} else {
				//$this->setMsg(self::MSG_APP_ERR, '削除対象のウィジェットが見つかりません');
				$this->setMsg(self::MSG_APP_ERR, $this->_('Widget not found.'));		// 削除対象のウィジェットが見つかりません
			}
		} else if ($act == 'upload'){		// ファイルアップロードの場合
			$replaceWidget = ($request->trimValueOf('item_replace') == 'on') ? 1 : 0;		// ウィジェットを置き換えるかどうか

			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得
				
				// ファイル名の解析
				$pathParts = pathinfo($uploadFilename);
				$ext = $pathParts['extension'];		// 拡張子
				$widgetName = basename($uploadFilename, '.' . $ext);		// 拡張子をはずす
				$widgetId = $widgetName;
			
				// ウィジェットIDを修正
				switch ($this->widgetType){
					case '0':		// PC用テンプレート
					default:
						break;
					case '1':		// 携帯用テンプレート
						$widgetId = M3_DIR_NAME_MOBILE . '/' . $widgetId;
						break;
					case '2':		// スマートフォン用テンプレート
						$widgetId = M3_DIR_NAME_SMARTPHONE . '/' . $widgetId;
						break;
				}
				
				// ファイル拡張子のチェック
				if ($ext != 'zip'){
					//$msg = 'zip圧縮のファイルのみアップロード可能です';
					$msg = $this->_('Only zip format file is allowed to upload.');	// zip圧縮のファイルのみアップロード可能です
					$this->setAppErrorMsg($msg);
				}
				
				// テンポラリディレクトリの書き込み権限をチェック
				if (!is_writable($this->gEnv->getWorkDirPath())){
					//$msg = '一時ディレクトリに書き込み権限がありません。ディレクトリ：' . $this->gEnv->getWorkDirPath();
					$msg = sprintf($this->_('You are not allowed to write temporary directory. (directory: %s)'), $this->gEnv->getWorkDirPath());	// 一時ディレクトリに書き込み権限がありません。(ディレクトリ：%s)
					$this->setAppErrorMsg($msg);
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					if (!$replaceWidget){		// ウィジェット置き換えないとき
						// 同じIDのウィジェットがないかチェック
						if ($this->db->isExistsWidgetId($widgetId)){
							//$msg = 'ウィジェットがすでに存在します(ウィジェットID：' . $widgetId . ')';
							$msg = sprintf($this->_('The widget already exists. (widget ID: %s)'), $widgetId);		// ウィジェットがすでに存在します(ウィジェットID：%s)
							$this->setAppErrorMsg($msg);
						}
					}
				}
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);

					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// 解凍先ディレクトリ
						switch ($this->widgetType){
							case '0':		// PC用テンプレート
							default:
								$extDir = $this->gEnv->getWidgetsPath();
								break;
							case '1':		// 携帯用テンプレート
								$extDir = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_MOBILE;
								break;
							case '2':		// スマートフォン用テンプレート
								$extDir = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_SMARTPHONE;
								break;
						}
					
						// zipファイルを解凍
						$zipFile = new PclZip($tmpFile);
						if (($zipList = $zipFile->listContent()) == 0){
							//$msg = 'zipファイルの内容のリスト取得に失敗しました(要因: ' . $zipFile->errorName(true) . ')';
							$msg = sprintf($this->_('Failed in getting file list from zip file. (detail: %s)'), $zipFile->errorName(true));		// zipファイルの内容のリスト取得に失敗しました(要因: %s)
							$this->setAppErrorMsg($msg);
						} else {
							// zipファイル名とディレクトリ名が同じであるかチェック
							$dirName = $widgetName . '/';
							if (strncmp($zipList[0]['filename'], $dirName, strlen($dirName)) == 0){
								$widgetPath = $this->gEnv->getWidgetsPath() . '/' . $widgetId;// ウィジェットのディレクトリ
								if ($replaceWidget){		// ウィジェット置き換える場合
									// ディレクトリ削除
									if ((is_dir($widgetPath) && rmDirectory($widgetPath)) || !is_dir($widgetPath)){// 削除成功か、ディレクトリが存在しないとき
									} else {
										//$this->setMsg(self::MSG_APP_ERR, 'ウィジェットのディレクトリが削除できませんでした');
										$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in deleting widget directory.'));	// ウィジェットのディレクトリが削除できませんでした
									}
								}
								
								if ($this->getMsgCount() == 0){		// エラーが発生していないとき					
									$ret = $zipFile->extract(PCLZIP_OPT_PATH, $extDir);
									if ($ret){
										// ウィジェット新規登録のときだけウィジェット登録処理を実行
										// ウィジェット情報が登録されているかチェック
										$registWidget = false;			// ウィジェットを登録するかどうか
										if (!$replaceWidget || ($replaceWidget && !$this->db->isExistsWidgetId($widgetId))) $registWidget = true;
										
										// ウィジェットの新規登録。ウィジェット情報がない場合は作成しておく
										if ($registWidget){
											// ウィジェットの初期化状態を取得
											// ウィジェットが初期化されている場合はデータをそのまま残す→スクリプトを実行しない
											$initialized = $this->db->isWidgetInitialized($widgetId);
										
											// ディレクトリ内のスクリプト、CSSの状況を取得
											$ret = $this->getDirStatus($widgetPath, $hasScripts, $hasCss, $hasAdmin);
										
											// ウィジェットを登録
											if ($ret){
												$ret = $this->db->addNewWidget($widgetId, $widgetName, intval($this->widgetType), $hasScripts, $hasCss, $hasAdmin);
											} else {
												$ret = $this->db->addNewWidget($widgetId, $widgetName, intval($this->widgetType));
											}
											
											if ($initialized) $this->db->updateIsWidgetInitialized($widgetId, true/*初期化済み*/);
											
											// インストールタイプ
											$installType = 0;		// インストール
										} else {
											// インストール初期化フラグを一旦リセット
											$this->db->updateIsWidgetInitialized($widgetId, false/*未初期化*/);
											
											// インストールタイプ
											$installType = 2;		// アップデート
										}
										
										// ウィジェットのインストール処理
										$saveWidgetId = $this->gEnv->getCurrentWidgetId();// ウィジェットID保存
										$this->gEnv->setCurrentWidgetId($widgetId);// ウィジェットID一時設定
										//$this->gLaunch->goInstallWidget(0);		// インストール
										$this->gLaunch->goInstallWidget($installType);		// インストール
										$this->gEnv->setCurrentWidgetId($saveWidgetId);// ウィジェットID戻す
									
										// インストールが完了したときは初期化済みに設定
										$this->db->updateIsWidgetInitialized($widgetId, true/*初期化済み*/);
										
										// エラーメッセージを取得
										$this->addMsg($this->gInstance->getMessageManager()->getErrorMessage(), 
														$this->gInstance->getMessageManager()->getWarningMessage(), $this->gInstance->getMessageManager()->getGuidanceMessage());
							
										// バージョンを通知
										if ($this->getMsgCount(self::MSG_APP_ERR) == 0 && $this->getMsgCount(self::MSG_USER_ERR) == 0){		// エラーが発生していないとき
											// バージョン取得
											$ret = $this->_db->getWidgetInfo($widgetId, $row);
											if ($ret) $version = $row['wd_version'];		// ウィジェットのバージョン
											
											switch ($installType){
												case 0:		// インストール
													//$msg = 'ウィジェットのインストールが完了しました。';
													$msg = $this->_('Widget installed successfully.');		// ウィジェットのインストールが完了しました。
													break;
												case 2:		// アップデート
													//$msg = 'ウィジェットの更新が完了しました。';
													$msg = $this->_('Widget updated successfully.');		// ウィジェットの更新が完了しました。
													break;
											}
											//$msg .= 'バージョンは' . $version . 'です。';
											$msg .= ' ' . sprintf($this->_('Current version is %s.'), $version);			// バージョンは$sです。
											$this->setGuidanceMsg($msg);
											
											//$msg = 'ファイルのアップロードが完了しました(ウィジェットID：' . $widgetId . ')';
											$msg = sprintf($this->_('File uploaded. (widget ID: %s)'), $widgetId);		// ファイルのアップロードが完了しました(ウィジェットID: %s)
											$this->setGuidanceMsg($msg);
										} else {
											//$msg = 'ファイルのアップロードに失敗しました';
											$msg = $this->_('Failed in uploading file.');		// ファイルのアップロードに失敗しました
											$this->setAppErrorMsg($msg);
										}

										$this->newWidget[] = $widgetId;			// 新規ウィジェットID保存
									} else {
										//$msg = 'ファイルのアップロードに失敗しました(要因: ' . $zipFile->errorName(true) . ')';
										$msg = sprintf($this->_('Failed in uploading file. (detail: %s)'), $zipFile->errorName(true));	// ファイルのアップロードに失敗しました(要因: %s)
										$this->setAppErrorMsg($msg);
									}
								}
							} else {
								//$msg = 'zipファイルのファイル名とディレクトリ名が異なっているか、全角文字が含まれています';
								$msg = $this->_('The zip filename is different from directory name.');			// zipファイルのファイル名とディレクトリ名が異なっているか、全角文字が含まれています
								$this->setAppErrorMsg($msg);
							}
						}
					} else {
						//$msg = 'ファイルのアップロードに失敗しました';
						$msg = $this->_('Failed in uploading file.');		// ファイルのアップロードに失敗しました
						$this->setAppErrorMsg($msg);
					}
					// テンポラリファイル削除
					unlink($tmpFile);
				}
			} else {
				//$msg = 'アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります - ' . $this->gSystem->getMaxFileSizeForUpload() . 'バイト)';
				$msg = sprintf($this->_('Uploded file not found. (detail: The file may be over maximum size to be allowed to upload. Size %s bytes.'), $this->gSystem->getMaxFileSizeForUpload());	// アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。%sバイト)
				$this->setAppErrorMsg($msg);
			}
		} else if ($act == 'download'){		// ファイルダウンロードのとき
			switch ($this->widgetType){
				case '0':		// PC用テンプレート
				default:
					$widgetsDir = $this->gEnv->getWidgetsPath();		// ウィジェットディレクトリ
					$widgetDir = $widgetsDir . '/' . $widgetId;		// ダウンロードするウィジェットのディレクトリ
					$downloadFilename = $widgetId . '.zip';				// ダウンロード時のファイル名
					break;
				case '1':		// 携帯用テンプレート
					$widgetsDir = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_MOBILE;		// ウィジェットディレクトリ
					$widgetDir = $this->gEnv->getWidgetsPath() . '/' . $widgetId;				// ダウンロードするウィジェットのディレクトリ
					list($dir, $filename) = explode('/', $widgetId);		// 先頭の「m/」を削除
					$downloadFilename = $filename . '.zip';					// ダウンロード時のファイル名
					break;
				case '2':		// スマートフォン用テンプレート
					$widgetsDir = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_SMARTPHONE;		// ウィジェットディレクトリ
					$widgetDir = $this->gEnv->getWidgetsPath() . '/' . $widgetId;				// ダウンロードするウィジェットのディレクトリ
					list($dir, $filename) = explode('/', $widgetId);		// 先頭の「s/」を削除
					$downloadFilename = $filename . '.zip';					// ダウンロード時のファイル名
					break;
			}
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD);		// zip処理用一時ファイル
			
			// zip圧縮
			$zipFile = new PclZip($tmpFile);
			$ret = $zipFile->create($widgetDir, PCLZIP_OPT_REMOVE_PATH, $widgetsDir);
			if ($ret){
				// ページ作成処理中断
				$this->gPage->abortPage();
				
				// ダウンロード処理
				$ret = $this->gPage->downloadFile($tmpFile, $downloadFilename, true/*実行後ファイル削除*/);
				
				// システム強制終了
				$this->gPage->exitSystem();
			} else {
				//$msg = 'ファイルのダウンロードに失敗しました(要因: ' . $zipFile->errorName(true) . ')';
				$msg = sprintf($this->_('Failed in downloading file. (detail: %s)'), $zipFile->errorName(true));		// ファイルのダウンロードに失敗しました(要因: %s)
				$this->setAppErrorMsg($msg);
				
				// テンポラリファイル削除
				unlink($tmpFile);
			}
		} else if ($act == 'changedetail'){		// 詳細表示の変更のとき
			// 画面設定値を更新
			$this->gDisp->setAdminConfig(admin_mainDef::CFG_SHOW_WIDGET_DETAIL, strval($this->showDetail));
		}
		// 詳細設定状況を再取得
		$this->showDetail = intval($this->gDisp->getAdminConfig(admin_mainDef::CFG_SHOW_WIDGET_DETAIL));
		
		// ウィジェットのタイプごとの処理
		switch ($this->widgetType){
			case '0':		// PC用テンプレート
			default:
				$installDir = $this->gEnv->getWidgetsPath();// ウィジェット格納ディレクトリ
				break;
			case '1':		// 携帯用テンプレート
				$installDir = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_MOBILE;// ウィジェット格納ディレクトリ
				break;
			case '2':		// スマートフォン用テンプレート
				$installDir = $this->gEnv->getWidgetsPath() . '/' . M3_DIR_NAME_SMARTPHONE;// ウィジェット格納ディレクトリ
				break;
		}

		// 表示制御
		if (!empty($this->showDetail)){		// 詳細表示のとき
			$this->tmpl->setAttribute('show_dir', 'visibility', 'visible');// ディレクトリ表示
			$this->tmpl->setAttribute('show_list_detail', 'visibility', 'visible');// 一覧を詳細表示
		}
		// ウィジェットタイプ選択メニュー作成
		$this->createWidgetTypeMenu();
		
		// ウィジェットリストを取得
		$this->db->getAllWidgetList(intval($this->widgetType), array($this, 'widgetListLoop'));
		if (!$this->isExistsWidgetList) $this->tmpl->setAttribute('widgetlist', 'visibility', 'hidden');// ウィジェットがないときは、一覧を表示しない
		
		// 画面にデータを埋め込む
		$checkedStr = '';
		if (!empty($this->showDetail)) $checkedStr = 'checked';
		$this->tmpl->addVar("_widget", "show_detail", $checkedStr);		// 詳細表示
		$this->tmpl->addVar("show_dir", "install_dir", $installDir);// インストールディレクトリを設定
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_update_line'] = $this->_('Update line?');		// 行を更新しますか?
		$localeText['msg_delete_line'] = $this->_('Delete widget?');		// このウィジェットを削除しますか?
		$localeText['msg_no_upload_file'] = $this->_('File not selected.');		// アップロードするファイルが選択されていません
		$localeText['msg_upload_file'] = $this->_('Upload file.');		// ファイルをアップロードします
		$localeText['label_widget_list'] = $this->_('Widget List');			// ウィジェット一覧
		$localeText['label_widget_type'] = $this->_('Widget Type:');			// ウィジェットタイプ：
		$localeText['label_install_dir'] = $this->_('Install Directory:');			// インストールディレクトリ:
		$localeText['label_read_new'] = $this->_('Reload directory');			// ディレクトリ再読み込み
		$localeText['label_show_detail'] = $this->_('Show detail');			// 詳細表示
		$localeText['label_widget_name'] = $this->_('Name');			// 名前
		$localeText['label_widget_version'] = $this->_('Version');			// バージョン
		$localeText['label_widget_available'] = $this->_('Available');			// 配置可
		$localeText['label_widget_active'] = $this->_('Active');			// 実行可
		$localeText['label_widget_date'] = $this->_('Release Date');			// リリース日
		$localeText['label_widget_operation'] = $this->_('Operation');			// 操作
		$localeText['label_widget_upload'] = $this->_('Widget Upload (zip compressed file)');			// ウィジェットアップロード(zip圧縮ファイル)
		$localeText['msg_replace_widget'] = $this->_('Replace widget if exists.');			// ウィジェットが存在する場合は置き換え
		$localeText['label_upload'] = $this->_('Upload');			// アップロード
		$this->setLocaleText($localeText);
	}
	/**
	 * ウィジェットのディレクトリの状態を取得
	 *
	 * @param string $path			ウィジェットディレクトリパス
	 * @param bool $hasScripts		スクリプトファイルが存在するかどうか
	 * @param bool $hasCss			CSSファイルが存在するかどうか
	 * @param bool $hasAdmin		管理画面があるかどうか
	 * @return bool 				true=取得成功、false=取得失敗
	 */
	function getDirStatus($path, &$hasScripts, &$hasCss, &$hasAdmin)
	{
		// 戻り値初期化
		$ret = true;
		$hasScripts = false;
		$hasCss = false;
		$hasAdmin = false;
	
		// スクリプトディレクトリをチェック
		$searchPath = $path . '/' . M3_DIR_NAME_SCRIPTS;
		if (is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
				$pathParts = pathinfo($file);
				$ext = $pathParts['extension'];		// 拡張子
					
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0 &&	// 「_」で始まる名前のファイルは読み込まない
					$ext == self::SCRIPT_FILE_EXT){		// 拡張子をチェック
					$hasScripts = true;
					break;
				}
			}
			$dir->close();
		}
		// CSSディレクトリをチェック
		$searchPath = $path . '/' . M3_DIR_NAME_CSS;
		if (is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
				$pathParts = pathinfo($file);
				$ext = $pathParts['extension'];		// 拡張子
					
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0 &&	// 「_」で始まる名前のファイルは読み込まない
					$ext == self::CSS_FILE_EXT){		// 拡張子をチェック
					$hasCss = true;
					break;
				}
			}
			$dir->close();
		}
		// adminディレクトリをチェック
		$searchPath = $path . '/' . M3_DIR_NAME_ADMIN;
		if (is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
				$pathParts = pathinfo($file);
				$ext = $pathParts['extension'];		// 拡張子
					
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0 &&	// 「_」で始まる名前のファイルは読み込まない
					$ext == self::PHP_FILE_EXT){		// 拡張子をチェック
					$hasAdmin = true;
					break;
				}
			}
			$dir->close();
		}
		return $ret;
	}
	/**
	 * タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createWidgetTypeMenu()
	{
		for ($i = 0; $i < count($this->widgetTypeArray); $i++){
			$value = $this->widgetTypeArray[$i]['value'];
			$name = $this->widgetTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->widgetType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_type_list', $row);
			$this->tmpl->parseTemplate('item_type_list', 'a');
		}
	}
	/**
	 * ウィジェットリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function widgetListLoop($index, $fetchedRow, $param)
	{
		// ウィジェットが存在するかどうかチェック
		$isExistsWidget = false;
		$widgetId = $fetchedRow['wd_id'];// ウィジェットID
		$widgetDir = $this->gEnv->getWidgetsPath() . '/' . $widgetId;			// ウィジェットのディレクトリ
		if (file_exists($widgetDir)) $isExistsWidget = true;
		
		// 詳細表示の設定
		if ($this->showDetail){
			$this->tmpl->addVar('widgetlist', 'widgettype', 'detail');		// 詳細表示
		}
		
		$available = '';				// 利用可能かどうか
		if ($fetchedRow['wd_available']){
			$available = 'checked';
		}
		$active = '';			// ウィジェット実行可能かどうか
		if ($fetchedRow['wd_active']){
			$active = 'checked';
		}
		
		// 編集不可項目のときは、ボタンを使用不可にする
		$buttonEnabled = '';
		$availableDisabled = '';
		$activeDisabled = '';
		if (!$fetchedRow['wd_editable']){
			$buttonEnabled = 'disabled';
			$availableDisabled = 'disabled';
			$activeDisabled = 'disabled';
		}
		
		// 管理画面がないときは、詳細ボタンを使用不可にする
		$detailButtonEnabled = '';
		if (!$fetchedRow['wd_has_admin']) $detailButtonEnabled = 'disabled';
		
		// 新規に追加されたウィジェットかチェック
		$idText = $this->convertToDispString($widgetId);
		for ($i = 0; $i < count($this->newWidget); $i++){
			if ($this->newWidget[$i] == $widgetId){
				$idText = '<b><font color="green">' . $this->convertToDispString($widgetId) . '</font></b>';
				break;
			}
		}
		// ウィジェットの画像を設定
		if ($isExistsWidget){		// ウィジェットが存在するとき
			$iconTitle = '';
			$iconUrl = $this->gDesign->getWidgetIconUrl($widgetId, $this->defaultImageSize);
		} else {
			//$iconTitle = self::NOT_FOUND_WIDGET_MESSAGE;		// ウィジェットが見つかりません
			$iconTitle = $this->_('Widget not found.');		// ウィジェットが見つかりません
			$iconUrl = $this->gEnv->getRootUrl() . self::NOT_FOUND_WIDGET_ICON_FILE;
		}
		$imageTag = '<img class="widget_obj" src="' . $this->getUrl($iconUrl) . '" ';
		$imageTag .= 'width="' . $this->defaultImageSize . '"';
		$imageTag .= ' height="' . $this->defaultImageSize . '"';
		$imageTag .= ' border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// ヘルプの作成
		$helpText = '';
		$title = $fetchedRow['wd_name'];
		if (!empty($title)){
			$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $fetchedRow['wd_description']);
		}
		$idText = '<span ' . $helpText . '>' . $idText . '</span>';
				
		// ボタンの状態
		$downloadButton = '';
		if (!$isExistsWidget) $downloadButton = 'disabled';
		if (!empty($fetchedRow['wd_license_type']))  $downloadButton = 'disabled';// ライセンスのチェック
		
		$row = array(
			'no' => $index + 1,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['wd_serial']),			// シリアル番号
			'id' => $this->convertToDispString($widgetId),			// ID
			'id_text' => $idText,
			'name' => $this->convertToDispString($fetchedRow['wd_name']),		// 名前
			'version' => $this->convertToDispString($fetchedRow['wd_version']),		// バージョン
			'release_dt' => $this->convertToDispDate($fetchedRow['wd_release_dt']),	// リリース日時
			'available' => $available,												// 利用可能かどうか
			'active' => $active,													// ウィジェット実行可能かどうか
			'available_disabled' => $availableDisabled,							// 利用可能かどうか、使用制御
			'active_disabled' => $activeDisabled,									// ウィジェット実行可能かどうか、使用制御
			'update_button' => $buttonEnabled,									// 更新ボタンの使用制御
			'delete_button' => $buttonEnabled,									// 削除ボタンの使用制御
			'detail_button' => $detailButtonEnabled,							// 詳細ボタンの使用制御
			'download_button' => $downloadButton,								// ダウンロードボタン
			'image_tag' => $imageTag,		// 画像
			'label_widget_config' => $this->_('Configure'),			// 設定
			'label_update' => $this->_('Update'),			// 更新
			'label_delete' => $this->_('Delete'),			// 削除
			'label_download' => $this->_('Download')			// ダウンロード
		);
		$this->tmpl->addVars('widgetlist', $row);
		$this->tmpl->parseTemplate('widgetlist', 'a');
		
		$this->isExistsWidgetList = true;		// ウィジェットが存在する
		return true;
	}
}
?>
