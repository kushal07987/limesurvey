<?php
/**
 * Example plugin that add a simple function
 */
class demoAddEmFunction extends PluginBase
{
    static protected $description = 'Demo: adding a new function';
    static protected $name = 'demoAddEmFunction';

    public function init()
    {
        $this->subscribe('newValidFunctions');
    }

    public function newValidFunctions()
    {
        Yii::setPathOfAlias(get_class($this),dirname(__FILE__));
        //~ Yii::import(get_class($this).".exampleFunctions");
        $newFunctions = array(
            'sayHello' => array(
                '\demoAddEmFunction\exampleFunctions::sayHello', // PHP function, no need Class if function is directly added here
                'sayHello', // Javascript function
                $this->gT("Say hello"), // Description for admin
                'string sayHello(string)', // Extra description
                'https://www.limesurvey.org', // Help url
                1, // Number of argument (time to make a good description of EM …
            ),
        );
        $this->getEvent()->append('functions', $newFunctions);
        /* For the js file : maybe add a helper on parent ? */
        $newPackage = array(
            'demoAddEmFunction'=> array(
                //~ 'devBaseUrl' => '/plugins/'.get_class($this)."/assets",
                'basePath' => get_class($this).".assets",
                // Can be an external url with baseUrl
                'js' => array(get_class($this).".js"),
            ),
        );
        $this->getEvent()->append('packages', $newPackage);
    }
}
