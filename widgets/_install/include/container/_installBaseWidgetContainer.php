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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: _installBaseWidgetContainer.php 3778 2010-11-07 14:13:00Z fishbone $
 * @link       http://www.magic3.org
 */
//require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

//class _installBaseWidgetContainer extends BaseWidgetContainer
class _installBaseWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_LANG = 'ja';			// デフォルトの言語(日本語)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
	function _postAssign($request, &$param)
	{
		// 言語を再設定
		$langId = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);
		if (empty($langId)) $langId = self::DEFAULT_LANG;
		$this->tmpl->addVar('_widget', 'lang', $langId);
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['label_go_next'] = $this->_('Next');
		$localeText['label_go_back'] = $this->_('Back');
		$this->setLocaleText($localeText);
	}
	/**
	 * ヘッダ部メタタグの設定
	 *
	 * HTMLのheadタグ内に出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ。連想配列で「title」「description」「keywords」を設定。
	 */
	function _setHeadMeta($request, &$param)
	{
		$headData = array(	'title' => $this->_('Magic3 Install'));
		return $headData;
	}
}
?>
