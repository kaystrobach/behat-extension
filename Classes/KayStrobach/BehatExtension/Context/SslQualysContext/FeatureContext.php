<?php

namespace KayStrobach\BehatExtension\Context\SslQualysContext;

use Behat\Mink\Exception\ExpectationException;
use KayStrobach\BehatExtension\Context\AbstractFeatureContext;
use KayStrobach\BehatExtension\Context\SslQualysContext\Exceptions\DnsNotResolveableException;
use KayStrobach\BehatExtension\Context\SslQualysContext\Exceptions\NoSslException;
use KayStrobach\BehatExtension\Context\SslQualysContext\Exceptions\StillRunningException;


class FeatureContext extends AbstractFeatureContext
{
    protected $sslStates = [
        'A+' => 100,
        'A' => 95,
        'A-' => 90,
        'B+' => 85,
        'B' => 80,
        'B-' => 75,
        'C+' => 60,
        'C' => 55,
        'C-' => 50,
        'D+' => 45,
        'D' => 40,
        'D-' => 35,
        'E+' => 30,
        'E' => 25,
        'E-' => 20,
        'T' => 0,
    ];

    /**
     * @return \Behat\Mink\Session
     */
    protected function getSession()
    {
        /** @var \Behat\MinkExtension\Context\MinkContext $mainContext */
        $mainContext = $this->getMainContext();
        /** @var \Behat\Mink\Session $session */
        return $mainContext->getSession();

    }

    /**
     * @return string
     */
    protected function getCurrentLocation()
    {
        return $this->getSession()->getCurrentUrl();
    }

    /**
     * @param string $uri
     * @return string
     * @throws \Exception
     */
    protected function getCurrentSslVoting($uri, $ip = NULL)
    {
        $pathSegments = parse_url(trim($uri));
        if ($ip === NULL) {
            if (!array_key_exists('host', $pathSegments)) {
                throw new \Exception('The Uri ' . $uri . ' can´t be parsed cleanly - ' . print_r($pathSegments, true));
            }
            $ip = gethostbyname($pathSegments['host']);
        }

        $buffer = file_get_contents('https://api.ssllabs.com/api/v2/analyze?host=' . urlencode($pathSegments['host']) . '&s=' . $ip);
        $state = json_decode($buffer, true);

        if (isset($state['status']) && ($state['status'] === 'ERROR')) {
            $this->prettyPrintDebug($state['statusMessage']);
            throw new NoSslException(trim($state['statusMessage']), $this->getSession());
        }
        if (isset($state['status']) && ($state['status'] === 'READY')) {
            if (isset($state['endpoints'][0]['statusMessage'])) {
                if (($state['endpoints'][0]['statusMessage'] === 'Ready') && (isset($state['endpoints'][0]['grade']))) {
                    return $state['endpoints'][0]['grade'];
                }
                $this->prettyPrintDebug($state['endpoints'][0]['statusMessage']);
                throw new NoSslException($state['endpoints'][0]['statusMessage'], $this->getSession());
            }
        }

        $message = '';
        if (isset($state['status'])) {
            $message = ' : ' . $state['status'];
        }

        throw new StillRunningException('No voting available yet' . $message, $this->getSession());
    }

    /**
     * @param string $state
     * @return int
     */
    protected function mapStateToInteger($state)
    {
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
    public function theSslCheckShouldBeAtleast($state)
    {
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
    public function theSslForUriCheckShouldBeAtleast($state, $uri)
    {
        $this->theSslForUriCheckShouldBeAtleastForIp($state, $uri);
    }

    /**
     * @param $state
     * @param $uri
     * @param $ip
     * @throws ExpectationException
     * @throws \Exception
     *
     * @Then /^the domain Qualys SSL check should be atleast "([^"]*)" for uri "([^"]*)" and ip "([^"]*)"$/
     */
    public function theSslForUriCheckShouldBeAtleastForIp($state, $uri, $ip = NULL)
    {
        $stateAsInteger = $this->mapStateToInteger($state);
        echo chr(10);
        $this->prettyPrintDebug('ssllabs.com: Starting check for ' . $uri);

        for ($i = 0; $i < 20; $i++) {
            try {
                $voting = $this->getCurrentSslVoting($uri, $ip);
                $this->prettyPrintDebug('ssllabs.com: current voting is ' . $voting);
                $votingAsInteger = $this->mapStateToInteger($voting);
                if (($stateAsInteger > $votingAsInteger)) {
                    throw new ExpectationException('SSL State ' . $voting . ' is lower than allowed', $this->getSession());
                }
                break;
            } catch (StillRunningException $e) {
                $this->prettyPrintDebug('ssllabs.com: ' . $e->getMessage());
                sleep(30);
            } catch (DnsNotResolveableException $e){
                $this->prettyPrintDebug('ssllabs.com: ' . $e->getMessage());
                throw $e;
            } catch (NoSslException $e) {
                $this->prettyPrintDebug('ssllabs.com: ' . $e->getMessage());
                throw $e;
            }
        }
    }
}
