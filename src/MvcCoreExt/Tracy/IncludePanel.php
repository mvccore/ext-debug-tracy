<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/3.0.0/LICENCE.md
 */

class MvcCoreExt_Tracy_IncludePanel implements Tracy\IBarPanel {
	protected static $files = array();
	protected static $appFilesCount = 0;
	protected static $allFilesCount = 0;
	public function getId() {
		return 'include-panel';
	}
	public function getTab() {
		static::getFiles();
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACt0lEQVQ4y62TW0iTcRjGn28nv43NzW14nM506s0UJDMVyo4k2RwolpJUhEUSyyiCCsSrSsrIKLuqoDAsBCVIQsWyoDLLQyhGU1Gz2jyxT79t7vR9/y5Kc+ZFFz2XD8//B+/z/l8h1pFMJhOEqtS5m3fkWyL1KSZm3s75vJ4x/ItCQ5WywsMnm1rffia9Ux7S9MlJjtW1EXl0wnmYLYK1eeFaIyvvwN0z1bWlaQmR4AhgZzmwkgiMDfVtxWAH7XWxADC+nA8iSqVSQ9qukjJXQATrnB9fZv2YYjjYWA5SQUBSfrziommfqZXWRhUsvxGtBvj9fiMXZhBY5/wQCygseHhMMRysk3bo3R8QF18M44ZoWh8f/+h2wxMTHNNdQSPQNA1EpJ7wKRMFNpbDhCOA0W+z0PVUIik1A+aC/Xjd/R5CWiYZHJ/SBXJLmqjVgENFKRkajfBli80kV+mSQS+MwOhsQZghFQcrriEpOmola9i2p3smbuPulQ4qy1P3nisTdwjcC3P24WcvhK+qF7eH25BRXAVzaQ0UAgWmp13o7f0BsVgICtSfDqpOpx8t2eK6VfMwMNHImevzjPZLWrVadurCVbQ9H8X1y29ACIHFkoWRkXkYjVrwhPzagkoRkpypd9w8e0883Ejyr/Aiyf3szE1yQ2KiyMV60Nk5Crfbh6goOez2RQwN2eF0LoH8BogY1ms98iC8iEnIFhEe7XxzXSAkpxY+XwBLSx6oVBJoNDRMpiTYbC7QtBAsuwoAAPMDPe0Y6FkpyOv1w+Pxg2EWUViYAAAQiznExoYgJiYODgcDnuf//gfLcru9iIjWoaHlcZD/fXIC76zjHymRZJERK74SQtYH9Pf3IUcqh06tCfKdM9NwKGOaqHD9HQLC88033NR6ACp9Zw6l1BrXvTal1so/re/C/9JPOxcb0VoXrMkAAAAASUVORK5CYII="/>'
			. self::$appFilesCount;
	}
	public function getPanel() {
		self::getFiles();
		$userListCode = join("", static::$files);
		return '<style type="text/css">'
				.'#tracy-include-panel .content{overflow:auto;max-width:800px;max-height:800px;}'
				.'#tracy-include-panel .content .tracy{color:#7a91a9;background:#eee;}'
			.'</style>'
			.'<div id="tracy-include-panel">'
				.'<h1>Included app files: '.static::$appFilesCount.' (all: '.static::$allFilesCount.')</h1>'
				.'<div class="content"><code>'
					.$userListCode
				.'<code></div>'
			.'</div>';
	}
	protected static function getFiles () {
		if (!static::$files) {
			$rawList = get_included_files();
			$list = array();
			$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
			$docRootLength = mb_strlen($docRoot);
			$tracyFileDetectionSubstr = '/tracy';
			foreach ($rawList as & $file) {
				$file = str_replace('\\', '/', $file);
				$text = mb_substr($file, $docRootLength);
				$tracyFile = mb_stripos($text, $tracyFileDetectionSubstr) !== FALSE;
				if (!$tracyFile) static::$appFilesCount += 1;
				static::$allFilesCount += 1;
				$href = Tracy\Helpers::editorUri($file, 1);
				$list[] = '<a '.($tracyFile ? 'class="tracy" ':'').'href="'.$href.'"><nobr>'.$text.'</nobr></a><br />';
			}
			static::$files = & $list;
		}
	}
}

