<?php

namespace KayStrobach\BehatExtension\Context\SslQualysContext;
use Behat\Mink\Exception\ExpectationException;
use KayStrobach\BehatExtension\Context\AbstractFeatureContext;
use KayStrobach\BehatExtension\Context\SslQualysContext\Exceptions\NoSslException;


class FeatureContext extends AbstractFeatureContext{
	protected $sslStates = [
		'A+' => 100,
		'A' => 90,
		'B' => 80,
		'C' => 60,
		'D' => 40,
		'E' => 30,
		'T' => 0,
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
	 * @param string $uri
	 * @return string
	 * @throws \Exception
	 */
	protected function getCurrentSslVoting($uri) {
		$pathSegments = parse_url($uri);
		if(!array_key_exists('host', $pathSegments)) {
			throw new \Exception('The Uri' . $uri . ' can´ be parsed cleanly');
		}
		$buffer = file_get_contents('https://www.ssllabs.com/ssltest/analyze.html?d=' . urlencode($pathSegments['host']) . '&s=' . gethostbyname($pathSegments['host']));
		libxml_use_internal_errors(true);
		$domDocument = new \DOMDocument();
		$domDocument->loadHTML($buffer);
		$ratingElement = $domDocument->getElementById('rating');
		if($ratingElement === NULL) {
			$warningBox = $domDocument->getElementById('warningBox');
			if($warningBox !== null) {
				switch (trim($warningBox->textContent)) {
					case 'Assessment failed: Unable to connect to server':
						throw new NoSslException(trim($warningBox->textContent), $this->getSession());
					break;
				}
			}
			throw new \Exception('No voting available yet');
		}
		libxml_use_internal_errors(false);
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
			$stateValue = 0;
		}
		return $stateValue;
	}

	/**
	 * Checks, that checkbox with specified in|name|label|value is checked.
	 *
	 * @Then /^the Qualys SSL check should be atleast "([^"]*)"$/
	 */
	public function theSslCheckShouldBeAtleast($state) {
		$this->theSslForUriCheckShouldBeAtleast(
			$state,
			$this->getCurrentLocation()
		);
	}

	/**
	 * @param $state
	 * @param $uri
	 * @throws ExpectationException
	 * @throws \Exception
	 *
	 * @Then /^the domain Qualys SSL check should be atleast "([^"]*)" for uri "([^"]*)"$/
	 */
	public function theSslForUriCheckShouldBeAtleast($state, $uri) {
		$stateAsInteger = $this->mapStateToInteger($state);
		try {
			$voting = $this->getCurrentSslVoting($uri);
		} catch(NoSslException $e) {
			throw $e;
		} catch (\Exception $e) {
			$this->printDebug('Connection to ssllabs.com for initializing the check, may take several minutes ...');
			sleep(300);

			$voting = $this->getCurrentSslVoting($uri);
		}
		$votingAsInteger = $this->mapStateToInteger($voting);
		if(($stateAsInteger > $votingAsInteger)) {
			throw new ExpectationException('SSL State ' . $voting. ' is lower than allowed', $this->getSession());
		}
	}
}