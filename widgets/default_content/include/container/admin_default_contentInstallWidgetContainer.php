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
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_default_contentInstallWidgetContainer.php 134 2007-12-16 07:44:48Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .	'/baseInstallWidgetContainer.php');

class admin_default_contentInstallWidgetContainer extends BaseInstallWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * SQLスクリプト実行前処理
	 *
	 * SQLスクリプトファイル実行前に呼ばれる。スクリプト実行前に必要な処理を行う。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップグレード)
	 * @return なし
	 */
	function _preScript($request, $install)
	{
	}
	/**
	 * SQLスクリプト実行後処理
	 *
	 * SQLスクリプトファイル実行後に呼ばれる。スクリプト実行後に必要な処理を行う。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップグレード)
	 * @return なし
	 */
	function _postScript($request, $install)
	{
	}
	/**
	 * SQLスクリプト実行
	 *
	 * 実行するSQLスクリプトファイル名を実行順に配列で返す。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップグレード)
	 * @return なし
	 */
	function _doScript($request, $install)
	{
		switch ($install){
			case 0:		// インストール
				return array('install.sql');
			case 1:		// アンインストール
				return array('uninstall.sql');
			case 2:		// アップグレード
				break;
			default:
				break;
		}
	}
}
?>
