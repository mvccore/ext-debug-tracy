<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Debugs\Tracys;

/**
 * Responsibility - after all scripts are done, display all used PHP files in it's own `Tracy` debug bar.
 * - Generate for all used PHP files their file debug links.
 * - If any used file is used by tracy debug bar - mark this file in debug panel by different css colour.
 */
class IncludePanel implements \Tracy\IBarPanel {

	/**
	 * All used PHP files list by current request.
	 * @var \string[]
	 */
	protected static $files = [];

	/**
	 * Used PHP files count except files used by `Tracy` extension.
	 * @var mixed
	 */
	protected static $appFilesCount = 0;

	/**
	 * All used PHP files count.
	 * @var int
	 */
	protected static $allFilesCount = 0;

	/**
	 * Get unique `Tracy` debug bar panel id.
	 * @return string
	 */
	public function getId() {
		return 'include-panel';
	}

	/**
	 * Return rendered debug panel heading HTML code displayed all time in `Tracy` debug  bar.
	 * @return string
	 */
	public function getTab() {
		static::completeFilesCountsAndEditorLinks();
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAADtUExURQAAAANAWAULDjxIVExOUQsKCA4nMANOaxYyPQJCWg0ZHgJQbQYMDyM8UzVrnmhXHl1eYm5uaHx8gQolLVJfaz9PXm2TtrOPIH1wMV5TJ29kLQI8UtWtJkhKK3FmKtOvLAQ1SARQbTMqHIyMjISEicLCz6zS9GdyeN/f6vf3+Vai6qOjymtrrb3Z83y37o3A8Obt9TaI03dxWdDY3ZmannJyr1BQoXeAhMPH3dTU37291rXG183j+Nbo+WWQuWqk2mCo6om+72Ko61JRQnN+hUczHJGepiBNd2ROJzhaeJijpF1dpkdHm5iYxe7u8lzeC4UAAAAkdFJOUwACQpD4JkALx2Z+RU/8/dr9+f28q6H982I0Snb1y/Pvn1zx+/LnWboAAAC0SURBVBjTbczVFoJAFIXhoUtQ7M4RKUUBBbs73v9xZAD1xu/u/GuvAwCC4QSBgx8mO18oKx77hlx/uFQGIv8ZUXmlr1gb8UAmopBYb63BbnQ8uXK0oSRd10d72fG8DINCqX6Wrhfb0TSNRaHcSGba9s1XVT8MxWqSI1NDYzKZPV9BoAq1FgOhen+YhqmiBS1goAOn5nhmjKfhjwBMdyOfkHZ7AZJlm3EgIZKqMPENaC4kgD/e1akVKEqC52oAAAAASUVORK5CYII="/>'
			. self::$appFilesCount;
	}

	/**
	 * Return rendered debug panel content window HTML code.
	 * @return string
	 */
	public function getPanel() {
		self::completeFilesCountsAndEditorLinks();
		$usedFilesListCode = join("", static::$files);
		return '<style type="text/css">'
				.'#tracy-include-panel{overflow:hidden;}'
				.'.tracy-mode-float #tracy-include-panel{overflow:visible;}'
				.'#tracy-include-panel .content a{white-space:nowrap;}'
				.'#tracy-include-panel .content .tracy{color:#7a91a9;background:#eee;}'
			.'</style>'
			.'<div id="tracy-include-panel">'
				.'<h1>Included app files: '.static::$appFilesCount.' (all: '.static::$allFilesCount.')</h1>'
				.'<div class="content"><code>'
					.$usedFilesListCode
				.'<code></div>'
			.'</div>';
	}

	/**
	 * Complete final used PHP files list as list of HTML codes with editor links.
	 * @return void
	 */
	protected static function completeFilesCountsAndEditorLinks () {
		if (!static::$files) {
			$rawList = get_included_files();
			$list = [];
			$docRoot = \MvcCore\Application::GetInstance()->GetRequest()->GetAppRoot();
			$docRootLength = mb_strlen($docRoot);
			$tracyFileDetectionSubstr = '/tracy';
			foreach ($rawList as $file) {
				$file = str_replace('\\', '/', $file);
				$text = (mb_strpos($file, $docRoot) === 0)
					? mb_substr($file, $docRootLength)
					: $file;
				$tracyFile = mb_stripos($text, $tracyFileDetectionSubstr) !== FALSE;
				if (!$tracyFile) static::$appFilesCount += 1;
				static::$allFilesCount += 1;
				$href = \Tracy\Helpers::editorUri($file, 1);
				$list[] = '<a '.($tracyFile ? 'class="tracy" ':'').'href="'.$href.'">'.$text.'</a><br />';
			}
			static::$files = & $list;
		}
	}
}
