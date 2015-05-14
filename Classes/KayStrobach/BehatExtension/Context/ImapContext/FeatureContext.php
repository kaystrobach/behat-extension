<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 08.01.15
 * Time: 17:36
 */

namespace KayStrobach\BehatExtension\Context\ImapContext;


use Behat\Behat\Exception\BehaviorException;
use KayStrobach\BehatExtension\Context\AbstractFeatureContext;

class FeatureContext extends AbstractFeatureContext{

	/**
	 * imap connection handle
	 *
	 * @var
	 */
	protected $imapConnection;

	/**
	 * Connection DSN used to connect to INBOX
	 * @var string
	 */
	protected $imapDsn = '';

	/**
	 * @param $mailbox
	 * @param $username
	 * @param $password
	 * @param int $options
	 * @param int $n_retries
	 * @param array $params
	 * @throws BehaviorException
	 *
	 * @Given /^(?:|I )connect to imap server "(?P<mailbox>[^"]*)" as "(?P<username>[^"]*)" with password "(?P<password>[^"]*)"$/
	 */
	public function iConnectToServer($mailbox, $username, $password, $options = 0, $n_retries = 0, $params = array()) {
		$this->imapDsn = $mailbox;
		$this->imapConnection = \imap_open ($mailbox , $username , $password, $options, $n_retries, $params);
		if($this->imapConnection === FALSE) {
			throw new BehaviorException('Failed to connect to ' . $mailbox . ' as ' . $username);
		}
	}

	/**
	 * @Given /^(?:|I )connect to imap server$/
	 */
	public function iConnectToServerByEnv() {
		$this->iConnectToServer(
			$this->getParameter('imapMailbox'),
			$this->getParameter('imapUsername'),
			$this->getParameter('imapPassword'),
			$this->getParameter('imapOptions')
		);
	}

	/**
	 * @Then /^(?:|I )can list mailboxes$/
	 *
	 * @return string
	 */
	public function iCanListMailboxes() {
		$this->printDebug(print_r(\imap_list($this->imapConnection, $this->imapDsn, '*'), TRUE));
	}

	/**
	 * @Then /^(?:|I )can read messages from inbox$/
	 */
	public function iCanReadmessagesFromInbox() {
		$this->iCanReadMessagesFromFolder('INBOX');
	}

	/**
	 * @param $folder
	 * @return string
	 *
	 * @Then /^(?:|I )can read messages from "(?P<folder>[^"]*)"$/
	 */
	public function iCanReadMessagesFromFolder($folder) {
		\imap_reopen($this->imapConnection, $this->imapDsn . $folder);
		$this->printDebug(print_r(\imap_headers($this->imapConnection), TRUE));
	}

}