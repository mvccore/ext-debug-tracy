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
	protected $files = [];

	/**
	 * Used PHP files count except files used by `Tracy` extension.
	 * @var mixed
	 */
	protected $appFilesCount = 0;

	/**
	 * All used PHP files count.
	 * @var int
	 */
	protected $allFilesCount = 0;

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
			. $this->appFilesCount;
	}

	/**
	 * Return rendered debug panel content window HTML code.
	 * @return string
	 */
	public function getPanel() {
		self::completeFilesCountsAndEditorLinks();
		$usedFilesListCode = join("", $this->files);
		$nonce = $nonce = version_compare(\Tracy\Debugger::Version, '2.10.8', '>=')
			? \Tracy\Helpers::getNonceAttr()
			: \Tracy\Helpers::getNonce();
		$nonceAttr = $nonce ? ' nonce="' . \Tracy\Helpers::escapeHtml($nonce) . '"' : '';
		return '<style type="text/css"'.$nonceAttr.'>'
				.'#tracy-include-panel{overflow:hidden;}'
				.'.tracy-mode-float #tracy-include-panel{overflow:visible;}'
				.'#tracy-debug #tracy-include-panel h1{word-wrap: normal;}'
				.'#tracy-include-panel .content a{white-space:nowrap;}'
				.'#tracy-include-panel .content .tracy{color:#7a91a9;background:#eee;}'
			.'</style>'
			.'<div id="tracy-include-panel">'
				.'<h1>Included app files: '.$this->appFilesCount.' (all: '.$this->allFilesCount.')</h1>'
				.'<div class="content"><code>'
					.$usedFilesListCode
				.'<code></div>'
			.'</div>';
	}

	/**
	 * Complete final used PHP files list as list of HTML codes with editor links.
	 * @return void
	 */
	protected function completeFilesCountsAndEditorLinks () {
		if (!$this->files) {
			$rawList = get_included_files();
			$list = [];
			$appRoot = \MvcCore\Application::GetInstance()->GetPathAppRoot();
			$appRootLen = mb_strlen($appRoot);
			$tracyFileDetectionSubstr = '/tracy';
			foreach ($rawList as $file) {
				$file = str_replace('\\', '/', $file);
				$text = $this->getVisibleFilePath($file, $appRoot, $appRootLen);
				$tracyFile = mb_stripos($text, $tracyFileDetectionSubstr) !== FALSE;
				if (!$tracyFile) $this->appFilesCount += 1;
				$this->allFilesCount += 1;
				$href = \Tracy\Helpers::editorUri($file, 1);
				$list[] = '<a '.($tracyFile ? 'class="tracy" ':'').'href="'.$href.'">'.$text.'</a><br />';
			}
			$this->files = & $list;
		}
	}

	/**
	 * Return file path to render in link text.
	 * If there is found application root in path, 
	 * return only path after it, if not, return 
	 * three dots, two parent folders and filename.
	 * @param  string $file 
	 * @param  string $appRoot 
	 * @param  int    $appRootLen 
	 * @return string
	 */
	protected function getVisibleFilePath ($file, $appRoot, $appRootLen) {
		$result = $file;
		if (mb_strpos($file, $appRoot) === 0) {
			$result = mb_substr($file, $appRootLen);
		} else {
			$i = 0;
			$pos = mb_strlen($file) + 1;
			while ($i < 5) {
				$pos = mb_strrpos(mb_substr($file, 0, $pos - 1), '/');
				if ($pos === FALSE) break; 
				$i++;
			}
			if ($pos === FALSE) {
				$result = $file;
			} else {
				$result = '&hellip;'.mb_substr($file, $pos);
			}
		}
		return $result;
	}
}
