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
	 * @Given /^(?:|I )connect to telnet server
	 */
	public function iConnectToImapServerByEnv() {
		$host = $this->getParameter('telnetHost');
		$port = $this->getParameter('telnetPort');
		$timeOut = $this->getParameter('telnetTimeout');
		$connection = fsockopen($host, $port, $errno, $errstr, $timeOut);
		if($connection === FALSE) {
			throw new BehaviorException('Failed to connect to ' . $host);
		}
	}
}