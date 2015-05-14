<?php

namespace KayStrobach\BehatExtension\Context\SslContext;
use Behat\Mink\Exception\ExpectationException;
use KayStrobach\BehatExtension\Context\AbstractFeatureContext;


class FeatureContext extends AbstractFeatureContext{
	protected $sslStates = [
		'A+' => 100,
		'A' => 90,
		'B' => 80,
		'C' => 60,
		'D' => 40,
		'E' => 30
	];

	/**
	 * @return \Behat\Mink\Session
	 */
	protected function getSession() {
		/** @var \Behat\MinkExtension\Context\MinkContext $mainContext */
		$mainContext = $this->getMainContext();
		/** @var \Behat\Mink\Session $session*/
		return $mainContext->getSession();

	}

	/**
	 * @return string
	 */
	protected function getCurrentLocation() {
		return $this->getSession()->getCurrentUrl();
	}

	/**
	 * @param string $url
	 * @return string
	 * @throws \Exception
	 */
	protected function getCurrentSslVoting($url) {
		$buffer = file_get_contents('https://www.ssllabs.com/ssltest/analyze.html?d=' . urlencode($this->getCurrentLocation()));
		$domDocument = new \DOMDocument();
		$domDocument->loadHTML($buffer);
		$ratingElement = $domDocument->getElementById('rating');
		if($ratingElement === NULL) {
			throw new \Exception('No voting available yet');
		}
		return trim($ratingElement->childNodes->item(3)->textContent);
	}

	/**
	 * @param string $state
	 * @return int
	 */
	protected function mapStateToInteger($state) {
		if (array_key_exists($state, $this->sslStates)) {
			$stateValue = $this->sslStates[$state];
		} else {
			$this->printDebug('the given state is unknown ... use A+, A, B, C, D, E please');
			$stateValue = 90;
		}
		return $stateValue;
	}

	/**
	 * Checks, that checkbox with specified in|name|label|value is checked.
	 *
	 * @Then /^the SSL check should be atleast "([^"]*)"$/
	 */
	public function theSslCheckShouldBeAtleast($state) {
		$stateAsInteger = $this->mapStateToInteger($state);
		try {
			$voting = $this->getCurrentSslVoting('https://www.ssllabs.com/ssltest/analyze.html?d=' . urlencode($this->getCurrentLocation()) . '&clearCache=on');
		} catch(\Exception $e) {
			$this->printDebug('Connection to ssllabs.com for initializing the check, may take several minutes ...');
			sleep(300);
			$voting = $this->getCurrentSslVoting('https://www.ssllabs.com/ssltest/analyze.html?d=' . urlencode($this->getCurrentLocation()) . '&clearCache=on');
		}
		$votingAsInteger = $this->mapStateToInteger($voting);
		if(($stateAsInteger > $votingAsInteger)) {
			throw new ExpectationException('SSL State ' . $voting. ' is lower than allowed', $this->getSession());
		}
	}
}