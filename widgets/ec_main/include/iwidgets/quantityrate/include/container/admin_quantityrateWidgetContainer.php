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
 * @version    SVN: $Id: admin_quantityrateWidgetContainer.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');

class admin_quantityrateWidgetContainer extends BaseIWidgetContainer
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
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, $act, $configObj, $optionObj)
	{	
		return 'admin.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @param								なし
	 */
	function _assign($request, $act, $configObj, $optionObj)
	{
		// 基本情報を取得
		$id		= $optionObj->id;		// ユニークなID(配送方法ID)
		$init	= $optionObj->init;		// データ初期化を行うかどうか
		
		// 入力値を取得
		$price = $request->trimValueOf('iw_price');	// 商品あたりの送料
		$minCount = $request->trimValueOf('iw_min_count');	// 最小商品数
		$inputDate = ($request->trimValueOf('iw_input_date') == 'on') ? 1 : 0;			// 配達希望日時の入力を許可するかどうか
		$useMin = ($request->trimValueOf('iw_use_min') == 'on') ? 1 : 0;		// 無料となる購入額を使用するかどうか
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力エラーチェック
			$this->checkNumeric($price, '商品１個あたりの送料');
			$this->checkNumeric($minCount, '最小商品数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$configObj->price = $price;	// 商品1個あたりの送料
				$configObj->minCount = $minCount;	// 最小購入数
				$configObj->inputDate = $inputDate;		// 希望日時の入力許可
				$configObj->useMin = $useMin;		// 無料となる購入額を使用するかどうか
				$ret = $this->updateConfigObj($configObj);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, 'インナーウィジェットデータの更新に失敗しました');
				// ***** 正常に終了した場合はメッセージを残さない *****
			}
		} else if ($act == 'content'){		// 画面表示のとき
			if (!empty($init)){			// 初期表示のとき
				// 保存値取得
				if (empty($configObj)){		// 保存値がないとき
					// デフォルト値設定
					$price		= 0;	// 商品1個あたりの送料
					$minCount	= 5;	// 最小購入数
					$inputDate	= 0;		// 希望日時の入力許可
					$useMin		= 0;		// 無料となる購入数を使用するかどうか
				} else {
					$price		= $configObj->price;	// 商品1個あたりの送料
					$minCount	= $configObj->minCount;	// 最小購入数
					$inputDate	= $configObj->inputDate;		// 希望日時の入力許可
					$useMin		= $configObj->useMin;		// 無料となる購入額を使用するかどうか
				}
			}
			
			$this->tmpl->addVar("_widget", "price",	$price);
			$this->tmpl->addVar("_widget", "min_count",	$minCount);
			if ($inputDate) $this->tmpl->addVar('_widget', 'input_date', 'checked');		// 配達希望日時が入力可能かどうか
			if ($useMin) $this->tmpl->addVar('_widget', 'use_min', 'checked');				// 無料となる購入額を使用するかどうか
		}
	}
}
?>
