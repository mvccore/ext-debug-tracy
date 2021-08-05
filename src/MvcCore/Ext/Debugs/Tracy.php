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

namespace MvcCore\Ext\Debugs {

	// Tracy exterrnal library main class:
	//require_once('Tracy/Debugger.php');

	// MvcCore Tracy extension panel:
	require_once('Tracys/IncludePanel.php');

	/**
	 * Responsibility - any development and logging messages and exceptions printing and logging by `Tracy`.
	 * - Printing any variable in content body by `Tracy`.
	 * - Printing any variable in `Tracy` debug bar.
	 * - Caught exceptions printing by `Tracy`.
	 * - Any variables and caught exceptions file logging by `Tracy`.
	 * - Time printing by `Tracy`.
	 */
	class Tracy extends \MvcCore\Debug {

		/**
		 * MvcCore Extension - Debug Tracy - version:
		 * Comparison by PHP function version_compare();
		 * @see http://php.net/manual/en/function.version-compare.php
		 */
		const VERSION = '5.0.4';

		/**
		 * Extended Tracy panels registry for automatic panel initialization.
		 * If panel class exists in `\MvcCore\Ext\Debugs\Tracys\<PanelClassName>`,
		 * it's automatically created and registered into Tracy debug bar.
		 * @var string[]
		 */
		public static $ExtendedPanels = [
			'MvcCorePanel',
			'SessionPanel',
			'RoutingPanel',
			'DbPanel',
			'AuthPanel',
			// 'IncludePanel', // created and registered every time by default as the last one
		];

		/**
		 * Add editor key for every Tracy editor link
		 * to open your files in specific editor.
		 * @var string
		 */
		public static $Editor = '';

		/**
		 * Initialize debugging and logging, once only.
		 * @param bool $forceDevelopmentMode If defined as `TRUE` or `FALSE`,
		 *								   debug mode will be set not by config but by this value.
		 * @return void
		 */
		public static function Init ($forceDevelopmentMode = NULL) {
			if (static::$debugging !== NULL) return;
			$strictExceptionsModeLocal = static::$strictExceptionsMode;
			static::$strictExceptionsMode = FALSE;
			parent::Init($forceDevelopmentMode);
			\Tracy\Debugger::$maxDepth = 4;
			if (isset(\Tracy\Debugger::$maxLen)) { // backwards compatibility
				\Tracy\Debugger::$maxLen = 5000;
			} else {
				\Tracy\Debugger::$maxLength = 5000;
			}
			// if there is any editor string defined - add editor param into all file debug links
			if (static::$Editor) \Tracy\Debugger::$editor .= '&editor=' . static::$Editor;
			// automatically initialize all classes in `\MvcCore\Ext\Debugs\Tracys\<PanelClassName>`
			// which implements `\Tracy\IBarPanel` and add those instances into tracy debug bar:
			$tracyBar = \Tracy\Debugger::getBar();
			$toolClass = static::$app->GetToolClass();
			$selfClass = get_class();
			foreach (static::$ExtendedPanels as $panelName) {
				$panelName = '\\'.$selfClass.'s\\' . $panelName;
				if (class_exists($panelName) && $toolClass::CheckClassInterface($panelName, 'Tracy\\IBarPanel', FALSE, FALSE)) {
					$panel = new $panelName();
					$tracyBar->addPanel($panel, $panel->getId());
				}
			}
			// all include panel every time as the last one
			$includePanel = new \MvcCore\Ext\Debugs\Tracys\IncludePanel();
			$tracyBar->addPanel($includePanel, $includePanel->getId());
			if (!static::$logDirectoryInitialized) static::initLogDirectory();
			/** @var \MvcCore\Session $sessionClass */
			$sessionClass = static::$app->GetSessionClass();
			$sessionClass::Start();
			$sysCfgDebug = static::getSystemCfgDebugSection();
			static::$EmailRecepient = isset($sysCfgDebug['emailRecepient'])
				? $sysCfgDebug['emailRecepient']
				: static::$EmailRecepient;
			\Tracy\Debugger::enable(!static::$debugging, static::$LogDirectory, static::$EmailRecepient);
			if ($strictExceptionsModeLocal !== FALSE)
				self::initStrictExceptionsMode($strictExceptionsModeLocal);
		}

		/**
		 * Initialize debugging and logging handlers.
		 * @return void
		 */
		protected static function initHandlers () {
			foreach (static::$handlers as $key => $value) {
				static::$handlers[$key] = ['\\Tracy\\Debugger', $key];
			}
			//register_shutdown_function(self::$handlers['shutdownHandler']); // already registered inside tracy debugger
		}
	}
}

namespace {
	\MvcCore\Ext\Debugs\Tracy::$InitGlobalShortHands = function ($debugging) {
		/**
		 * Dump a variable in Tracy Debug Bar.
		 * @tracySkipLocation
		 * @param	mixed	$value		Variable to dump.
		 * @param	string	$title		Optional title.
		 * @param	array	$options	Dumper options.
		 * @return	mixed				Variable itself.
		 */
		function x ($value, $title = NULL, $options = []) {
			$options[\Tracy\Dumper::LOCATION]	= TRUE;
			$options[\Tracy\Dumper::TRUNCATE]	= 0;
			$options[\Tracy\Dumper::DEBUGINFO]	= TRUE;
			if (PHP_SAPI === 'cli') {
				if ($title !== NULL) echo $title . ':' . PHP_EOL;
				return \Tracy\Dumper::dump($value, $options);
			} else {
				return \Tracy\Debugger::barDump($value, $title, $options);
			}
		}
		/**
		 * Dumps variables about a variable in Tracy Debug Bar.
		 * @tracySkipLocation
		 * @param  ...mixed  Variables to dump.
		 * @return	void
		 */
		function xx () {
			$args = func_get_args();
			$isCli = PHP_SAPI === 'cli';
			$options = [
				\Tracy\Dumper::LOCATION		=> TRUE,
				\Tracy\Dumper::TRUNCATE		=> 0,
				\Tracy\Dumper::DEBUGINFO	=> TRUE
			];
			foreach ($args as $arg) {
				if ($isCli) {
					\Tracy\Dumper::dump($arg, $options);
				} else {
					\Tracy\Debugger::barDump($arg, NULL, $options);
				}
			}
		}

		if ($debugging) {
			/**
			 * Dump variables and die. If no variable, throw stop exception.
			 * @param mixed $args,...	Variables to dump.
			 * @tracySkipLocation
			 * @throws \Exception
			 * @return void
			 */
			function xxx ($args = NULL) {
				$args = func_get_args();
				if (count($args) === 0) {
					throw new \ErrorException('Stopped.', 500);
				} else {
					if (!headers_sent())
						@header('Content-Type: text/html');
					$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
					$isCli = PHP_SAPI === 'cli';
					$options = [
						\Tracy\Dumper::LOCATION		=> TRUE,
						\Tracy\Dumper::TRUNCATE		=> 0,
						\Tracy\Dumper::DEBUGINFO	=> TRUE,
						\Tracy\Dumper::LIVE			=> FALSE,
					];
					foreach ($args as $arg) {
						if ($isCli) {
							\Tracy\Dumper::dump($arg, $options);
						} else {
							echo \Tracy\Dumper::toHtml($arg, $options);
						}
					}
					exit;
				}
			}
		} else {
			/**
			 * Log variables and die. If no variable, throw stop exception.
			 * @param mixed $args,... Variables to dump.
			 * @tracySkipLocation
			 * @throws \Exception
			 * @return void
			 */
			function xxx ($args = NULL) {
				$args = func_get_args();
				$isCli = PHP_SAPI === 'cli';
				$options = [
					\Tracy\Dumper::LOCATION		=> TRUE,
					\Tracy\Dumper::TRUNCATE		=> 0,
					\Tracy\Dumper::DEBUGINFO	=> TRUE,
					\Tracy\Dumper::LIVE			=> FALSE,
				];
				if (count($args) > 0) {
					foreach ($args as $arg) {
						if ($isCli) {
							\Tracy\Dumper::dump($arg, $options);
						} else {
							\Tracy\Debugger::log($arg, \Tracy\ILogger::DEBUG);
						}
					}
				}
				if ($isCli) {
					echo 'Stopped' . PHP_EOL;
				} else {
					try {
						throw new \Exception('Stopped.', 500);
					} catch (\Exception $e) {
						\Tracy\Debugger::getBlueScreen()->render($e);
					}
				}
				exit;
			}
		}
	};
}
