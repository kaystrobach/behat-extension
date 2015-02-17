<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 08.01.15
 * Time: 17:36
 */

namespace KayStrobach\BehatExtension\Context\ImapContext;


use Behat\Behat\Exception\BehaviorException;
use Behat\Behat\Exception\Exception;
use KayStrobach\BehatExtension\Context\AbstractFeatureContext;

class FeatureContext extends AbstractFeatureContext{
	/**
	 *
	 * @Given /^(?:|I )connect to telnet server "(?P<host>[^"]*)" on port "(?P<port>[^"]*)" with timeout "(?P<timeout>[^"]*)"$/
	 * @param $host
	 * @param $port
	 * @param $timeOut
	 * @throws BehaviorException
	 */
	public function iConnectToServer($host, $port, $timeOut) {
		$connection = fsockopen($host, $port, $errno, $errstr, $timeOut);
		if($connection === FALSE) {
			throw new BehaviorException('Failed to connect to ' . $host);
		}
	}


	/**
	 * @Given /^(?:|I )connect to telnet server
	 */
	public function iConnectToServerByEnv() {
		$this->iConnectToServer(
			$this->getParameter('telnetHost'),
			$this->getParameter('telnetPort'),
			$this->getParameter('telnetTimeout')
		);
	}
}