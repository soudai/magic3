<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainWidgetContainer.php 6132 2013-06-25 05:29:46Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $redirectUrl;		// リダイレクト先URL
	private $content;			// メッセージ用コンテンツ
	const CF_USE_CONTENT_MAINTENANCE = 'use_content_maintenance';		// メンテナンス画面に汎用コンテンツを使用するかどうか
	const CF_USE_CONTENT_ACCESS_DENY = 'use_content_access_deny';		// アクセス不可画面に汎用コンテンツを使用するかどうか
	const CF_USE_CONTENT_PAGE_NOT_FOUND = 'use_content_page_not_found';		// 存在しないページ画面に汎用コンテンツを使用するかどうか

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * ディスパッチ処理(メインコンテナのみ実行)
	 *
     * HTTPリクエストの内容を見て処理をコンテナに振り分ける
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return bool 						このクラスの_setTemplate(), _assign()へ処理を継続するかどうかを返す。
	 *                                      true=処理を継続、false=処理を終了
	 */
	function _dispatch($request, &$param)
	{
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$url = $request->trimValueOf('url');			// リダイレクト先URL
		
		// システム制御画面を表示する場合
		// ログインの場合はログイン処理へ
		$systemMode = $this->gPage->getSystemHandleMode();		// システム制御モード(0=設定なし、1=ログイン画面、10=サイト非公開、11=アクセス不可)
		if ($systemMode > 0 &&
				$cmd != M3_REQUEST_CMD_LOGOUT && 				// ログアウトはスルーして後のログアウト部分で処理
				($cmd != M3_REQUEST_CMD_LOGIN || ($cmd == M3_REQUEST_CMD_LOGIN && $request->isGetMethod()))){	// GETで来たログインコマンドはログインとしない
			if ($systemMode >= 10){			// サイト非公開、アクセス不可のとき
				// 画面を作成
				switch ($systemMode){
					case 10:		// システムメンテナンスのとき
						$useContentKey = self::CF_USE_CONTENT_MAINTENANCE;	// コンテンツを使用するかどうか
						$contentKey = M3_CONTENT_KEY_MAINTENANCE;	// コンテンツ取得用キー
						break;
					case 11:		// アクセス不可のとき
						$useContentKey = self::CF_USE_CONTENT_ACCESS_DENY;	// コンテンツを使用するかどうか
						$contentKey = M3_CONTENT_KEY_ACCESS_DENY;	// コンテンツ取得用キー
						break;
					case 12:		// 存在しないページのとき
						$useContentKey = self::CF_USE_CONTENT_PAGE_NOT_FOUND;	// コンテンツを使用するかどうか
						$contentKey = M3_CONTENT_KEY_PAGE_NOT_FOUND;	// コンテンツ取得用キー
						break;
					default:
						$param = 'message';			// メッセージ画面表示
						return true;
				}

				$createMessage = false;		// メッセージを作成したかどうか
				$value = $this->gSystem->getSystemConfig($useContentKey);
				if (!empty($value)){		// 汎用コンテンツを使用するとき
					// コンテンツタイプを取得
					$contentType = $this->getContentType();
				
					$db = new admin_mainDb();
					$ret = $db->getContentByKey($contentType, $this->gEnv->getCurrentLanguage(), $contentKey, $row);
					if ($ret && !empty($row['cn_html'])){
						$createMessage = true;		// メッセージを作成したかどうか

						$this->content = $row['cn_html'];
						$this->headTitle = $row['cn_meta_title'];
						$this->headDesc = $row['cn_meta_description'];
						$this->headKeyword = $row['cn_meta_keywords'];
						$param = 'content';			// コンテンツ画面表示
					}
				}
				if (!$createMessage){		// メッセージを作成したかどうか
					// 現在の言語でメッセージ取得
					switch ($systemMode){
						case 10:	// システムメンテナンスのとき
							$msg = $this->gInstance->getMessageManager()->getMessage(MessageManager::MSG_SITE_IN_MAINTENANCE);
							break;
						case 11:	// アクセス不可のとき					
							$msg = $this->gInstance->getMessageManager()->getMessage(MessageManager::MSG_ACCESS_DENY);
							break;
						case 12:	// 存在しないページのとき
							$msg = $this->gInstance->getMessageManager()->getMessage(MessageManager::MSG_PAGE_NOT_FOUND);
							break;
					}
					$this->SetMsg(self::MSG_APP_ERR, $msg);
					$param = 'message';			// メッセージ画面表示
				}
			} else if ($cmd == M3_REQUEST_CMD_PREVIEW || $cmd == M3_REQUEST_CMD_LOGIN){		// プレビュー画面への遷移のとき
				$param = 'userlogin';			// ユーザログイン画面表示

				// ログイン、ログアウトコマンドを削除したURL先へ遷移
				$removeParam = array(	array('key' => M3_REQUEST_PARAM_OPERATION_COMMAND, 'value' => M3_REQUEST_CMD_LOGIN),
										array('key' => M3_REQUEST_PARAM_OPERATION_COMMAND, 'value' => M3_REQUEST_CMD_LOGOUT));
				$this->redirectUrl = removeUrlParam($this->gEnv->getCurrentRequestUri(), $removeParam);		// 遷移先
			} else if (	$cmd == M3_REQUEST_CMD_CONFIG_WIDGET ||				// ウィジェットの設定
						$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 表示位置を表示するとき(ウィジェット付き)
				$param = 'userlogin';			// ユーザログイン画面表示
				$this->redirectUrl = $this->gEnv->getCurrentRequestUri();		// 遷移先
			}
			return true;
		}
		// 直接実行の場合
		if($cmd == M3_REQUEST_CMD_DO_WIDGET){		// ウィジェット単体実行
			// 管理者権限がなければ実行できない
			if ($this->gEnv->isSystemAdmin()){
				// コンテナを起動
				$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
				switch ($task){
					case 'filebrowse':			// ファイルブラウズ
						$this->gLaunch->goSubWidget($task);
						return false;
				}
			}
		}
		// 一般画面でログアウトの場合は画面を維持
		if ($cmd == M3_REQUEST_CMD_LOGOUT && !$this->gEnv->isAdminDirAccess()){
			$removeParam = array(	array('key' => M3_REQUEST_PARAM_OPERATION_COMMAND, 'value' => M3_REQUEST_CMD_LOGIN),
									array('key' => M3_REQUEST_PARAM_OPERATION_COMMAND, 'value' => M3_REQUEST_CMD_LOGOUT));
			$url = removeUrlParam($this->gEnv->getCurrentRequestUri(), $removeParam);		// 遷移先
		}

		// 入力画面確認用ハッシュ値取得
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		
		// ログイン、ログアウト処理を行う
		// 処理を行った場合は現在の画面へリダイレクト
		// ログイン失敗時あるいは、ログインログアウト以外の場合返ってくる
		$retValue = 0;		// 「実行処理なし」にリセット
		if ($cmd == M3_REQUEST_CMD_LOGIN){
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// ログインの場合は入力画面をチェック
				$retValue = $this->gPage->standardLoginLogoutRedirect($request, $success, $url);
			}
		} else {
			$retValue = $this->gPage->standardLoginLogoutRedirect($request, $success, $url);
		}
		if ($retValue == 0){
			if ($this->gEnv->isCurrentUserLogined()){	// ログインしている場合
				if ($this->gEnv->isSystemAdmin()){	// システム管理者の場合
					// 表示画面を決定
					$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
					$taskSrc = $task;
					if (empty($task) || $task == 'menu'){	// デフォルト値
						$task = 'top';		// トップメニュー
					} else if ($task == 'master'){		// マスター管理
						$task = 'pageinfo';			// ページ情報をデフォルトにする
					} else if ($task == 'userlist_detail'){		// ユーザ詳細
						$task = 'userlist';
					} else if ($task == 'usergroup_detail'){		// ユーザグループ詳細
						$task = 'usergroup';
					} else if ($task == 'loginstatus' ||				// ログイン状況
								$task == 'loginstatus_history'){		// ログイン状況ユーザ単位履歴
						$task = 'loginstatus';
					} else if ($task == 'pagedef_detail' ||		// ページ定義詳細
								$task == 'pagedef_mobile' ||	// 携帯用ページ定義
								$task == 'pagedef_smartphone'){		// スマートフォン用ページ定義
						$task = 'pagedef';
					} else if ($task == 'edittable' ||		// テーブルデータ編集一覧
								$task == 'edittable_detail'){		// テーブルデータ編集詳細
						$task = 'edittable';
					} else if ($task == 'editmenu' ||		// 管理メニュー編集
								$task == 'editmenu_others'){		// 管理メニュー編集その他
						$task = 'editmenu';
					} else if ($task == 'pageinfo' ||				// ページ情報
								$task == 'pageinfo_detail'){		// ページ情報詳細
						$task = 'pageinfo';
					} else if ($task == 'pageid' ||				// ページID一覧
								$task == 'pageid_detail'){		// ページID詳細
						$task = 'pageid';
					} else if ($task == 'menuid' ||				// メニューID一覧
								$task == 'menuid_detail'){		// メニューID詳細
						$task = 'menuid';
					} else if ($task == 'opelog' ||				// 運用ログ一覧
								$task == 'opelog_detail'){		// 運用ログ詳細
						$task = 'opelog';
					} else if ($task == 'accesslog' ||				// アクセスログ一覧
								$task == 'accesslog_detail'){		// アクセスログ詳細
						$task = 'accesslog';
					} else if ($task == 'searchwordlog' ||				// 検索語ログ一覧
								$task == 'searchwordlog_detail'){		// 検索語ログ詳細
						$task = 'searchwordlog';
					} else if ($task == 'menudef' ||				// メニュー定義
								$task == 'menudef_detail'){		// メニュー定義詳細
						$task = 'menudef';
					} else if ($task == 'smenudef' ||				// 単一階層メニュー定義
								$task == 'smenudef_detail'){		// 単一階層メニュー定義詳細
						$task = 'smenudef';
					} else if ($task == 'linkinfo'){		// 内部リンク情報取得
						$task = 'linkinfo';
					} else if ($task == 'pagehead' ||				// ページヘッダ情報
								$task == 'pagehead_detail'){		// ページヘッダ情報
						$task = 'pagehead';
					} else if ($task == 'portal'){		// Magic3ポータル
						$task = 'portal';
					} else if ($task == 'tenantserver_detail'){		// テナントサーバ管理詳細
						$task = 'tenantserver';
					} else if ($task == 'analyzecalc'){		// アクセス解析集計
						$task = 'analyzecalc';
					} else if ($task == 'analyzegraph'){		// アクセス解析グラフ表示
						$task = 'analyzegraph';
					}
					
					// オプション表示
					if (strncmp($task, 'configwidget_', strlen('configwidget_')) == 0){// $taskが「configwidget_xxxx」タイプの場合の処理
						$task = 'configwidget';
					} else if (strncmp($task, 'showpage_', strlen('showpage_')) == 0){// $taskが「showpage_xxxx」タイプの場合の処理
						$task = 'showpage';
					}
					// コンテナを起動
					switch ($task){
						case 'test';			// テスト用画面
						case 'top';			// トップ画面
						case 'userlist':	// ユーザリスト
						case 'usergroup':	// ユーザグループ
						case 'loginstatus':	// ログイン状況
						case 'widgetlist':	// ウィジェットリスト
						case 'templist':	// テンプレートリスト
						case 'pagedef':		// 画面定義
						case 'initsystem':	// システム初期化
						case 'configsys':	// システム設定
						case 'configlang':	// 言語設定
						case 'configmessage':	// メッセージ設定
						case 'configwidget':	// ウィジェット定義
						case 'adjustwidget':	// ウィジェット位置調整
						case 'usercustom':	// ユーザ定義値管理
						case 'createtable':		// テーブル作成
						case 'edittable':		// テーブル編集
						case 'editmenu':		// メニュー編集
						case 'pageinfo':		// ページ情報
						case 'pageid':			// ページID
						case 'menuid':			// メニューID
						case 'opelog':			// 運用ログ
						case 'accesslog':			// アクセスログ
						case 'searchwordlog':		// 検索語ログ一覧
						case 'resbrowse':			// リソースブラウズ
						case 'filebrowse':			// ファイルブラウズ
						case 'filebrowser':			// ファイルブラウズ(elfinder)
						case 'menudef':			// メニュー定義
						case 'smenudef':			// 単一階層メニュー定義
						case 'linkinfo':			// 内部リンク情報取得
						case 'configsite':			// サイト情報
						case 'pagehead':			// ページヘッダ情報
						case 'portal':				// Magic3ポータル
						case 'tenantserver':			// テナントサーバ管理
						case 'analyzecalc':		// アクセス解析集計
						case 'analyzegraph':		// アクセス解析グラフ表示
							$this->gLaunch->goSubWidget($task);
							return false;
						case 'logout':		// ログアウト処理
							$logoutPage = $this->gEnv->createCurrentPageUrl() . '&cmd=logout';
							$this->gPage->redirect($this->getUrl($logoutPage));
							break;
						case 'showpage':	// 管理画面別ページ表示
							list($taskName, $pageSubId) = explode('_', $taskSrc, 2);
							$otherPage = $this->gEnv->getDefaultAdminUrl() . '?sub=' . $pageSubId;
							$this->gPage->redirect($this->getUrl($otherPage));
							break;
						default:
							$this->SetMsg(self::MSG_APP_ERR, $this->_('Can not access the page.'));		// アクセスできません
							$param = 'message';			// メッセージ画面表示
							break;
					}
				} else {		// システム管理者以外の場合
					$this->SetMsg(self::MSG_APP_ERR, $this->_('Failed to login.'));// ログインに失敗しました
				}
			} else {		// ログインしていないとき
				// メッセージは何も表示しないでログイン画面へ
			}
		} else if ($retValue == 1 && $success == false){	// ログイン失敗の場合
			$this->SetMsg(self::MSG_APP_ERR, $this->_('Failed to login.'));			// ログインに失敗しました
			if (!empty($url)) $this->redirectUrl = $url;	// リダイレクト先が設定されている場合は再設定
		} else if ($retValue == 3){			// パスワード送信のとき
			$this->setGuidanceMsg($this->_('Password sent.'));		// パスワードを送信しました
		}
		$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// ハッシュ値クリア
		return true;
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
		$template = '';
		
		switch ($param){
			case 'message':		// メッセージ表示のとき	
				$template = 'message.tmpl.html';
				break;
			case 'content':		// コンテンツ表示のとき
				$template = 'content.tmpl.html';
				break;
			default:						// ユーザログイン画面
				$template = 'userlogin.tmpl.html';
				break;
		}
		return $template;
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
		switch ($param){
			case 'message':		// メッセージ表示のとき	
				break;
			case 'content':		// コンテンツ表示のとき
				$this->tmpl->addVar("_widget", "content", $this->content);			// メンテナンスコンテンツ
				break;
			default:						// ユーザログイン画面
				if (!empty($this->redirectUrl)){		// 遷移先
					$this->tmpl->setAttribute('redirect_url', 'visibility', 'visible');
					$this->tmpl->addVar('redirect_url', 'url', $this->convertToDispString($this->redirectUrl));
				}
				
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
				break;
		}
		// テキストをローカライズ
		$localeText = array();
		$localeText['label_login'] = $this->_('Login');		// ログイン
		$localeText['label_login2'] = $this->_('Send Password');		// パスワード送信
		$localeText['label_account'] = $this->_('Account');		// アカウント
		$localeText['label_password'] = $this->_('Password');		// パスワード
		$localeText['label_password2'] = $this->_('Email');		// Eメール
		$this->setLocaleText($localeText);
	}
	/**
	 * アクセスポイントから汎用コンテンツのタイプを取得
	 *
	 * @return string		汎用コンテンツデータタイプ
	 */
	function getContentType()
	{
		// PC用サイト、携帯用サイト、スマートフォン用サイトを判断
		if ($this->gEnv->getIsPcSite()){
			return '';
		} else if ($this->gEnv->getIsMobileSite()){
			return 'mobile';
		} else if ($this->gEnv->getIsSmartphoneSite()){
			return 'smartphone';
		} else {
			return '';
		}
	}
}
?>
