<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ポータル用コンテンツ更新情報
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: rss_portal_updateinfoWidgetContainer.php 2724 2009-12-21 07:41:16Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseRssContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/portal_updateinfoDb.php');

class rss_portal_updateinfoWidgetContainer extends BaseRssContainer
{
	private $db;
	private $itemCount;					// リスト項目数
	private $isExistsList;				// リスト項目が存在するかどうか
	private $rssChannel;				// RSSチャンネル部出力データ
	private $rssSeqUrl = array();					// 項目の並び
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const CONTENT_TYPE = 'content';		// 取得コンテンツタイプ
	const DEFAULT_TITLE = 'コンテンツ更新情報';			// デフォルトのウィジェットタイトル
	const DEFAULT_DESC = '関連サイトのコンテンツの更新情報が取得できます。';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new portal_updateinfoDb();
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
		return 'rss_index.tmpl.html';
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$langId = $this->gEnv->getDefaultLanguage();
		
		$this->itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->itemCount	= $paramObj->itemCount;
		}
			
		// 一覧を作成
		$this->db->getUpdateInfoList(self::CONTENT_TYPE, intval($this->itemCount), array($this, 'itemsLoop'));
				
		// 画面にデータを埋め込む
		if ($this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'visible');
		
		// RSSチャンネル部出力データ作成
		$linkUrl = $this->getUrl($this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId()));
		$this->rssChannel = array(	'title' => self::DEFAULT_TITLE,		// タイトル
									'link' => $linkUrl,					// RSS配信用URL
									'description' => self::DEFAULT_DESC,// 説明
									'seq' => $this->rssSeqUrl);			// 項目の並び
	}
	/**
	 * RSSのチャンネル部出力
	 *
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ
	 */
	function _setRssChannel($request, &$param)
	{
		return $this->rssChannel;
	}
	/**
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$name = $fetchedRow['nw_name'];
		$linkUrl = $fetchedRow['nw_link'];		// コンテンツへのリンク
		$message = $fetchedRow['nw_message'];
		$siteLink = $fetchedRow['nw_site_link'];
		$siteName = $fetchedRow['nw_site_name'];
		
		if (!empty($name)){
			// タイトルにサイト名を付加
			$name = $siteName . ' - ' . $name;
			$row = array(
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($name),			// タイトル
				'message' => $this->convertToDispString($message),		// メッセージ
				'site_link' => $this->convertUrlToHtmlEntity($siteLink),	// サイトへのリンク
				'site_name' => $this->convertToDispString($siteName),		// サイト名
				'date' => getW3CDate($fetchedRow['nw_regist_dt'])		// 登録日時
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		
			// RSS用
			$this->rssSeqUrl[] = $linkUrl;					// 項目の並び
			
			$this->isExistsList = true;		// リスト項目が存在するかどうか
		}
		return true;
	}
}
?>
