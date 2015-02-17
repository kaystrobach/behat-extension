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
	public function __construct(array $parameters) {
		$this->params = $parameters;
	}

	/**
	 * @param $key
	 * @return string
	 * @throws BehaviorException
	 */
	public function getParameter($key) {
		if(array_key_exists($key, $this->parameters)) {
			return $this->parameters[$key];
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
}