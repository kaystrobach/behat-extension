<?php

namespace KayStrobach\BehatExtension\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\StepEvent;

class AbstractFeatureContext extends BehatContext{
	/**
	 * @var string
	 */
	protected $output = '';

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