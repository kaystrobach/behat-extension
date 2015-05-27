# behat-extension

Contains basic functionality to test several services with behat

Use it with like this (services will be added step by step, not all will be available from the beginning:

```
class FeatureContext extends \Behat\MinkExtension\Context\MinkContext {
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        \KayStrobach\BehatExtension\Utility\LoaderUtility::loadContexts($this);
    }
}
```

# Examples

## SSL Checking for a given url

```
Feature: Schullogin is basicly available
  #@mink:selenium2

  Scenario: SSL Opal
    Then the domain Qualys SSL check should be atleast "A" for uri "https://www.google.de"
```

But you can also use the current url, instead of providing a fully blown url via the scenario
```
Feature: Schullogin is basicly available
  #@mink:selenium2

  Scenario: SSL Opal
    Given I am on "/"
    Then the Qualys SSL check should be atleast "A"
```

The SSL Check can handle quite some states.

| SSL Labs State  | Integer Value  |
|-----------------|----------------|
| A+              |   100          |
| A               |    90          |
| B               |    80          |
| C               |    60          |
| D               |    40          |
| E               |    30          |
| T               |     0          |

This way we can use the atleast statements.

## IMAP checking

```
Feature: Imap Server
  #@mink:selenium2

  Scenario: Check Imap Server
    Given I connect to imap server "{mail.example.com:143/imap/novalidate-cert}" as "user" with password "password"
    Then I can list mailboxes
    Then I can read messages from inbox
```

## Telnet checking

```
Feature: Telnet Server
  #@mink:selenium2

  Scenario: Check Telnet Server

    I connect to telnet server "telnet.example.com" on port "25" with timeout "30"
```

## Planned features

currently it's planned to add checks for

* mail server security
* ssh
* rdp
* vnc
* git
* ...