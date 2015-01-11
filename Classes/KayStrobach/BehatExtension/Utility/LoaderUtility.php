<?php

namespace KayStrobach\BehatExtension\Utility;

class LoaderUtility {
	/**
	 * @param \Behat\Behat\Context\BehatContext $parent
	 */
	public static function loadContexts($parent) {
		$workingDir = dirname(__DIR__) . '/Context/';
		$contexts = scandir($workingDir);
		foreach($contexts as $context) {
			if(is_file($workingDir . '/' . $context . '/FeatureContext.php')) {
				$className = '\\KayStrobach\\BehatExtension\\Context\\' . $context . '\\FeatureContext';
				$parent->useContext($context, new $className);
			}
		}
	}
}