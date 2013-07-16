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
 * @version    SVN: $Id: commentTopWidgetContainer.php 6155 2013-07-02 00:02:49Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('comment') . '/commentBaseWidgetContainer.php');

class commentTopWidgetContainer extends commentBaseWidgetContainer
{
	private $message;			// ユーザ向けメッセージ
	private $pageNo;				// ページ番号
	private $cmd;				// 実行コマンド
	private $commentSerialNo;		// コメントシリアル番号
	private $isReadImageCheck;		// 画像読み込みチェックかどうか
	private $isErrorInReadImage;	// 画像読み込み中にエラーがあるかどうか
	private $currentPageUrl;			// 現在のページURL
	private $currentPageRootUrl;
	private $widgetTitle;			// ウィジェットタイトル
	private $userLimited;		// ユーザ制限あり
	private $commentVisible;		// コメントを表示する
	private $commentAccept;		// コメントを受け付ける
	private $isExistsComment;	// 表示するコメントがあるかどうか
	private $useTitle;		// コメント入力項目(タイトルあり)
	private $useAuthor;		// コメント入力項目(投稿者名あり)
	private $useEmail;		// コメント入力項目(Eメールあり)
	private $useUrl;		// コメント入力項目(URLあり)
	private $useAvatar;		// コメント入力項目(アバターあり)
	private $permitHtml;		// HTMLあり
	private $permitImage;		// 画像あり
	private $autolink;			// 自動リンク
	private $addLib = array();		// 追加スクリプト
	private $avatarSize;		// アバター画像サイズ
	private $maxImageSize;		// 画像最大サイズ
	private $imageDir;			// 画像格納ディレクトリ
	private $imageFileInfoArray;	// 画像ファイル情報
	const COMMENT_ID_SEPARATOR	= ':';			// コメントID作成用セパレータ
	const DEFAULT_TITLE = 'コメント';		// デフォルトのウィジェットタイトル名
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	
	const MESSAGE_NOT_PERMITTED_REFER		= '閲覧は許可されていません';				// コメント閲覧権限がない場合
	const MESSAGE_NOT_PERMITTED_POST		= '投稿は許可されていません';				// コメント投稿権限がない場合
	const MESSAGE_NO_COMMENT		= 'コメントは投稿されていません';				// コメントが投稿されていないメッセージ
	const NO_COMMENT_TITLE = 'タイトルなし';				// 未設定時のコメントタイトル
	const NO_COMMENT_AUTHOR = '投稿者名なし';				// 未設定時のコメント投稿者名
	const COMMENT_TITLE_FORMAT		= '「%s」についてのコメント';	// コメント用タイトルフォーマット
	const AVATAR_TITLE_TAIL = 'のアバター';
	const COOKIE_LIB = 'jquery.cookie';		// 名前保存用クッキーライブラリ
	const PERMALINK_ICON_FILE = '/images/system/permalink.png';		// 「パーマリンク」アイコン
	const PERMA_BUTTON_ICON_SIZE = 16;				// ボタン用アイコンサイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->imageDir = $this->gEnv->getIncludePath() . commentCommonDef::UPLOAD_IMAGE_DIR;			// 画像格納ディレクトリ
		if (!is_dir($this->imageDir)) @mkdir($this->imageDir, M3_SYSTEM_DIR_PERMISSION);
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
		return 'main.tmpl.html';
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
		$contentsId = '';			// 共通コンテンツID
		$this->cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if ($this->cmd == M3_REQUEST_CMD_DO_WIDGET){	// ウィジェット単体実行
			// 画像IDからコメントシリアル番号取得
			// コメントに添付されている場合はコメントの表示条件をチェック
			$imageId = $request->trimValueOf(commentCommonDef::REQUEST_PARAM_IMAGE_ID);	// 画像ID
			$ret = $this->gInstance->getFileManager()->getAttachFileInfoByFileId(commentCommonDef::$_viewContentType, $imageId, $attachFileRow, false/*登録状態区別せず*/);
			if ($ret){
				// コメントのアクセス可能状況をチェック
				$this->commentSerialNo = $attachFileRow[af_content_id];		// コメントシリアル番号
				if (empty($this->commentSerialNo)){			// コメントに未添付の画像の場合
					// クライアントIDが同じであればダウンロード可能
					$clientId = $this->gAccess->getClientId();
					if (!empty($clientId) && $clientId == $attachFileRow[af_client_id]){
						$this->downloadImage($request);			// 画像取得
					}
				} else {
					$ret = self::$_mainDb->getCommentItem($this->commentSerialNo, $row);
					if ($ret && !$row['cm_deleted'] && $row['cm_status'] == 2/*コメント公開中*/){		
						$this->contentType = $row['cm_content_type'];		// コンテンツタイプ
						$contentsId = $row['cm_contents_id'];		// 共通コンテンツID
					}
				}
			}
		} else {
			$this->contentType = $this->gPage->getContentType();

			switch ($this->contentType){
				case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID_SHORT);
					break;
				case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID_SHORT);
					break;
				case M3_VIEW_TYPE_BBS:					// BBS
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT);
					break;
				case M3_VIEW_TYPE_BLOG:				// ブログ
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);
					break;
				case M3_VIEW_TYPE_WIKI:				// wiki
					$contentsId = $request->getWikiPageFromQuery();		// 「=」なしのパラメータはwikiパラメータとする
					break;
				case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_ROOM_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_ROOM_ID_SHORT);
					break;
				case M3_VIEW_TYPE_EVENT:				// イベント情報
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID_SHORT);
					break;
				case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
					$contentsId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID);
					if (empty($contentsId)) $contentsId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID_SHORT);
					break;
			}
		}
		
		// 共通コンテンツIDがない場合は非表示にする
		if (empty($contentsId)){
			//$this->cancelParse();		// テンプレート変換処理中断
			$this->exitWidget();		// ウィジェット終了処理
			return;
		}
		
		// コメント定義取得
		$ret = self::$_mainDb->getConfig($this->contentType, ''/*全体の定義*/, $row);
		if ($ret){
			$viewType				= $row[commentCommonDef::FD_VIEW_TYPE];				// コメントタイプ(0=フラット、1=ツリー)
			$viewCount				= $row[commentCommonDef::FD_MAX_COUNT];			// 表示項目数
			$viewDirection			= $row[commentCommonDef::FD_VIEW_DIRECTION];				// 表示順
			$this->commentVisible	= $row[commentCommonDef::FD_VISIBLE];		// コメントを表示する
			$commentVisibleDefault	= $row[commentCommonDef::FD_VISIBLE_D];		// コメントを表示する(個別デフォルト)
			$this->commentAccept	= $row[commentCommonDef::FD_ACCEPT_POST];		// コメントを受け付ける
			$commentAcceptDefault	= $row[commentCommonDef::FD_ACCEPT_POST_D];		// コメントを受け付ける(個別デフォルト)
			$this->userLimited		= $row[commentCommonDef::FD_USER_LIMITED];		// ユーザ制限あり
			$needAuthorize			= $row[commentCommonDef::FD_NEED_AUTHORIZE];		// 認証が必要かどうか
			$this->permitHtml		= $row[commentCommonDef::FD_PERMIT_HTML];		// HTMLあり
			$this->permitImage		= $row[commentCommonDef::FD_PERMIT_IMAGE];		// 画像あり
			$this->autolink			= $row[commentCommonDef::FD_AUTOLINK];			// 自動リンク
			$maxLength				= $row[commentCommonDef::FD_MAX_LENGTH];			// 文字数
			$this->maxImageSize		= $row[commentCommonDef::FD_MAX_IMAGE_SIZE];		// 画像最大サイズ
			$this->useTitle			= $row[commentCommonDef::FD_USE_TITLE];		// タイトルあり
			$this->useAuthor		= $row[commentCommonDef::FD_USE_AUTHOR];		// 投稿者名あり
			$this->useEmail			= $row[commentCommonDef::FD_USE_EMAIL];		// Eメールあり
			$this->useUrl			= $row[commentCommonDef::FD_USE_URL];		// URLあり
			$this->useAvatar		= $row[commentCommonDef::FD_USE_AVATAR];		// アバターあり
		} else {		// 定義が取得できないとき
			//$this->cancelParse();		// テンプレート変換処理中断
			$this->exitWidget();		// ウィジェット終了処理
			return;
		}
		// コンテンツ個別のコメント定義取得
		// 全体の定義の否定形を最優先する
		$ret = self::$_mainDb->getConfig($this->contentType, $contentsId, $row);
		if ($ret){
			if ($this->commentVisible) $this->commentVisible	= $row[commentCommonDef::FD_VISIBLE];		// コメントを表示する
			if ($this->commentAccept) $this->commentAccept		= $row[commentCommonDef::FD_ACCEPT_POST];		// コメントを受け付ける
		} else {		// 個別の定義がない場合はデフォルトを取得
			if ($this->commentVisible) $this->commentVisible	= $commentVisibleDefault;		// コメントを表示する
			if ($this->commentAccept) $this->commentAccept		= $commentAcceptDefault;		// コメントを受け付ける
		}
		// コメント非表示の場合は終了
		if (!$this->commentVisible){
			//$this->cancelParse();		// テンプレート変換処理中断
			$this->exitWidget();		// ウィジェット終了処理
			return;
		}
		// 初期設定値
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->currentPageRootUrl = $this->gEnv->getRootUrlByCurrentPage();
		$sendButtonLabel = '投稿';		// 送信ボタンラベル
		$sendStatus = 0;		// 送信状況
		$avatarFormat = $this->gInstance->getImageManager()->getDefaultAvatarFormat();		// 画像フォーマット取得
		$this->gInstance->getImageManager()->parseImageFormat($avatarFormat, $imageType, $imageAttr, $this->avatarSize);		// 画像情報取得
		
		// 入力値取得
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf('page', '1');				// ページ番号
		if ($this->useTitle){
			$title = $request->trimValueOf('title');
		} else {
			$title = '';
		}
		if ($this->useAuthor){
			$author = $request->trimValueOf('author');			// 投稿者名
					
			// ログイン中はログインユーザ名を取得
			$userName = $this->gEnv->getCurrentUserName();
			if (!empty($userName)) $author = $userName;
		} else {
			$author = '';
		}
		if ($this->useEmail){
			$email = $request->trimValueOf('email');
			
			// ログイン中はログインユーザのEメールを取得
			$userEmail = $this->gEnv->getCurrentUserEmail();
			if (!empty($userEmail)) $email = $userEmail;
		} else {
			$email = '';
		}
		if ($this->useUrl){
			$url = $request->trimValueOf('url');
		} else {
			$url = '';
		}
		$comment = $request->trimValueOf('comment');
		if ($this->permitHtml){			// HTML送信の場合はBBCodeをHTMLに変換
			$commentHtml = $this->parseComment($comment);
		}
		$sendStatus = intval($request->trimValueOf('sendstatus'));			// 送信ステータス
		if ($sendStatus < 0 || 1 < $sendStatus) $sendStatus = 0;
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		
		// コメントIDの確認
		$isCommentValid = false;
		$commentId = $request->trimValueOf('commentid');			// コメントID
		if (!empty($commentId) && $commentId = md5($this->contentType . self::COMMENT_ID_SEPARATOR . $contentsId)) $isCommentValid = true;
		
		$isInit = false;	// 初期表示かどうか
		if ($this->cmd == M3_REQUEST_CMD_DO_WIDGET){	// ウィジェット単体実行
			if (!$this->userLimited || ($this->userLimited && $this->gEnv->isCurrentUserLogined())){		// ユーザ制限なし、または、ユーザ制限ありでログイン済みの場合
				$this->downloadImage($request);			// 画像取得
			} else {
				$this->exitWidget();		// ウィジェット終了処理
			}
		} else if ($act == 'checkcomment' && $sendStatus == 0){		// コメント確認のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET) && $isCommentValid){		// 正常なPOST値のとき
				// 入力チェック
				if ($this->useAuthor){
					$this->checkInput($author, '投稿者');
				}
				if (empty($maxLength)){		// 空のときは長さのチェックなし
					$this->checkInput($comment, 'コメント内容');
				} else {
					$this->checkLength($comment, 'コメント内容', $maxLength);
				}
				if ($this->useEmail) $this->checkMailAddress($email, 'Eメール', true);

				// ##### 画像ありの場合は画像を取り込む #####
				if ($this->permitHtml && $this->permitImage){
					// 画像URL変換
					$commentHtml = $this->convertImageUrl($commentHtml);
					if ($this->isErrorInReadImage){
						$this->setUserErrorMsg('画像読み込みに失敗しました');
						$commentHtml = '';
					}
				}
					
				// エラーなしの場合は確認画面表示
				if ($this->getMsgCount() == 0){
					$this->setGuidanceMsg('この内容でコメントを投稿しますか?');
					
					// 入力の変更不可
					$sendButtonLabel = '投稿';		// 送信ボタンラベル
					$sendStatus = 1;// 送信状況を「確定」に変更
				}
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			} else {		// ハッシュキーが異常のとき
				// 送信ステータスを初期化
				$sendStatus = 0;
				
				$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			}
		} else if ($act == 'sendcomment' && $sendStatus == 1){	// コメント受信のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET) && $isCommentValid){		// 正常なPOST値のとき
				// 入力チェック
				if ($this->useAuthor){
					$this->checkInput($author, '投稿者');
				}
				if (empty($maxLength)){		// 空のときは長さのチェックなし
					$this->checkInput($comment, 'コメント内容');
				} else {
					$this->checkLength($comment, 'コメント内容', $maxLength);
				}
				if ($this->useEmail) $this->checkMailAddress($email, 'Eメール', true);
				
				// ##### 画像ありの場合は画像を取り込む #####
				if ($this->permitHtml && $this->permitImage){
					// 仮登録画像を取得
					$this->imageFileInfoArray = array();
					$clientId = $this->gAccess->getClientId();
					if (!empty($clientId)){
						$ret = $this->gInstance->getFileManager()->getAttachFileInfoByClientId(commentCommonDef::$_viewContentType, $clientId, $imageFileRows);
						if ($ret){
							for ($i = 0; $i < count($imageFileRows); $i++){
								$fileRow = $imageFileRows[$i];
								$newInfoObj = new stdClass;
								$newInfoObj->title		= '';
								$newInfoObj->filename	= '';
								$newInfoObj->fileId		= $fileRow['af_file_id'];
								$newInfoObj->originalUrl	= $fileRow['af_original_url'];		// 取得先URL
								$this->imageFileInfoArray[] = $newInfoObj;
							}
						}
					}
					
					// 画像URL変換
					$this->isReadImageCheck = true;		// 画像読み込みチェック
					$commentHtml = $this->convertImageUrl($commentHtml);
					if ($this->isErrorInReadImage){
						$this->setUserErrorMsg('画像読み込みに失敗しました');
						$commentHtml = '';
					}
				}
						
				if ($this->getMsgCount() == 0){
					$ret = false;
					if (!empty($comment) &&
						!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
						// コメント登録
						$commentStatus = 0;		// 未設定
						if (!$needAuthorize) $commentStatus = 2;		// 認証が必要でない場合は自動的に公開
					
						// コメント追加タイプ
						$addType = 0;	// フラット(最後に追加)
						if ($this->permitHtml){			// HTML送信の場合はBBCodeをHTMLに変換
							$ret = self::$_mainDb->addComment($addType, $this->contentType, $this->_langId, $contentsId, commentCommonDef::$_deviceType, 0/*親コメントシリアル*/, 
													$title, $commentHtml, $url, $author, $email, $commentStatus, $newSerial);
						} else {
							$ret = self::$_mainDb->addComment($addType, $this->contentType, $this->_langId, $contentsId, commentCommonDef::$_deviceType, 0/*親コメントシリアル*/, 
													$title, $comment, $url, $author, $email, $commentStatus, $newSerial);
						}
						
						// ##### 添付ファイル情報を更新 #####
						if ($ret){
							$ret = $this->gInstance->getFileManager()->updateAttachFileInfo(commentCommonDef::$_viewContentType, $newSerial, 0, $newSerial,
																																$this->imageFileInfoArray, $this->imageDir);
						}
					}
					if ($ret){
						$this->setGuidanceMsg('コメントを投稿しました');
						
						// 入力値を初期化
						$title = '';
						$comment = '';
						$commentHtml = '';
						$url = '';
//						$author = '';
//						$email = '';
						
						// 送信ステータスを更新
						$sendStatus = 0;
					
						// ハッシュキー作成
						$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
						$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
					} else {
						$this->setUserErrorMsg('コメントの投稿に失敗しました');

						// 送信ステータスを更新
						$sendStatus = 0;
					
						$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
					}
				} else {		// 送信時入力エラーの場合は初期画面に戻す
					// 送信ステータスを更新
					$sendStatus = 0;
					
					// ハッシュキー作成
					$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
					$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				}
			} else {		// ハッシュキーが異常のとき
				// 送信ステータスを初期化
				$sendStatus = 0;
					
				$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			}
		} else if ($act == 'sendcancel' && $sendStatus == 1){	// コメントキャンセルのとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET) && $isCommentValid){		// 正常なPOST値のとき
				$this->setGuidanceMsg('コメントをキャンセルしました');
				
				// 送信ステータスを更新
				$sendStatus = 0;

				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			} else {		// ハッシュキーが異常のとき
				// 送信ステータスを初期化
				$sendStatus = 0;
				
				$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			}
		} else {
			$isInit = true;	// 初期表示かどうか
			
			// 送信ステータスを初期化
			$sendStatus = 0;
				
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			
			// ##### 初期表示時は仮登録の添付ファイルを削除 #####
			$this->gInstance->getFileManager()->cleanAttachFileInfo(commentCommonDef::$_viewContentType, $this->imageDir);
		}
		
		// タイトル作成
		$this->widgetTitle = $this->createTitle($this->contentType, $contentsId);
		
		// ##### コメントを表示 #####
		$showComment = false;		// コメント表示するかどうか
		if (!$this->userLimited || ($this->userLimited && $this->gEnv->isCurrentUserLogined())){		// ユーザ制限なし、または、ユーザ制限ありでログイン済みの場合
			if ($sendStatus == 0){		// 初期表示の場合はすべてのコメントを表示
				//$this->tmpl->setAttribute('show_comment_list', 'visibility', 'visible');		// 既存コメントを表示
				// コメント表示項目設定
				if ($viewType == 1) $this->tmpl->setAttribute('show_reply', 'visibility', 'visible');		// 「返信」ボタン表示
				if ($this->useTitle) $this->tmpl->setAttribute('show_title', 'visibility', 'visible');	// タイトル
				if ($this->useAvatar) $this->tmpl->setAttribute('show_avatar', 'visibility', 'visible');	// アバター
				
				// コメント総数取得
				$totalCount = self::$_mainDb->getCommentCount($this->contentType, $this->_langId, $contentsId, true/*公開コメントのみ*/);
				$pageLink = $this->createPageLink($pageNo, $totalCount, $viewCount, $this->currentPageUrl);
		
				// コメント取得
				self::$_mainDb->getComment($this->contentType, $this->_langId, $contentsId, $viewCount, $pageNo, $viewDirection, array($this, 'itemsLoop'));
			
				// コメントがない場合はリストを非表示
				//if (!$this->isExistsComment) $this->tmpl->setAttribute('comment_list', 'visibility', 'hidden');
				if ($this->isExistsComment) $this->tmpl->setAttribute('show_comment_list', 'visibility', 'visible');		// 既存コメントを表示
			} else {
				// 初期表示以外の場合はリプライ先のコメントのみ表示
			}
			
			$showComment = true;		// コメント表示するかどうか
		} else {
			$this->message = self::MESSAGE_NOT_PERMITTED_REFER;		// 閲覧不可メッセージ
		}

		// コメントを受け付けるときは、コメント入力欄を表示
		if ($showComment && $this->commentAccept){
			$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');		// コメント投稿欄を表示
			
			// 入力値を戻す
			if ($this->useTitle){
				$this->tmpl->setAttribute('input_title', 'visibility', 'visible');		// タイトルあり
				$this->tmpl->addVar("input_title", "title", $this->convertToDispString($title));
			}
			if ($this->useAuthor){
				$this->tmpl->setAttribute('input_author', 'visibility', 'visible');		// 投稿者名あり
				$this->tmpl->addVar("input_author", "author", $this->convertToDispString($author));
				
				// ログイン中はログインユーザ名を表示
				if ($this->gEnv->isCurrentUserLogined()){
					$this->tmpl->addVar("input_author", "author_disabled", 'readonly');
					$this->tmpl->setAttribute('update_cookie_author', 'visibility', 'hidden');
					$this->tmpl->setAttribute('init_cookie_author', 'visibility', 'hidden');
				}
			}
			if ($this->useEmail){
				$this->tmpl->setAttribute('input_email', 'visibility', 'visible');		// Eメールあり
				$this->tmpl->addVar("input_email", "email", $this->convertToDispString($email));
				
				// ログイン中はログインユーザのEメールを表示
				$userEmail = $this->gEnv->getCurrentUserEmail();
				if ($this->gEnv->isCurrentUserLogined() && !empty($userEmail)){		// Eメールが設定されている場合のみ
					$this->tmpl->addVar("input_email", "email_disabled", 'readonly');
					$this->tmpl->setAttribute('update_cookie_email', 'visibility', 'hidden');
					$this->tmpl->setAttribute('init_cookie_email', 'visibility', 'hidden');
				}
			}
			if ($this->useUrl){
				$this->tmpl->setAttribute('input_url', 'visibility', 'visible');		// URLあり
				$this->tmpl->addVar("input_url", "url", $this->convertToDispString($url));
			}

			//if ($this->useAvatar) $this->tmpl->setAttribute('input_title', 'visibility', 'visible');		// アバターあり
			
			if ($sendStatus == 1){		// 確認画面のとき
				$this->tmpl->addVar("input_title", "title_disabled", 'readonly');
				$this->tmpl->addVar("input_author", "author_disabled", 'readonly');
				$this->tmpl->addVar("input_email", "email_disabled", 'readonly');
				$this->tmpl->addVar("input_url", "url_disabled", 'readonly');
				$this->tmpl->addVar("input_comment", "comment_disabled", 'readonly');
				$this->tmpl->setAttribute('cancel_button', 'visibility', 'visible');		// キャンセルボタン表示
				
				// クッキー停止
				$this->tmpl->setAttribute('update_cookie_author', 'visibility', 'hidden');
				$this->tmpl->setAttribute('init_cookie_author', 'visibility', 'hidden');
				$this->tmpl->setAttribute('update_cookie_email', 'visibility', 'hidden');
				$this->tmpl->setAttribute('init_cookie_email', 'visibility', 'hidden');
			}

			// HTML入力用の設定
			if ($this->permitHtml){
				if ($sendStatus == 0){		// 初期状態のとき
					$this->tmpl->setAttribute('input_comment', 'visibility', 'visible');		// コメント入力フィールド表示
					
					$this->tmpl->setAttribute('show_wysiwyg', 'visibility', 'visible');		// wysiwygエディター表示
					$this->addLib[] = ScriptLibInfo::LIB_CKEDITOR;		// CKEditorライブラリを追加
				} else {
					$this->tmpl->setAttribute('show_comment', 'visibility', 'visible');
					$this->tmpl->addVar('show_comment', 'comment_html', $commentHtml);			// HTMLコメント
					$this->tmpl->addVar('show_comment', 'comment', $this->convertToDispString($comment));			// コメントソース
				}
			} else {
				$this->tmpl->setAttribute('input_comment', 'visibility', 'visible');	// コメント入力フィールド表示
				$this->tmpl->addVar('input_comment', 'comment', $this->convertToDispString($comment));			// コメントソース
			}

			// コメントID作成
			$commentId = md5($this->contentType . self::COMMENT_ID_SEPARATOR . $contentsId);			// コメントID作成用セパレータ
			
			$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
			$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
			$this->tmpl->addVar("add_comment", "status",	$sendStatus);			// 送信ステータス
			$this->tmpl->addVar("add_comment", "ticket", $postTicket);				// 画面確認用
			$this->tmpl->addVar("add_comment", "comment_id", $commentId);				// コメントID
			
			//if ($sendStatus != 0 || $this->getMsgCount() > 0){		// 初期状態以外または入力エラーがあるとき
			if (!$isInit){		// 初期表示以外のとき
				// コメント入力エリアをトップに表示
				$this->tmpl->setAttribute('scrollup_comment', 'visibility', 'visible');
			}
				
			// 名前保存用のスクリプトライブラリ追加
			$this->tmpl->setAttribute('init_form', 'visibility', 'visible');
			$this->tmpl->setAttribute('update_cookie', 'visibility', 'visible');
			$this->addLib[] = self::COOKIE_LIB;
			
		} else {		// コメント投稿不可の場合
			if (empty($this->message)) $this->message = self::MESSAGE_NOT_PERMITTED_POST;		// 投稿不可メッセージ
		}
		
		// コメントがないときは投稿なしメッセージを表示
		if (empty($this->message) && !$this->isExistsComment){
			$this->message = self::MESSAGE_NO_COMMENT;
		}
		
		// 初期表示の場合で入力エラーメッセージがない場合はメッセージを表示
		//if ($sendStatus == 0 && $this->getMsgCount() == 0 && !empty($this->message)){
		if ($isInit && !empty($this->message)){
			$this->tmpl->setAttribute('message', 'visibility', 'visible');
			$this->tmpl->addVar("message", "message", $this->convertToDispString($this->message));
		}
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return $this->widgetTitle;
	}
	/**
	 * ウィジェット終了処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function exitWidget()
	{
		if ($this->cmd == M3_REQUEST_CMD_DO_WIDGET){	// ウィジェット単体実行
			// ダウンロード不可のときはエラーログを残す
			$msgDetail = '';
			if (!empty($this->commentSerialNo)) $msgDetail .= 'コメントシリアル番号=' . $this->commentSerialNo;
			$this->writeUserError(__METHOD__, '画像ファイルへの不正なアクセスを検出しました。画像ID=' . $imageId, 2200, $msgDetail);
			
			// ページ作成処理中断
			$this->gPage->abortPage();
						
			// レスポンスヘッダ設定
			$this->gPage->setResponse(403/*アクセス禁止*/);

			// システム強制終了
			$this->gPage->exitSystem();
		} else {
			$this->cancelParse();		// テンプレート変換処理中断
		}
	}
	/**
	 * 画像ダウンロード処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function downloadImage($request)
	{
		$imageId = $request->trimValueOf(commentCommonDef::REQUEST_PARAM_IMAGE_ID);	// 画像ID

		// ページ作成処理中断
		$this->gPage->abortPage();
		
		// 添付ファイル情報を取得
		$downloadCompleted = false;				// ダウンロード処理完了かどうか
		
		if (empty($this->commentSerialNo)){		// まだ画像が登録されていない場合
			$downloadFile = $this->imageDir . DIRECTORY_SEPARATOR . $imageId;
			$downloadFilename = $attachFileRow['af_filename'];
			if (empty($downloadFilename)) $downloadFilename = $attachFileRow['af_original_filename'];
		
			// ダウンロード処理
			$ret = $this->gPage->downloadFile($downloadFile, $downloadFilename);
			$downloadCompleted = true;				// ダウンロード処理完了かどうか
		} else {
			$ret = $this->gInstance->getFileManager()->getAttachFileInfo(commentCommonDef::$_viewContentType, $this->commentSerialNo, $attachFileRows);
			if ($ret){
				for ($i = 0; $i < count($attachFileRows); $i++){
					$fileRow = $attachFileRows[$i];
					if ($imageId == $fileRow['af_file_id']){
						//$downloadFile = commentCommonDef::getAttachFileDir() . DIRECTORY_SEPARATOR . $imageId;
						$downloadFile = $this->imageDir . DIRECTORY_SEPARATOR . $imageId;
						$downloadFilename = $fileRow['af_filename'];
						if (empty($downloadFilename)) $downloadFilename = $fileRow['af_original_filename'];
					
						// ダウンロード処理
						$ret = $this->gPage->downloadFile($downloadFile, $downloadFilename);
						$downloadCompleted = true;				// ダウンロード処理完了かどうか
						break;
					}
				}
			}
		}
		if ($downloadCompleted){		// ダウンロード処理完了のとき
			// ダウンロードログを残す
			$this->gInstance->getAnalyzeManager()->logContentDownload(commentCommonDef::$_viewContentType . commentCommonDef::DOWNLOAD_TYPE_IMAGE, $imageId);
		} else {
			$msgDetail = '';
			if (!empty($this->commentSerialNo)) $msgDetail .= 'コメントシリアル番号=' . $this->commentSerialNo;
			$this->writeError(__METHOD__, '画像ファイルのダウンロードに失敗しました。画像ファイルが見つかりません。画像ID=' . $imageId, 2200, $msgDetail);
		}

		// システム強制終了
		$this->gPage->exitSystem();
	}
	/**
	 * タイトル作成
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentsId		共通コンテンツID
	 * @param string					タイトル
	 */
	function createTitle($contentType, $contentsId)
	{
		$title = self::DEFAULT_TITLE;
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$ret = self::$_mainDb->getContentById(''/*PC用コンテンツ*/, $this->_langId, $contentsId, $row);
				if ($ret) $contentName = $row['cn_name'];
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$ret = self::$_mainDb->getProductById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['pt_name'];
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				// 未使用
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$ret = self::$_mainDb->getEntryById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['be_name'];
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$contentName = $contentsId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$ret = self::$_mainDb->getRoomById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['ur_name'];
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$ret = self::$_mainDb->getEventById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['ee_name'];
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$ret = self::$_mainDb->getPhotoById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['ht_name'];
				break;
		}
		if (!empty($contentName)){
			$title = sprintf(self::COMMENT_TITLE_FORMAT, $contentName);
		}
		return $title;
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
	{
		return $this->addLib;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$contentsId = $fetchedRow['cm_contents_id'];		// 共通コンテンツID
		$permaLink = commentCommonDef::COMMENT_PERMA_HEAD . $fetchedRow['cm_no'];		// コメントパーマリンク
		$titleTag = '';
		if ($this->useTitle){
			$commentTitle = $fetchedRow['cm_title'];			// コメントタイトル
			if (empty($commentTitle)) $commentTitle = self::NO_COMMENT_TITLE;
			$titleTag = '<a name="' . $permaLink . '" href="#' . $permaLink . '">' . $this->convertToDispString($commentTitle) . '</a>';
		}
		$permaUrl = commentCommonDef::createCommentUrl($this->contentType, $contentsId, $fetchedRow['cm_no']);
		$permaTag = '<a href="' . $this->convertUrlToHtmlEntity($permaUrl) . '"><img src="' . $this->getUrl($this->gEnv->getRootUrl() . self::PERMALINK_ICON_FILE) . 
						'" width="' . self::PERMA_BUTTON_ICON_SIZE . '" height="' . self::PERMA_BUTTON_ICON_SIZE . '" title="パーマリンク" alt="パーマリンク" style="border:none;margin:0;padding:0;" /></a>';
		$commentTag = $fetchedRow['cm_message'];
		
		// コメント投稿ユーザ名
		if ($this->useAuthor){
			$userName = $fetchedRow['lu_name'];
			if (empty($userName)) $userName = $fetchedRow['cm_author'];
		}
		if (empty($userName)) $userName = self::NO_COMMENT_AUTHOR;
	
		// アバター
		$avatarTag = '';
		if ($this->useAvatar){
			$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl($fetchedRow['lu_avatar']);
			$avatarTitle = $this->convertToDispString($userName) . self::AVATAR_TITLE_TAIL;
			$avatarTag = '<img src="' . $this->getUrl($avatarUrl) . '" width="' . $this->avatarSize . '" height="' . $this->avatarSize . 
							'" border="0" alt="' . $avatarTitle . '" title="' . $avatarTitle . '" />';
		}

		// URL
		$urlTag = '';
		if ($this->useUrl){
			$url = $fetchedRow['cm_url'];
			if (!empty($url)) $urlTag = '<br />URL: <a href="' . $this->convertUrlToHtmlEntity($url) . '" target="_blank">' . $this->convertToDispString($url) . '</a>';
		}

		// コメント内容
		if ($this->permitHtml){			// HTMLコメントの場合
			// BBコード変換で自動リンクは作成される
			// Magic3マクロの変換
			//$commentTag = $this->convertM3ToHtml($fetchedRow['cm_message'], true/*改行コーをbrタグに変換*/);
			$commentTag = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->currentPageRootUrl, $commentTag);// アプリケーションルートを変換
		} else {
			if ($this->autolink){		// 自動リンクの場合
				$commentTag = preg_replace("/(https?):\/\/([\w;\/\?:\@&=\+\$,\-\.!~\*'\(\)%#]+)/", "<a href=\"$1://$2\" target=\"_blank\">$1://$2</a>", $commentTag);
			}
			$commentTag = $this->convertToPreviewText($this->convertToDispString($commentTag));// 改行コードをbrタグに変換
		}

		$row = array(
			'title'	=> $titleTag,		// タイトル
			'avatar' => $avatarTag,			// アバター
			'author' => $this->convertToDispString($userName),	// 投稿者名
			'date' => $this->timestampToDate($fetchedRow['cm_create_dt']),		// コメント投稿日(日付)
			'time' => $this->timestampToTime($fetchedRow['cm_create_dt']),		// コメント投稿日(時間)
			'permalink' => $permaTag,			// パーマリンクアイコン
			'url'	=> $urlTag,		// URL
			'comment' => $commentTag	// コメント内容
		);
		$this->tmpl->addVars('comment_list', $row);
		$this->tmpl->parseTemplate('comment_list', 'a');
		$this->isExistsComment = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * ページリンク作成
	 *
	 * @param int $pageNo			ページ番号(1～)。ページ番号が範囲外にある場合は自動的に調整
	 * @param int $totalCount		総項目数
	 * @param int $viewItemCount	1ページあたりの項目数
	 * @param string $baseUrl		リンク用のベースURL
	 * @return string				リンクHTML
	 */
	function createPageLink(&$pageNo, $totalCount, $viewItemCount, $baseUrl)
	{
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewItemCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;

		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			// ページ数1から「LINK_PAGE_COUNT」までのリンクを作成
			$maxPageCount = $pageCount < self::LINK_PAGE_COUNT ? $pageCount : self::LINK_PAGE_COUNT;
			for ($i = 1; $i <= $maxPageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$linkUrl = $this->getUrl($baseUrl . '&page=' . $i, true/*リンク用*/);
					$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $i . '</a>';
				}
				$pageLink .= $link;
			}
			// 残りは「...」表示
			if ($pageCount > self::LINK_PAGE_COUNT) $pageLink .= '&nbsp;...';
		}
		if ($pageNo > 1){		// 前ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&page=' . ($pageNo -1), true/*リンク用*/);
			$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >前へ</a>';
			$pageLink = $link . $pageLink;
		}
		if ($pageNo < $pageCount){		// 次ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&page=' . ($pageNo +1), true/*リンク用*/);
			$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >次へ</a>';
			$pageLink .= $link;
		}
		return $pageLink;
	}
	/**
	 * BBCodeのコメントを解析し、HTMLに変換
	 *
	 * @param string $src		コメント入力
	 * @return string			HTML変換したコメント
	 */
	function parseComment($src)
	{
		$commentHtml = $this->gInstance->getTextConvManager()->convBBCodeToHtml($src, true);
		return $commentHtml;
	}
	/**
	 * コンテンツの画像URLを変換
	 *
	 * @param string $html		変換元コンテンツ
	 * @return string			変換後コンテンツ
	 */
	function convertImageUrl($html)
	{
		$str = '/<img[^<]*?src\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
		$dest = preg_replace_callback($str, array($this, "_convert_image_url_callback"), $html);
		return $dest;
	}
	/**
	 * IMGタグURL変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _convert_image_url_callback($matchData)
	{
		static $index = 0;
		
		// エラーチェック
		if ($this->isErrorInReadImage) return '';		// 画像読み込みエラー発生
		
		//$destTag = $matchData[0];	// マッチしたタグ全体
		$destTag = '';
		$imageUrl = $matchData[1];
		if ($this->gEnv->isSystemUrlAccess($imageUrl)){		// システム内のファイルのとき
		} else {
			if ($this->isReadImageCheck){
				if ($index < count($this->imageFileInfoArray)){
					$fileInfo = $this->imageFileInfoArray[$index];
					if (!empty($imageUrl) && $imageUrl == $fileInfo->originalUrl){	// 取得先URL比較
						$imageId = $fileInfo->fileId;		// 画像ID
						$imagePath = $this->imageDir . DIRECTORY_SEPARATOR . $imageId;
						
						// 画像サイズ取得
						$this->gInstance->getImageManager()->getImageInfo($imagePath, $width, $height);
						
						$param = commentCommonDef::REQUEST_PARAM_IMAGE_ID . '=' . $imageId;
						$newUrl = $this->createCmdUrlToCurrentWidget($param, true/*マクロ形式で取得*/);
						$destTag = '<img src="' . $newUrl . '" width="' . $width . '" height="' . $height . '" />';
					} else {
						$this->isErrorInReadImage = true;		// 画像読み込みエラー発生
					}
				} else {
					$this->isErrorInReadImage = true;		// 画像読み込みエラー発生
				}
			} else {
				if (empty($imageUrl)){
					$this->isErrorInReadImage = true;		// 画像読み込みエラー発生
				} else {
					// 画像ファイル名作成
					$imageId = $this->gInstance->getFileManager()->createRandFileId();
					$originalFilename = basename($imageUrl);		// 元のファイル名
					$imagePath = $this->imageDir . DIRECTORY_SEPARATOR . $imageId;
						
					// 画像を取り込む
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
					if ($tmpFile !== false){
						// 一時ファイルに読み込む
						$ret = true;
						if ($input = @fopen($imageUrl, 'r')){
							$ret = file_put_contents($tmpFile, $input);
							if ($ret !== false) $ret = true;
							@fclose($input);
						} else {
							$ret = false;
						}

						// 画像作成
						if ($ret){
							$ret = $this->gInstance->getImageManager()->createImage($tmpFile, $imagePath, $this->maxImageSize, commentCommonDef::OUTPUT_IMAGE_TYPE, $destSize);
						}

						// 画像登録
						if ($ret){
							$ret = $this->gInstance->getFileManager()->addAttachFileInfo(commentCommonDef::$_viewContentType, $imageId, $imagePath, $originalFilename, $imageUrl);
						}

						// 画像が作成できない場合は画像を表示しない
						if ($ret){
							$param = commentCommonDef::REQUEST_PARAM_IMAGE_ID . '=' . $imageId;
							$newUrl = $this->createCmdUrlToCurrentWidget($param);
							$destTag = '<img src="' . $this->getUrl($newUrl) . '" width="' . $destSize['width'] . '" height="' . $destSize['height'] . '" />';
						}
						if (!$ret) $this->isErrorInReadImage = true;		// 画像読み込みエラー発生

						// 一時ファイル削除
						unlink($tmpFile);
					} else {
						$this->isErrorInReadImage = true;		// 画像読み込みエラー発生
					}
				}
			}
		}
		$index++;
		return $destTag;
    }
}
?>
