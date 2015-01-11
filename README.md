# behat-extension

Contains basic functionality to test several services with behat

Use it with like this (services will be added step by step, not all will be available from the beginning:

```
class FeatureContext extends \Behat\MinkExtension\Context\MinkContext
{
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
