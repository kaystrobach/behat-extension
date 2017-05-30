<?php

namespace KayStrobach\BehatExtension\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Exception\BehaviorException;

class AbstractFeatureContext extends BehatContext{
	/**
	 * some buffered output
	 * @var string
	 */
	protected $output = '';

	/**
	 * config parameters
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * @param array $parameters
	 */
	public function __construct(array $parameters = array()) {
		$this->params = $parameters;
	}

	/**
	 * @param $key
	 * @param string $default
	 * @return string
	 * @throws BehaviorException
	 */
	public function getParameter($key, $default = NULL) {
		if(array_key_exists($key, $this->parameters)) {
			return $this->parameters[$key];
		} elseif($default !== NULL) {
			return $default;
		} else {
			throw new BehaviorException('Can´t find parameter ' . $key . ', please add it to default.context.parameters or similar.');
		}
	}

	/**
	 * @AfterStep
	 */
	public function after(StepEvent $event) {
		if(trim($this->output) !== '') {
			echo $this->output . "\n";
			$this->output = '';
		}
	}

	public function prettyPrintDebug($string) {
		echo "\033[33m        |> " . strtr($string, array("\n" => "\n     | ")) . "\033[0m\n";
	}
}
