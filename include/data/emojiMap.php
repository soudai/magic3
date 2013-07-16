<?php
/**
 * 絵文字情報ファイル
 * 
 * Magic3の絵文字番号と画像ファイル、imodeのSJIS対応マッピング
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: emojiMap.php 786 2008-06-24 03:06:55Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

$EMOJI = array(
	array('name' => '晴れ',							'filename' => 'sun',			'i' => 'F89F'),
	array('name' => '曇り',							'filename' => 'cloud',			'i' => 'F8A0'),
	array('name' => '雨',							'filename' => 'rain',			'i' => 'F8A1'),
	array('name' => '雪',							'filename' => 'snow',			'i' => 'F8A2'),
	array('name' => '雷',							'filename' => 'thunder',		'i' => 'F8A3'),
	array('name' => '台風',							'filename' => 'typhoon',		'i' => 'F8A4'),
	array('name' => '霧',							'filename' => 'mist',			'i' => 'F8A5'),
	array('name' => '小雨',							'filename' => 'sprinkle',		'i' => 'F8A6'),
	array('name' => '牡羊座',						'filename' => 'aries',			'i' => 'F8A7'),
	array('name' => '牡牛座',						'filename' => 'taurus',			'i' => 'F8A8'),
	array('name' => '双子座',						'filename' => 'gemini',			'i' => 'F8A9'),
	array('name' => '蟹座',							'filename' => 'cancer',			'i' => 'F8AA'),
	array('name' => '獅子座',						'filename' => 'leo',			'i' => 'F8AB'),
	array('name' => '乙女座',						'filename' => 'virgo',			'i' => 'F8AC'),
	array('name' => '天秤座',						'filename' => 'libra',			'i' => 'F8AD'),
	array('name' => '蠍座',							'filename' => 'scorpius',		'i' => 'F8AE'),
	array('name' => '射手座',						'filename' => 'sagittarius',	'i' => 'F8AF'),
	array('name' => '山羊座',						'filename' => 'capricornus',	'i' => 'F8B0'),
	array('name' => '水瓶座',						'filename' => 'aquarius',		'i' => 'F8B1'),
	array('name' => '魚座',							'filename' => 'pisces',			'i' => 'F8B2'),
	array('name' => 'スポーツ',						'filename' => 'sports',			'i' => 'F8B3'),
	array('name' => '野球',							'filename' => 'baseball',		'i' => 'F8B4'),
	array('name' => 'ゴルフ',						'filename' => 'golf',			'i' => 'F8B5'),
	array('name' => 'テニス',						'filename' => 'tennis',			'i' => 'F8B6'),
	array('name' => 'サッカー',						'filename' => 'soccer',			'i' => 'F8B7'),
	array('name' => 'スキー',						'filename' => 'ski',			'i' => 'F8B8'),
	array('name' => 'バスケットボール',				'filename' => 'basketball',		'i' => 'F8B9'),
	array('name' => 'モータースポーツ',				'filename' => 'motorsports',	'i' => 'F8BA'),
	array('name' => 'ポケットベル',					'filename' => 'pocketbell',		'i' => 'F8BB'),
	array('name' => '電車',							'filename' => 'train',			'i' => 'F8BC'),
	array('name' => '地下鉄',						'filename' => 'subway',			'i' => 'F8BD'),
	array('name' => '新幹線',						'filename' => 'bullettrain',	'i' => 'F8BE'),
	array('name' => '車(セダン)',					'filename' => 'car',			'i' => 'F8BF'),
	array('name' => '車(ＲＶ)',						'filename' => 'rvcar',			'i' => 'F8C0'),
	array('name' => 'バス',							'filename' => 'bus',			'i' => 'F8C1'),
	array('name' => '船',							'filename' => 'ship',			'i' => 'F8C2'),
	array('name' => '飛行機',						'filename' => 'airplane',		'i' => 'F8C3'),
	array('name' => '家',							'filename' => 'house',			'i' => 'F8C4'),
	array('name' => 'ビル',							'filename' => 'building',		'i' => 'F8C5'),
	array('name' => '郵便局',						'filename' => 'postoffice',		'i' => 'F8C6'),
	array('name' => '病院',							'filename' => 'hospital',		'i' => 'F8C7'),
	array('name' => '銀行',							'filename' => 'bank',			'i' => 'F8C8'),
	array('name' => 'ＡＴＭ',						'filename' => 'atm',			'i' => 'F8C9'),
	array('name' => 'ホテル',						'filename' => 'hotel',			'i' => 'F8CA'),
	array('name' => 'コンビニ',						'filename' => '24hours',		'i' => 'F8CB'),
	array('name' => 'ガソリンスタンド',				'filename' => 'gasstation',		'i' => 'F8CC'),
	array('name' => '駐車場',						'filename' => 'parking',		'i' => 'F8CD'),
	array('name' => '信号',							'filename' => 'signaler',		'i' => 'F8CE'),
	array('name' => 'トイレ',						'filename' => 'toilet',			'i' => 'F8CF'),
	array('name' => 'レストラン',					'filename' => 'restaurant',		'i' => 'F8D0'),
	array('name' => '喫茶店',						'filename' => 'cafe',			'i' => 'F8D1'),
	array('name' => 'バー',							'filename' => 'bar',			'i' => 'F8D2'),
	array('name' => 'ビール',						'filename' => 'beer',			'i' => 'F8D3'),
	array('name' => 'ファーストフード',				'filename' => 'fastfood',		'i' => 'F8D4'),
	array('name' => 'ブティック',					'filename' => 'boutique',		'i' => 'F8D5'),
	array('name' => '美容院',						'filename' => 'hairsalon',		'i' => 'F8D6'),
	array('name' => 'カラオケ',						'filename' => 'karaoke',		'i' => 'F8D7'),
	array('name' => '映画',							'filename' => 'movie',			'i' => 'F8D8'),
	array('name' => '右斜め上',						'filename' => 'upwardright',	'i' => 'F8D9'),
	array('name' => '遊園地',						'filename' => 'carouselpony',	'i' => 'F8DA'),
	array('name' => '音楽',							'filename' => 'music',			'i' => 'F8DB'),
	array('name' => 'アート',						'filename' => 'art',			'i' => 'F8DC'),
	array('name' => '演劇',							'filename' => 'drama',			'i' => 'F8DD'),
	array('name' => 'イベント',						'filename' => 'event',			'i' => 'F8DE'),
	array('name' => 'チケット',						'filename' => 'ticket',			'i' => 'F8DF'),
	array('name' => '喫煙',							'filename' => 'smoking',		'i' => 'F8E0'),
	array('name' => '禁煙',							'filename' => 'nosmoking',		'i' => 'F8E1'),
	array('name' => 'カメラ',						'filename' => 'camera',			'i' => 'F8E2'),
	array('name' => 'カバン',						'filename' => 'bag',			'i' => 'F8E3'),
	array('name' => '本',							'filename' => 'book',			'i' => 'F8E4'),
	array('name' => 'リボン',						'filename' => 'ribbon',			'i' => 'F8E5'),
	array('name' => 'プレゼント',					'filename' => 'present',		'i' => 'F8E6'),
	array('name' => 'バースデー',					'filename' => 'birthday',		'i' => 'F8E7'),
	array('name' => '電話',							'filename' => 'telephone',		'i' => 'F8E8'),
	array('name' => '携帯電話',						'filename' => 'mobilephone',	'i' => 'F8E9'),
	array('name' => 'メモ',							'filename' => 'memo',			'i' => 'F8EA'),
	array('name' => 'ＴＶ',							'filename' => 'tv',				'i' => 'F8EB'),
	array('name' => 'ゲーム',						'filename' => 'game',			'i' => 'F8EC'),
	array('name' => 'ＣＤ',							'filename' => 'cd',				'i' => 'F8ED'),
	array('name' => 'ハート',						'filename' => 'heart',			'i' => 'F8EE'),
	array('name' => 'スペード',						'filename' => 'spade',			'i' => 'F8EF'),
	array('name' => 'ダイヤ',						'filename' => 'diamond',		'i' => 'F8F0'),
	array('name' => 'クラブ',						'filename' => 'club',			'i' => 'F8F1'),
	array('name' => '目',							'filename' => 'eye',			'i' => 'F8F2'),
	array('name' => '耳',							'filename' => 'ear',			'i' => 'F8F3'),
	array('name' => '手(グー)',						'filename' => 'rock',			'i' => 'F8F4'),
	array('name' => '手(チョキ)',					'filename' => 'scissors',		'i' => 'F8F5'),
	array('name' => '手(パー)',						'filename' => 'paper',			'i' => 'F8F6'),
	array('name' => '右斜め下',						'filename' => 'downwardright',	'i' => 'F8F7'),
	array('name' => '左斜め上',						'filename' => 'upwardleft',		'i' => 'F8F8'),
	array('name' => '足',							'filename' => 'foot',			'i' => 'F8F9'),
	array('name' => 'くつ',							'filename' => 'shoe',			'i' => 'F8FA'),
	array('name' => '眼鏡',							'filename' => 'eyeglass',		'i' => 'F8FB'),
	array('name' => '車椅子',						'filename' => 'wheelchair',		'i' => 'F8FC'),
	array('name' => '新月',							'filename' => 'newmoon',		'i' => 'F940'),
	array('name' => 'やや欠け月',					'filename' => 'moon1',			'i' => 'F941'),
	array('name' => '半月',							'filename' => 'moon2',			'i' => 'F942'),
	array('name' => '三日月',						'filename' => 'moon3',			'i' => 'F943'),
	array('name' => '満月',							'filename' => 'fullmoon',		'i' => 'F944'),
	array('name' => '犬',							'filename' => 'dog',			'i' => 'F945'),
	array('name' => '猫',							'filename' => 'cat',			'i' => 'F946'),
	array('name' => 'リゾート',						'filename' => 'yacht',			'i' => 'F947'),
	array('name' => 'クリスマス',					'filename' => 'xmas',			'i' => 'F948'),
	array('name' => '左斜め下',						'filename' => 'downwardleft',	'i' => 'F949'),
	array('name' => 'phone to',						'filename' => 'phoneto',		'i' => 'F972'),
	array('name' => 'mail to',						'filename' => 'mailto',			'i' => 'F973'),
	array('name' => 'fax to',						'filename' => 'faxto',			'i' => 'F974'),
	array('name' => 'iモード',						'filename' => 'info01',			'i' => 'F975'),
	array('name' => 'iモード(枠付き)',				'filename' => 'info02',			'i' => 'F976'),
	array('name' => 'メール',						'filename' => 'mail',			'i' => 'F977'),
	array('name' => 'ドコモ提供',					'filename' => 'by-d',			'i' => 'F978'),
	array('name' => 'ドコモポイント',				'filename' => 'd-point',		'i' => 'F979'),
	array('name' => '有料',							'filename' => 'yen',			'i' => 'F97A'),
	array('name' => '無料',							'filename' => 'free',			'i' => 'F97B'),
	array('name' => 'ID',							'filename' => 'id',				'i' => 'F97C'),
	array('name' => 'パスワード',					'filename' => 'key',			'i' => 'F97D'),
	array('name' => '次項有',						'filename' => 'enter',			'i' => 'F97E'),
	array('name' => 'クリア',						'filename' => 'clear',			'i' => 'F980'),
	array('name' => 'サーチ(調べる)',				'filename' => 'search',			'i' => 'F981'),
	array('name' => 'ＮＥＷ',						'filename' => 'new',			'i' => 'F982'),
	array('name' => '位置情報',						'filename' => 'flag',			'i' => 'F983'),
	array('name' => 'フリーダイヤル',				'filename' => 'freedial',		'i' => 'F984'),
	array('name' => 'シャープダイヤル',				'filename' => 'sharp',			'i' => 'F985'),
	array('name' => 'モバＱ',						'filename' => 'mobaq',			'i' => 'F986'),
	array('name' => '1',							'filename' => 'one',			'i' => 'F987'),
	array('name' => '2',							'filename' => 'two',			'i' => 'F988'),
	array('name' => '3',							'filename' => 'three',			'i' => 'F989'),
	array('name' => '4',							'filename' => 'four',			'i' => 'F98A'),
	array('name' => '5',							'filename' => 'five',			'i' => 'F98B'),
	array('name' => '6',							'filename' => 'six',			'i' => 'F98C'),
	array('name' => '7',							'filename' => 'seven',			'i' => 'F98D'),
	array('name' => '8',							'filename' => 'eight',			'i' => 'F98E'),
	array('name' => '9',							'filename' => 'nine',			'i' => 'F98F'),
	array('name' => '0',							'filename' => 'zero',			'i' => 'F990'),
	array('name' => '決定',							'filename' => 'ok',				'i' => 'F9B0'),
	array('name' => '黒ハート',						'filename' => 'heart01',		'i' => 'F991'),
	array('name' => '揺れるハート',					'filename' => 'heart02',		'i' => 'F992'),
	array('name' => '失恋',							'filename' => 'heart03',		'i' => 'F993'),
	array('name' => 'ハートたち(複数ハート)',		'filename' => 'heart04',		'i' => 'F994'),
	array('name' => 'わーい(嬉しい顔)',				'filename' => 'happy01',		'i' => 'F995'),
	array('name' => 'ちっ(怒った顔)',				'filename' => 'angry',			'i' => 'F996'),
	array('name' => 'がく～(落胆した顔)',			'filename' => 'despair',		'i' => 'F997'),
	array('name' => 'もうやだ～(悲しい顔)',			'filename' => 'sad',			'i' => 'F998'),
	array('name' => 'ふらふら',						'filename' => 'wobbly',			'i' => 'F999'),
	array('name' => 'グッド(上向き矢印)',			'filename' => 'up',				'i' => 'F99A'),
	array('name' => 'るんるん',						'filename' => 'note',			'i' => 'F99B'),
	array('name' => 'いい気分(温泉)',				'filename' => 'spa',			'i' => 'F99C'),
	array('name' => 'かわいい',						'filename' => 'cute',			'i' => 'F99D'),
	array('name' => 'キスマーク',					'filename' => 'kissmark', 		'i' => 'F99E'),
	array('name' => 'ぴかぴか(新しい)',				'filename' => 'shine',			'i' => 'F99F'),
	array('name' => 'ひらめき',						'filename' => 'flair',			'i' => 'F9A0'),
	array('name' => 'むかっ(怒り)',					'filename' => 'annoy',			'i' => 'F9A1'),
	array('name' => 'パンチ',						'filename' => 'punch',			'i' => 'F9A2'),
	array('name' => '爆弾',							'filename' => 'bomb',			'i' => 'F9A3'),
	array('name' => 'ムード',						'filename' => 'notes',			'i' => 'F9A4'),
	array('name' => 'バッド(下向き矢印)',			'filename' => 'down',			'i' => 'F9A5'),
	array('name' => '眠い(睡眠)',					'filename' => 'sleepy',			'i' => 'F9A6'),
	array('name' => 'exclamation',					'filename' => 'sign01',			'i' => 'F9A7'),
	array('name' => 'exclamation&question',			'filename' => 'sign02',			'i' => 'F9A8'),
	array('name' => 'exclamation×2',				'filename' => 'sign03',			'i' => 'F9A9'),
	array('name' => 'どんっ(衝撃)',					'filename' => 'impact',			'i' => 'F9AA'),
	array('name' => 'あせあせ(飛び散る汗)',			'filename' => 'sweat01',		'i' => 'F9AB'),
	array('name' => 'たらーっ(汗)',					'filename' => 'sweat02',		'i' => 'F9AC'),
	array('name' => 'ダッシュ(走り出すさま)',		'filename' => 'dash',			'i' => 'F9AD'),
	array('name' => 'ー(長音記号１)',				'filename' => 'sign04',			'i' => 'F9AE'),
	array('name' => 'ー(長音記号２)',				'filename' => 'sign05',			'i' => 'F9AF'),
	array('name' => 'カチンコ',						'filename' => 'slate',			'i' => 'F950'),
	array('name' => 'ふくろ',						'filename' => 'pouch',			'i' => 'F951'),
	array('name' => 'ペン',							'filename' => 'pen',			'i' => 'F952'),
	array('name' => '人影',							'filename' => 'shadow',			'i' => 'F955'),
	array('name' => 'いす',							'filename' => 'chair',			'i' => 'F956'),
	array('name' => '夜',							'filename' => 'night',			'i' => 'F957'),
	array('name' => 'soon',							'filename' => 'soon',			'i' => 'F95B'),
	array('name' => 'on',							'filename' => 'on',				'i' => 'F95C'),
	array('name' => 'end',							'filename' => 'end',			'i' => 'F95D'),
	array('name' => '時計',							'filename' => 'clock',			'i' => 'F95E'),
	array('name' => 'iアプリ',						'filename' => 'appli01',		'i' => 'F9B1'),
	array('name' => 'iアプリ(枠付き)',				'filename' => 'appli02',		'i' => 'F9B2'),
	array('name' => 'Tシャツ(ボーダー)',			'filename' => 't-shirt',		'i' => 'F9B3'),
	array('name' => 'がま口財布',					'filename' => 'moneybag',		'i' => 'F9B4'),
	array('name' => '化粧',							'filename' => 'rouge',			'i' => 'F9B5'),
	array('name' => 'ジーンズ',						'filename' => 'denim',			'i' => 'F9B6'),
	array('name' => 'スノボ',						'filename' => 'snowboard',		'i' => 'F9B7'),
	array('name' => 'チャペル',						'filename' => 'bell',			'i' => 'F9B8'),
	array('name' => 'ドア',							'filename' => 'door',			'i' => 'F9B9'),
	array('name' => 'ドル袋',						'filename' => 'dollar',			'i' => 'F9BA'),
	array('name' => 'パソコン',						'filename' => 'pc',				'i' => 'F9BB'),
	array('name' => 'ラブレター',					'filename' => 'loveletter',		'i' => 'F9BC'),
	array('name' => 'レンチ',						'filename' => 'wrench',			'i' => 'F9BD'),
	array('name' => '鉛筆',							'filename' => 'pencil',			'i' => 'F9BE'),
	array('name' => '王冠',							'filename' => 'crown',			'i' => 'F9BF'),
	array('name' => '指輪',							'filename' => 'ring',			'i' => 'F9C0'),
	array('name' => '砂時計',						'filename' => 'sandclock',		'i' => 'F9C1'),
	array('name' => '自転車',						'filename' => 'bicycle',		'i' => 'F9C2'),
	array('name' => '湯のみ',						'filename' => 'japanesetea',	'i' => 'F9C3'),
	array('name' => '腕時計',						'filename' => 'watch',			'i' => 'F9C4'),
	array('name' => '考えてる顔',					'filename' => 'think',			'i' => 'F9C5'),
	array('name' => 'ほっとした顔',					'filename' => 'confident',		'i' => 'F9C6'),
	array('name' => '冷や汗',						'filename' => 'coldsweats01',	'i' => 'F9C7'),
	array('name' => '冷や汗2',						'filename' => 'coldsweats02',	'i' => 'F9C8'),
	array('name' => 'ぷっくっくな顔',				'filename' => 'pout',			'i' => 'F9C9'),
	array('name' => 'ボケーっとした顔', 			'filename' => 'gawk',			'i' => 'F9CA'),
	array('name' => '目がハート',					'filename' => 'lovely',			'i' => 'F9CB'),
	array('name' => '指でOK',						'filename' => 'good',			'i' => 'F9CC'),
	array('name' => 'あっかんべー',					'filename' => 'bleah',			'i' => 'F9CD'),
	array('name' => 'ウィンク',						'filename' => 'wink',			'i' => 'F9CE'),
	array('name' => 'うれしい顔',					'filename' => 'happy02',		'i' => 'F9CF'),
	array('name' => 'がまん顔',						'filename' => 'bearing',		'i' => 'F9D0'),
	array('name' => '猫2',							'filename' => 'catface',		'i' => 'F9D1'),
	array('name' => '泣き顔',						'filename' => 'crying',			'i' => 'F9D2'),
	array('name' => '涙',							'filename' => 'weep',			'i' => 'F9D3'),
	array('name' => 'NG',							'filename' => 'ng',				'i' => 'F9D4'),
	array('name' => 'クリップ',						'filename' => 'clip',			'i' => 'F9D5'),
	array('name' => 'コピーライト',					'filename' => 'copyright',		'i' => 'F9D6'),
	array('name' => 'トレードマーク',				'filename' => 'tm',				'i' => 'F9D7'),
	array('name' => '走る人',						'filename' => 'run',			'i' => 'F9D8'),
	array('name' => 'マル秘',						'filename' => 'secret',			'i' => 'F9D9'),
	array('name' => 'リサイクル',					'filename' => 'recycle',		'i' => 'F9DA'),
	array('name' => 'レジスタードトレードマーク',	'filename' => 'r-mark',			'i' => 'F9DB'),
	array('name' => '危険・警告',					'filename' => 'danger',			'i' => 'F9DC'),
	array('name' => '禁止',							'filename' => 'ban',			'i' => 'F9DD'),
	array('name' => '空室・空席・空車',				'filename' => 'empty',			'i' => 'F9DE'),
	array('name' => '合格マーク',					'filename' => 'pass',			'i' => 'F9DF'),
	array('name' => '満室・満席・満車',				'filename' => 'full',			'i' => 'F9E0'),
	array('name' => '矢印左右',						'filename' => 'leftright',		'i' => 'F9E1'),
	array('name' => '矢印上下',						'filename' => 'updown',			'i' => 'F9E2'),
	array('name' => '学校',							'filename' => 'school',			'i' => 'F9E3'),
	array('name' => '波',							'filename' => 'wave',			'i' => 'F9E4'),
	array('name' => '富士山',						'filename' => 'fuji',			'i' => 'F9E5'),
	array('name' => 'クローバー',					'filename' => 'clover',			'i' => 'F9E6'),
	array('name' => 'さくらんぼ',					'filename' => 'cherry',			'i' => 'F9E7'),
	array('name' => 'チューリップ',					'filename' => 'tulip',			'i' => 'F9E8'),
	array('name' => 'バナナ',						'filename' => 'banana',			'i' => 'F9E9'),
	array('name' => 'りんご',						'filename' => 'apple',			'i' => 'F9EA'),
	array('name' => '芽',							'filename' => 'bud',			'i' => 'F9EB'),
	array('name' => 'もみじ',						'filename' => 'maple',			'i' => 'F9EC'),
	array('name' => '桜',							'filename' => 'cherryblossom',	'i' => 'F9ED'),
	array('name' => 'おにぎり',						'filename' => 'riceball',		'i' => 'F9EE'),
	array('name' => 'ショートケーキ',				'filename' => 'cake',			'i' => 'F9EF'),
	array('name' => 'とっくり(おちょこ付き)',		'filename' => 'bottle',			'i' => 'F9F0'),
	array('name' => 'どんぶり',						'filename' => 'noodle',			'i' => 'F9F1'),
	array('name' => 'パン',							'filename' => 'bread',			'i' => 'F9F2'),
	array('name' => 'かたつむり',					'filename' => 'snail',			'i' => 'F9F3'),
	array('name' => 'ひよこ',						'filename' => 'chick',			'i' => 'F9F4'),
	array('name' => 'ペンギン',						'filename' => 'penguin',		'i' => 'F9F5'),
	array('name' => '魚',							'filename' => 'fish',			'i' => 'F9F6'),
	array('name' => 'うまい！',						'filename' => 'delicious',		'i' => 'F9F7'),
	array('name' => 'ウッシッシ',					'filename' => 'smile',			'i' => 'F9F8'),
	array('name' => 'ウマ',							'filename' => 'horse',			'i' => 'F9F9'),
	array('name' => 'ブタ',							'filename' => 'pig',			'i' => 'F9FA'),
	array('name' => 'ワイングラス',					'filename' => 'wine',			'i' => 'F9FB'),
	array('name' => 'げっそり',						'filename' => 'shock',			'i' => 'F9FC')
);
?>
