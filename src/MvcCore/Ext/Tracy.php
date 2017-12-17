<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Debug {

	// Tracy library main class:
	//require_once('Tracy/Debugger.php');

	// MvcCore Tracy extension panel:
	require_once('Tracy/IncludePanel.php');

	class Tracy extends \MvcCore\Debug {
		/**
		 * MvcCore Extension - Debug Tracy - version:
		 * Comparation by PHP function version_compare();
		 * @see http://php.net/manual/en/function.version-compare.php
		 */
		const VERSION = '4.3.1';
		/**
		 * Auto initialize all panel classes if exists in registry bellow.
		 * @var bool
		 */
		public static $autoInitPanels = TRUE;
		/**
		 * Extended Tracy panels registry for automatic panel initialization.
		 * If panel class exists, it is automaticly created and registred into Tracy bar.
		 * @var string[]
		 */
		public static $ExtendedPanels = array(
			'MvcCorePanel',
			'SessionPanel',
			'RoutingPanel',
			'AuthPanel',
			// 'IncludePanel', // added every time strictly, not in foreach
		);
		/**
		 * Add editor key for every Tracy editor link
		 * to open your files in specific editor.
		 * @var string
		 */
		public static $Editor = '';

		/**
		 * Initialize global development shorthands.
		 * @param string $logDirectory relative path from app root
		 * @var callable
		 */
		public static $InitGlobalShortHands = array();

		/**
		 * Initialize debuging and loging.
		 * @param boolean $debugMode TRUE for development, FALSE for production.
		 * @return void
		 */
		public static function Init () {
			if (!is_null(static::$development)) return;
			parent::Init();
			\Tracy\Debugger::$maxDepth = 4;
			if (isset(\Tracy\Debugger::$maxLen)) { // backwards compatibility
				\Tracy\Debugger::$maxLen = 5000;
			} else {
				\Tracy\Debugger::$maxLength = 5000;
			}
			if (static::$Editor) \Tracy\Debugger::$editor .= '&editor=' . static::$Editor;
			$tracyBar = \Tracy\Debugger::getBar();
			foreach (static::$ExtendedPanels as $panelName) {
				$panelName = '\MvcCore\Ext\Debug\Tracy\\' . $panelName;
				if (class_exists($panelName)) {
					$panel = new $panelName();
					$tracyBar->addPanel($panel, $panel->getId());
				}
			}
			$includePanel = new \MvcCore\Ext\Debug\Tracy\IncludePanel();
			$tracyBar->addPanel($includePanel, $includePanel->getId());
			\Tracy\Debugger::enable(!static::$development, static::$LogDirectory, static::$EmailRecepient);
		}

		/**
		 * Initialize debuging and loging handlers.
		 * @return void
		 */
		protected static function initHandlers () {
			foreach (static::$handlers as $key => $value) {
				static::$handlers[$key] = array('\Tracy\Debugger', $key);
			}
			static::$handlers = (object) static::$handlers;
		}
	}
}

namespace {
	\MvcCore\Ext\Debug\Tracy::$InitGlobalShortHands = function () {
		/**
			* Dump a variable in Tracy Debug Bar.
			* @param	mixed	$value		variable to dump
			* @param	string	$title		optional title
			* @param	array	$options	dumper options
			* @return	mixed				variable itself
			*/
		function x ($value, $title = NULL, $options = array()) {
			return \Tracy\Debugger::barDump($value, $title, $options);
		}
		/**
			* Dumps variables about a variable in Tracy Debug Bar.
			* @param	...mixed	variables to dump
			* @return	void
			*/
		function xx () {
			$args = func_get_args();
			foreach ($args as $arg) \Tracy\Debugger::barDump($arg);
		}
		/**
			* Dump a variable in Tracy Debug Bar and die. If no variable, throw stop exception.
			* @param	mixed		variable to dump
			* @param	string		optional title
			* @param	array		dumper options
			* @throws	\Exception
			* @return	void
			*/
		function xxx ($var = NULL) {
			$args = func_get_args();
			if (count($args) === 0) {
				throw new \Exception("Stopped.");
			} else {
				@header("Content-Type: text/html; charset=utf-8");
				foreach ($args as $arg) \Tracy\Debugger::barDump($arg);
			}
			echo ob_get_clean();
			die();
		}
	};
}