/**
 * コンテキストメニュー作成用用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: cotextmenu1.0.js 5784 2013-03-04 03:46:49Z fishbone $
 * @link       http://www.magic3.org
 */
$(function(){
	// コンテキストメニュー
	widgetWindow  = '<div class="m3_contextmenu" id="m3_widgetmenu" style="visibility:hidden;">';
	widgetWindow += '<ul>';
	widgetWindow += '<li id="m3_wconfig"><img src="' + M3_ROOT_URL + '/images/system/config.png" />&nbsp;<span>ウィジェットの設定</span></li>';
	widgetWindow += '</ul>';
	widgetWindow += '</div>';
	$("body").append(widgetWindow);

	// コンテキストメニューを作成
	$('.m3_widget').contextMenu('m3_widgetmenu', {
		menuStyle: {
			// border : "2px solid green",
			backgroundColor: '#FFFFFF',
			width: "150px",
			textAlign: 'left',
			font: '12px/1.5 Arial, sans-serif'
		},
		itemStyle: {
			padding: '3px 3px'
		},
		bindings: {
			'm3_wconfig': function(t){
				var attrs = m3_splitAttr($('#' + t.id).attr('m3'));
			    if (attrs['useconfig'] == '0'){
			        alert("このウィジェットには設定画面がありません");
					return;
			    }
				m3ShowConfigWindow(attrs['widgetid'], attrs['configid'], attrs['serial']);
			}
		}
/*		onContextMenu: function(e) {
			var attrs = m3_splitAttr($(e.target).parents('dl').attr('m3'));
			if (attrs['shared'] == '0'){	// 共通ウィジェットでない
				$('#m3_wshared span').text('ページ共通属性を設定');
			} else {
				$('#m3_wshared span').text('ページ共通属性を解除');
			}
			return true;
		},*/
/*		onShowMenu: function(e, menu) {
			// メニュー項目の変更
			var attrs = m3_splitAttr($(e.target).parents('dl').attr('m3'));
			if (attrs['useconfig'] == '0'){
				$('#m3_wconfig', menu).remove();
			}
			return menu;
		}*/
	}).addClass('m3_widget_contextmenu');		// コンテキストメニュークラス追加
});


