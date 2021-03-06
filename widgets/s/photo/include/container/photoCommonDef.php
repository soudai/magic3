<?php
/**
 * index.php用共通定義クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: photoCommonDef.php 4716 2012-02-26 02:19:15Z fishbone $
 * @link       http://www.magic3.org
 */
 
class photoCommonDef
{
	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
	const PHOTO_DIR = '/etc/photo';		// マスター画像格納ディレクトリ
	const DEFAULT_IMAGE_EXT = 'jpg';			// 画像ファイルのデフォルト拡張子
	const DEFAULT_PUBLIC_IMAGE_SIZE = 450;		// 一般表示用画像(ウォータマーク入り画像)の縦または横の最大サイズ
	const BUTTON_ICON_SIZE = 16;				// ボタン用アイコンサイズ
	const REF_TYPE_CONTENT = 'pt';		// 参照数カウント用(画像参照)
	const REF_TYPE_DOWNLOAD = 'photo-DL';		// 参照数カウント用(画像ダウンロード)
	const DEFAULT_CATEGORY_COUNT = 2;		// デフォルトの画像カテゴリー数
	const DEFAULT_PHOTO_LIST_VIEW_COUNT = 24;		// デフォルトの画像カテゴリー数
	const DEFAULT_PHOTO_TITLE_SHORT_LENGTH = 8;	// デフォルトの画像タイトル文字数
	const DEFAULT_COMMENT_COUNT = 30;		// コメント数
	const DEFAULT_SEARCH_AREA_TMPL = 'default_search.tmpl.html';		// デフォルトの検索エリアテンプレート
	const SEARCH_TEXT_ID = 'photo_main_text';
	const SEARCH_BUTTON_ID = 'photo_main_button';
	const SEARCH_RESET_ID = 'photo_main_reset';
	const SEARCH_SORT_ID = 'photo_main_sort';
	const SEARCH_FORM_ID = 'photo_main_form';
	const USER_OPTION = ';photo_main=author;';		// ログインユーザのユーザオプション
	const EC_LIB_OBJ_ID = 'eclib';		// Eコマースオブジェクト
	
	// デフォルト値
	const DEFAULT_IMAGE_SIZE			= 450;		// 公開画像サイズ(設定されていないときのデフォルト値)
	const DEFAULT_THUMBNAIL_SIZE		= 128;		// サムネール画像サイズ(設定されていないときのデフォルト値)
	const DEFAULT_PHOTO_LIST_ORDER		= '0';		// デフォルトの画像一覧並び順(降順)
	const DEFAULT_PHOTO_LIST_SORT_KEY	= 'index';	// デフォルトのソートキー
	
	// DB定義値
//	const CF_THUMBNAIL_BG_COLOR			= 'thumbnail_bg_color';		// サムネール背景色
//	const CF_IMAGE_CATEGORY_COUNT		= 'image_category_count';			// 画像カテゴリー数
	const CF_PHOTO_LIST_ITEM_COUNT		= 's:photo_list_item_count';			// 画像一覧表示項目数
	const CF_PHOTO_LIST_ORDER			= 's:photo_list_order';			// 画像一覧並び順
	const CF_PHOTO_LIST_SORT_KEY		= 's:photo_list_sort_key';			// 画像一覧ソートキー
	const CF_PHOTO_TITLE_SHORT_LENGTH	= 's:photo_title_short_length';// 略式写真タイトルの長さ
//	const CF_IMAGE_PROTECT_COPYRIGHT	= 'image_protect_copyright';		// 画像著作権保護
	const CF_ONLINE_SHOP				= 'online_shop';			// オンラインショップ機能
	const CF_PHOTO_CATEGORY_PASSWORD	= 'photo_category_password';		// 画像カテゴリーのパスワード制限
	const CF_IMAGE_SIZE					= 'image_size';				// 公開画像サイズ(生成)
	const CF_THUMBNAIL_SIZE				= 'thumbnail_size';			// サムネール画像サイズ(生成)
	const CF_DEFAULT_IMAGE_SIZE			= 's:default_image_size';				// デフォルト公開画像サイズ
	const CF_DEFAULT_THUMBNAIL_SIZE		= 's:default_thumbnail_size';			// デフォルトサムネール画像サイズ
//	const CF_THUMBNAIL_CROP				= 'thumbnail_crop';			// 切り取りサムネール
	// Eコマース
//	const CF_SELL_PRODUCT_PHOTO			= 'sell_product_photo';		// フォト商品販売
//	const CF_SELL_PRODUCT_DOWNLOAD		= 'sell_product_download';	// ダウンロード商品販売
	
	/**
	 * サムネール画像のパスを取得
	 *
	 * @param string $photoId		画像ID
	 * @param int    $size			画像サイズ
	 * @return string				画像パス
	 */
	static function getThumbnailPath($photoId, $size = 0)
	{
		global $gEnvManager;
		
		if (empty($size)){
			return $gEnvManager->getResourcePath() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . self::DEFAULT_THUMBNAIL_SIZE . '.' . self::DEFAULT_IMAGE_EXT;
		} else {
			return $gEnvManager->getResourcePath() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . $size . '.' . self::DEFAULT_IMAGE_EXT;
		}
	}
	/**
	 * サムネール画像のURLを取得
	 *
	 * @param string $photoId		画像ID
	 * @param int    $size			画像サイズ
	 * @return string				画像パス
	 */
	static function getThumbnailUrl($photoId, $size = 0)
	{
		global $gEnvManager;
		
		if (empty($size)){
			return $gEnvManager->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . self::DEFAULT_THUMBNAIL_SIZE . '.' . self::DEFAULT_IMAGE_EXT;
		} else {
			return $gEnvManager->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . $size . '.' . self::DEFAULT_IMAGE_EXT;
		}
	}
	/**
	 * 公開画像のパスを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像パス
	 */
	static function getPublicImagePath($photoId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourcePath() . self::THUMBNAIL_DIR . '/' . $photoId . '.' . self::DEFAULT_IMAGE_EXT;
	}
	/**
	 * 公開画像のURLを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像パス
	 */
	static function getPublicImageUrl($photoId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '.' . self::DEFAULT_IMAGE_EXT;
	}
	/**
	 * 画像取得
	 *
	 * @param string $photoId		画像ID
	 * @param object $db			DBオブジェクト
	 * @param int    $width			画像幅
	 * @param int    $height		画像高さ
	 * @return						なし
	 */
	static function getImage($photoId, $db, $width = 0, $height = 0)
	{
		global $gEnvManager;
		global $gPageManager;
		
		// ページ作成処理中断
		$gPageManager->abortPage();

		$ret = $db->getPhotoInfo($photoId, $gEnvManager->getCurrentLanguage(), $row);
		if ($ret){
			header('Content-type: ' . $row['ht_mime_type']);// 画像タイプ
			
			// キャッシュの設定
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0');
			header('Pragma: no-cache');

			// 画像サイズが指定されている場合は画像をリサイズ
			$imagePath = $gEnvManager->getIncludePath() . self::PHOTO_DIR . $row['ht_dir'] . DIRECTORY_SEPARATOR . $row['ht_public_id'];
			if ($width > 0 && $height > 0){
				// 画像サイズ取得
				$imageSize = getimagesize($imagePath);
				if ($imageSize){
					$srcWidth = $imageSize[0];
					$srcHeight = $imageSize[1];
					$imageType = $imageSize[2];

					// TrueColorイメージを作成
					$destImage = imagecreatetruecolor($width, $height);
				
					// 画像作成
					switch ($imageType){
						case IMAGETYPE_GIF:
							$image = @imagecreatefromgif($imagePath);
							break;
						case IMAGETYPE_JPEG:
							$image = @imagecreatefromjpeg($imagePath);
							break;
						case IMAGETYPE_PNG:
							$image = @imagecreatefrompng($imagePath);
							break;
						default:
							break;
					}
					// imagecopyresampledの方がimagecopyresizedよりも画質が良いのでこちらを使用
					imagecopyresampled($destImage, $image, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);

					// 画像出力
					switch ($imageType){
						case IMAGETYPE_GIF:
							$ret = @imagegif($destImage, null, 100);
							break;
						case IMAGETYPE_JPEG:
							$ret = @imagejpeg($destImage, null, 100);
							break;
						case IMAGETYPE_PNG:
							$ret = @imagepng($destImage, null, 100);
							break;
					}
					// イメージを破棄
					$ret = imagedestroy($image);
					$ret = imagedestroy($destImage);
				}
			} else {
				readfile($imagePath);
			}
		}
	
		// システム強制終了
		$gPageManager->exitSystem();
	}
	/**
	 * 画像サイズ調整
	 *
	 * @param int $width		画像横幅
	 * @param int $height		画像高さ
	 * @param int $maxSize		最大サイズ
	 * @return					なし
	 */
	static function adjustImageSize(&$width, &$height, $maxSize)
	{
		// 画像サイズ
		if ($width > $height){
			$height = round(($height / $width) * $maxSize);
			$width = round($maxSize);
		} else {
			$width = round(($width / $height) * $maxSize);
			$height = round($maxSize);
		}
	}
	/**
	 * フォトギャラリー定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// フォトギャラリー定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['hg_id'];
				$value = $rows[$i]['hg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
}
?>
