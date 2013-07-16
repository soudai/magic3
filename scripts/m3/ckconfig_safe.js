/*
 * CKEditor(WYSIWYGエディター)の設定
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ckconfig_safe.js 6074 2013-06-04 12:57:46Z fishbone $
 * @link       http://www.magic3.org
 */
// デフォルトスタイル定義
CKEDITOR.stylesSet.add( 'default', []);

CKEDITOR.editorConfig = function( config ) {
	config.language = 'ja';
//	config.enterMode = CKEDITOR.ENTER_BR; // 改行をbrに変更
//	config.shiftEnterMode = CKEDITOR.ENTER_DIV;
//	config.autoParagraph = false;
//	config.fillEmptyBlocks = false;		// 「&nbsp;」が自動的に入るのを防ぐ
//	config.toolbarCanCollapse = true;		// ツールバー表示制御
	
	// ツールバーの設定
	config.toolbar_Safe = [
//		{ name: 'debug', items: [ 'Source' ] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
		{ name: 'colors', items : [ 'TextColor' ] },
//		{ name: 'styles', items: [ 'FontSize' ] },		// bug?
		{ name: 'insert', items: [ 'Image' ] }
	];
	
	// 追加プラグインの設定
	config.extraPlugins = 'bbcode';
	config.removePlugins = 'elementspath';
};
/*
CKEDITOR.on('dialogDefinition', function(ev){
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	var dialog = dialogDefinition.dialog;
	
	if (dialogName == 'image'){
		dialogDefinition.removeContents('Link');	// 「リンク」タブ削除
	}
});*/
