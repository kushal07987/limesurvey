<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class index extends CAction {

    public $oTemplate;

    public function run()
    {
        useFirebug();
        $this->action();
    }

    function action()
    {
        global $surveyid;
        global $thissurvey, $thisstep;
        global $clienttoken, $tokensexist, $token;

        // only attempt to change session lifetime if using a DB backend
        // with file based sessions, it's up to the admin to configure maxlifetime
        if(isset(Yii::app()->session->connectionID)){
            @ini_set('session.gc_maxlifetime', Yii::app()->getConfig('iSessionExpirationTime'));
        }

        $this->_loadRequiredHelpersAndLibraries();

        $param       = $this->_getParameters(func_get_args(), $_POST);
        $surveyid    = $param['sid'];
        $thisstep    = $param['thisstep'];
        $move        = getMove();
        $clienttoken = trim($param['token']);

        Yii::app()->setConfig('surveyID',$surveyid);
        Yii::app()->setConfig('move',$move);
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."survey_runtime.js");

        if (is_null($thissurvey) && !is_null($surveyid))
            $thissurvey = getSurveyInfo($surveyid);

        // unused vars in this method (used in methods using compacted method vars)
        $loadname = $param['loadname'];
        $loadpass = $param['loadpass'];
        $sitename = Yii::app()->getConfig('sitename');

        if (isset($param['newtest']) && $param['newtest'] == "Y"){
            killSurveySession($surveyid);
        }

        $surveyExists   = ($surveyid && Survey::model()->findByPk($surveyid));
        $isSurveyActive = ($surveyExists && Survey::model()->findByPk($surveyid)->active=="Y");

        // collect all data in this method to pass on later
        $redata = compact(array_keys(get_defined_vars()));

        $this->_loadLimesurveyLang($surveyid);


        // Set the language of the survey, either from POST, GET parameter of session var
        // Keep the old value, because SetSurveyLanguage update $_SESSION
        $sOldLang = isset($_SESSION['survey_'.$surveyid]['s_lang'])?$_SESSION['survey_'.$surveyid]['s_lang']:"";// Keep the old value, because SetSurveyLanguage update $_SESSION

        if (!empty($param['lang'])){
            $sDisplayLanguage = $param['lang'];// $param take lang from returnGlobal and returnGlobal sanitize langagecode
        }elseif (isset($_SESSION['survey_'.$surveyid]['s_lang'])){
            $sDisplayLanguage = $_SESSION['survey_'.$surveyid]['s_lang'];
        }elseif(Survey::model()->findByPk($surveyid)){
            $sDisplayLanguage=Survey::model()->findByPk($surveyid)->language;
        }else{
            $sDisplayLanguage=Yii::app()->getConfig('defaultlang');
        }

        if ($surveyid && $surveyExists){
            SetSurveyLanguage( $surveyid, $sDisplayLanguage);
        }

        if ($this->_isClientTokenDifferentFromSessionToken($clienttoken, $surveyid)) {
            $sReloadUrl = $this->getController()->createUrl("/survey/index/sid/{$surveyid}",array('token'=>$clienttoken,'lang'=>App()->language,'newtest'=>'Y'));
            $aErrors    = array(gT('Token mismatch'));
            $asMessage  = array(gT('The token you provided doesn\'t match the one in your session.'));
            $aUrl       = array(
                            'url'=>$sReloadUrl,
                            'type'=>'restart-survey',
                            'description'=>gT("Click here to start the survey.")
                          );

            killSurveySession($surveyid);

            App()->getController()->renderExitMessage(
                $surveyid,
                'restart-survey',
                $aMessage,
                $aUrl,
                $aErrors
            );

            $this->_createNewUserSessionAndRedirect($surveyid, $redata, __LINE__, $asMessage);

        } elseif (!$clienttoken) {
            $clienttoken= isset($_SESSION['survey_'.$surveyid]['token']) ? $_SESSION['survey_'.$surveyid]['token'] : ""; // Fix for #12003
        }

        // No test for response update
        if ( $this->_isSurveyFinished($surveyid) && ($thissurvey['alloweditaftercompletion'] != 'Y' || $thissurvey['tokenanswerspersistence'] != 'Y')){
            $aReloadUrlParam=array('lang'=>App()->language,'newtest'=>'Y');

            if($clienttoken){
                $aReloadUrlParam['token'] = $clienttoken;
            }

            $aErrors  = array(gT('Previous session is set to be finished.'));
            $aMessage = array( gT('Your browser reports that it was used previously to answer this survey. We are resetting the session so that you can start from the beginning.'),);
            $aUrl     = array(
                            'url'=>$this->getController()->createUrl("/survey/index/sid/{$surveyid}",$aReloadUrlParam),
                            'type'=>'restart-survey',
                            'description'=>gT("Click here to start the survey.")
                        );

            killSurveySession($surveyid);
            App()->getController()->renderExitMessage(
                $surveyid,
                'restart-survey',
                $aMessage,
                $aUrl,
                $aErrors
            );
        }

        $previewmode = false;
        if (isset($param['action']) && (in_array($param['action'],array('previewgroup','previewquestion')))){

            if(!$this->_canUserPreviewSurvey($surveyid)){

                // @todo : throw a 401
                $aErrors  = array( gT('Error'));
                $aMessage = array( gT("We are sorry but you don't have permissions to do this."),);

                App()->getController()->renderExitMessage(
                    $surveyid,
                    'norights-401',
                    $aMessage,
                    null,
                    $aErrors
                 );
            }else{
                if((intval($param['qid']) && $param['action']=='previewquestion')) $previewmode='question';
                if((intval($param['gid']) && $param['action']=='previewgroup')) $previewmode='group';
            }
        }

        Yii::app()->setConfig('previewmode',$previewmode);

        if ( $this->_surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $isSurveyActive, $surveyExists) ){
            $bPreviewRight = $this->_userHasPreviewAccessSession($surveyid);

            if ($bPreviewRight === false){
                // @todo : throw a 401
                $aErrors  = array(gT('Error'));
                $aMessage = array(gT("We are sorry but you don't have permissions to do this."),);
                $event    = new PluginEvent('onSurveyDenied');
                $event->set('surveyId', $surveyid);
                $event->set('reason', 'noPreviewPermission');

                App()->getPluginManager()->dispatchEvent($event);
                App()->getController()->renderExitMessage(
                    $surveyid,
                    'norights-410',
                    $aMessage,
                    null,
                    $aErrors
                 );
            }
        }

        // TODO can this be moved to the top?
        // (Used to be global, used in ExpressionManager, merged into amVars. If not filled in === '')
        // can this be added in the first computation of $redata?
        if (isset($_SESSION['survey_'.$surveyid]['srid'])){
            $saved_id = $_SESSION['survey_'.$surveyid]['srid'];
        }

        // recompute $redata since $saved_id used to be a global
        $redata = compact(array_keys(get_defined_vars()));

        if ( $this->_didSessionTimeout($surveyid) ){
            $aErrors=array(gT('We are sorry but your session has expired.'));
            $aMessage = array(
                gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection."),
            );

            $aReloadUrlParam=array('lang'=>App()->language,'newtest'=>'Y');
            if($clienttoken){
                $aReloadUrlParam['token']=$clienttoken;
            }
            $aUrl=array(
                'url'=>$this->getController()->createUrl("/survey/index/sid/{$surveyid}",$aReloadUrlParam),
                'type'=>'restart-survey',
                'description'=>gT("Click here to start the survey.")
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'sessionExpired');
            App()->getPluginManager()->dispatchEvent($event);

            App()->getController()->renderExitMessage(
                $surveyid,
                'session-timeout',
                    $aMessage,
                    $aUrl,
                    $aErrors
            );
        };

        //CHECK FOR REQUIRED INFORMATION (sid)
        if ($surveyid && $surveyExists){

            LimeExpressionManager::SetSurveyId($surveyid); // must be called early - it clears internal cache if a new survey is being used

            if($previewmode) LimeExpressionManager::SetPreviewMode($previewmode);

            // Update the Session var only if needed
            if (App()->language != $sOldLang){
                UpdateGroupList($surveyid, App()->language);   // to refresh the language strings in the group list session variable
                UpdateFieldArray();                             // to refresh question titles and question text
            }
        }else{

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'surveyDoesNotExist');
            App()->getPluginManager()->dispatchEvent($event);

            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }

        // Get token
        if (!isset($token)){
            $token = $clienttoken;
        }

        //GET BASIC INFORMATION ABOUT THIS SURVEY
        $thissurvey = getSurveyInfo($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);

        $event = new PluginEvent('beforeSurveyPage');
        $event->set('surveyId', $surveyid);
        App()->getPluginManager()->dispatchEvent($event);

        if (!is_null($event->get('template'))){
            $thissurvey['templatedir'] = $event->get('template');
        }

        //SEE IF SURVEY USES TOKENS
        if ($surveyExists == 1 && tableExists('{{tokens_'.$thissurvey['sid'].'}}')){
            $tokensexist = 1;
        }else{
            $tokensexist = 0;
            unset($_POST['token']);
            unset($param['token']);
            unset($token);
            unset($clienttoken);
        }

        //SET THE TEMPLATE DIRECTORY
        $oTemplate  = Template::model()->getInstance('', $surveyid);
        $timeadjust = Yii::app()->getConfig("timeadjust");

        //MAKE SURE SURVEY HASN'T EXPIRED
        if ($thissurvey['expiry']!='' and dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)>$thissurvey['expiry'] && $thissurvey['active']!='N' && !$previewmode){
            $aErrors=array(gT('Error'));
            $aMessage = array(
                gT("We are sorry but the survey is expired and no longer available."),
                sprintf(gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail']) /* Maybe better to move this to a global replacement 'surveycontact' */
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'surveyNoLongerAvailable');

            App()->getPluginManager()->dispatchEvent($event);
            App()->getController()->renderExitMessage(
                $surveyid,
                'survey-expiry',
                $aMessage,
                null,
                $aErrors
             );
        }

        //MAKE SURE SURVEY IS ALREADY VALID
        if ($thissurvey['startdate']!='' and  dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)<$thissurvey['startdate'] && $thissurvey['active']!='N' && !$previewmode){
            $aErrors  = array(gT('Error'));
            $aMessage = array(
                gT("This survey is not yet started."),
                sprintf(gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])/* Maybe better to move this to a global replacement 'surveycontact' */
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'surveyNotYetStarted');
            App()->getPluginManager()->dispatchEvent($event);
            App()->getController()->renderExitMessage(
                $surveyid,
                'survey-notstart',
                $aMessage,
                null,
                $aErrors
             );
        }

        //CHECK FOR PREVIOUSLY COMPLETED COOKIE
        //If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
        $sCookieName="LS_".$surveyid."_STATUS";
        if (isset($_COOKIE[$sCookieName]) && $_COOKIE[$sCookieName] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1 && (!isset($param['newtest']) || $param['newtest'] != "Y")){

            $aErrors  = array(gT('Error'));
            $aMessage = array(
                gT("You have already completed this survey."),
                sprintf(gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])/* Maybe better to move this to a global replacement 'surveycontact' */
            );

            $event = new PluginEvent('onSurveyDenied');
            $event->set('surveyId', $surveyid);
            $event->set('reason', 'alreadyCompleted');
            App()->getPluginManager()->dispatchEvent($event);

            App()->getController()->renderExitMessage(
                $surveyid,
                'survey-notstart',
                $aMessage,
                null,
                $aErrors
             );
        }

        //LOAD SAVED SURVEY
        if (Yii::app()->request->getParam('loadall') == "reload"){

            $aLoadErrorMsg = array();
            $sLoadName     = Yii::app()->request->getParam('loadname');
            $sLoadPass     = Yii::app()->request->getParam('loadpass');

            if ( isset($sLoadName) && !$sLoadName){
                $aLoadErrorMsg['name']=gT("You did not provide a name.");
            }

            if ( isset($sLoadPass) && !$sLoadPass){
                $aLoadErrorMsg['password']=gT("You did not provide a password.");
            }

            // if security question answer is incorrect
            // Not called if scid is set in GET params (when using email save/reload reminder URL)
            // && Yii::app()->request->isPostRequest ?
            if (function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen',$thissurvey['usecaptcha']) && is_null(Yii::app()->request->getQuery('scid'))){
                $sLoadSecurity  = Yii::app()->request->getPost('loadsecurity');
                $captcha        = Yii::app()->getController()->createAction('captcha');
                $captchaCorrect = $captcha->validate( $sLoadSecurity, false);

                if(empty($sLoadSecurity)){
                    $aLoadErrorMsg['captchaempty']=gT("You did not answer to the security question.");
                }elseif ( !$captchaCorrect ){
                    $aLoadErrorMsg['captcha']=gT("The answer to the security question is incorrect.");
                }
            }

            if ( empty($aLoadErrorMsg) ) {
                LimeExpressionManager::SetDirtyFlag();
                buildsurveysession($surveyid);

                if (loadanswers()){
                    Yii::app()->setConfig('move','reload');
                    $move = "reload";// veyRunTimeHelper use $move in $arg
                } else {
                    $aLoadErrorMsg['matching']=gT("There is no matching saved survey.");
                }

                randomizationGroupsAndQuestions($surveyid);
                initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);
            }
            if (count($aLoadErrorMsg)) {
                Yii::app()->setConfig('move',"loadall");// Show loading form
            }
        }

        //Allow loading of saved survey
        if (Yii::app()->getConfig('move')=="loadall"){
            /* Construction of the form */
            $aLoadForm['aCaptcha']['show'] = false;

            if(function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', Survey::model()->findByPk($surveyid)->usecaptcha)){
                $aLoadForm['aCaptcha']['show'] = true;
                $aLoadForm['aCaptcha']['sImageUrl'] = Yii::app()->getController()->createUrl('/verification/image',array('sid'=>$surveyid));
            }

            if (isset($clienttoken)){
                $aLoadForm['sHiddenField'] = CHtml::hiddenField('token',$clienttoken);
            }

            $aLoadForm['aErrors']    = empty($aLoadErrorMsg) ? null : $aLoadErrorMsg; // Set tit to null if empty
            $thissurvey['aLoadForm'] = $aLoadForm;
            //$oTemplate->registerAssets();
            Yii::app()->twigRenderer->renderTemplateFromFile("layout_load.twig", array('aSurveyInfo'=>$thissurvey), false);
        }


        //Check if TOKEN is used for EVERY PAGE
        //This function fixes a bug where users able to submit two surveys/votes
        //by checking that the token has not been used at each page displayed.
        // bypass only this check at first page (Step=0) because
        // this check is done in buildsurveysession and error message
        // could be more interresting there (takes into accound captcha if used)
        if ($tokensexist == 1 && isset($token) && $token!="" &&
            isset($_SESSION['survey_'.$surveyid]['step']) && $_SESSION['survey_'.$surveyid]['step']>0 && tableExists("tokens_{$surveyid}}}")){

            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $tokenInstance = Token::model($surveyid)->findByAttributes(array('token' => $token));
            } else {
                $tokenInstance = Token::model($surveyid)->usable()->incomplete()->findByAttributes(array('token' => $token));
            }
        }

        //check if token is in a valid time frame
        if ($tokensexist == 1 && isset($token) && $token!="" && tableExists("{{tokens_".$surveyid."}}") && !$previewmode){

            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $tokenInstance = Token::model($surveyid)->editable()->findByAttributes(array('token' => $token));
            }else{
                $tokenInstance = Token::model($surveyid)->usable()->incomplete()->findByAttributes(array('token' => $token));
            }

            if (empty($tokenInstance)){

                $oToken = Token::model($surveyid)->findByAttributes(array('token' => $token));

                if($oToken){

                    $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));

                    // This can not happen (TokenInstance must fix this)
                    if($oToken->completed != 'N' && !empty($oToken->completed)){
                        $sError = gT("This invitation has already been used.");
                    }elseif(strtotime($now) < strtotime($oToken->validfrom)){
                        $sError = gT("This invitation is not valid yet.");
                    }elseif(strtotime($now) > strtotime($oToken->validuntil)){
                        $sError = gT("This invitation is not valid anymore.");
                    }else {
                        // This can not happen
                        $sError = gT("This is a controlled survey. You need a valid token to participate.");
                    }

                    $aMessage = array(
                        gT("We are sorry but you are not allowed to enter this survey."),
                        sprintf(gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])/* Maybe better to move this to a global replacement 'surveycontact' */
                    );

                    $event = new PluginEvent('onSurveyDenied');
                    $event->set('surveyId', $surveyid);
                    $event->set('reason', 'invalidToken');
                    App()->getPluginManager()->dispatchEvent($event);

                    App()->getController()->renderExitMessage(
                        $surveyid,
                        'survey-notstart',
                        $aMessage,
                        null,
                        array($sError)
                     );
                }else{
                    $sError = gT("This is a controlled survey. You need a valid token to participate.");
                }
            }
        }

        //Check to see if a refering URL has been captured.
        if (!isset($_SESSION['survey_'.$surveyid]['refurl'])){
            $_SESSION['survey_'.$surveyid]['refurl']=GetReferringUrl(); // do not overwrite refurl
        }

        // Let's do this only if
        //  - a saved answer record hasn't been loaded through the saved feature
        //  - the survey is not anonymous
        //  - the survey is active
        //  - a token information has been provided
        //  - the survey is setup to allow token-response-persistence

        if (!isset($_SESSION['survey_'.$surveyid]['srid']) && $thissurvey['anonymized'] == "N" && $thissurvey['active'] == "Y" && isset($token) && $token !=''){

            // load previous answers if any (dataentry with nosubmit)
             $oResponses  = Response::model($surveyid)->findAllByAttributes(array(
                'token' => $token
            ), array('order' => 'id DESC'));

            if (!empty($oResponses)){

                /**
                 * We fire the response selection event when at least 1 response was found.
                 * If there is just 1 response the plugin still has to option to choose
                 * NOT to use it.
                 */
                $event = new PluginEvent('beforeLoadResponse');
                $event->set('responses', $oResponses);
                $event->set('surveyId', $surveyid);
                App()->pluginManager->dispatchEvent($event);

                $oResponse = $event->get('response');

                // If $oResponse is false we act as if no response was found.
                // This allows a plugin to deny continuing a response.
                if ($oResponse !== false){
                    // If plugin does not set a response we use the first one found, (this replicates pre-plugin behavior)
                    if (!isset($oResponse) && (!isset($oResponses[0]->submitdate) || $thissurvey['alloweditaftercompletion'] == 'Y') && $thissurvey['tokenanswerspersistence'] == 'Y'){
                        $oResponse = $oResponses[0];
                    }

                    if (isset($oResponse)){
                        $_SESSION['survey_'.$surveyid]['srid'] = $oResponse->id;

                        if (!empty($oResponse->lastpage)){

                            $_SESSION['survey_'.$surveyid]['LEMtokenResume'] = true;

                            // If the response was completed and user is allowed to edit after completion start at the beginning and not at the last page - just makes more sense
                            if (!($oResponse->submitdate && $thissurvey['alloweditaftercompletion'] == 'Y')){
                                $_SESSION['survey_'.$surveyid]['step'] = $oResponse->lastpage;
                            }
                        }

                        buildsurveysession($surveyid);

                        // alloweditaftercompletion
                        if(!empty($oResponse->submitdate)){
                            $_SESSION['survey_'.$surveyid]['maxstep'] = $_SESSION['survey_'.$surveyid]['totalsteps'];
                        }

                        loadanswers();
                        randomizationGroupsAndQuestions($surveyid);
                        initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);
                    }
                }
            }
        }

        // Preview action : Preview right already tested before
        if ($previewmode){

            // Unset all SESSION: be sure to have the last version
            unset($_SESSION['fieldmap-' . $surveyid . App()->language]);// Needed by createFieldMap: else fieldmap can be outdated
            unset($_SESSION['survey_'.$surveyid]);

            if ($param['action'] == 'previewgroup'){
                $thissurvey['format'] = 'G';
            }elseif ($param['action'] == 'previewquestion'){
                $thissurvey['format'] = 'S';
            }

            buildsurveysession($surveyid,true);
            randomizationGroupsAndQuestions($surveyid, true);
            initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);
        }

        sendCacheHeaders();

        //Send local variables to the appropriate survey type
        unset($redata);
        $redata = compact(array_keys(get_defined_vars()));
        Yii::import('application.helpers.SurveyRuntimeHelper');
        $tmp = new SurveyRuntimeHelper();
        $tmp->run($surveyid,$redata);

        if (App()->request->getPost('saveall') || isset($flashmessage)){
            App()->clientScript->registerScript("saveflashmessage","alert('".gT("Your responses were successfully saved.","js")."');",CClientScript::POS_READY);
        }
    }

    function _getParameters($args = array(), $post = array())
    {
        $param = array();
        if(@$args[0]==__CLASS__) array_shift($args);
        if(count($args)%2 == 0) {
            for ($i = 0; $i < count($args); $i+=2) {
                //Sanitize input from URL with returnGlobal
                $param[$args[$i]] = returnGlobal($args[$i], true);
            }
        }

        // Need some $param (else PHP notice)
        foreach(array('lang','action','newtest','qid','gid','sid','loadname','loadpass','scid','thisstep','move','token') as $sNeededParam){
            $param[$sNeededParam]=returnGlobal($sNeededParam,true);
        }

        return $param;
    }

    function _loadRequiredHelpersAndLibraries()
    {
        //Load helpers, libraries and config vars
        Yii::app()->loadHelper("database");
        Yii::app()->loadHelper("frontend");
        Yii::app()->loadHelper("surveytranslator");
    }

    function _loadLimesurveyLang($mvSurveyIdOrBaseLang)
    {
        if ( is_numeric($mvSurveyIdOrBaseLang) && Survey::model()->findByPk($mvSurveyIdOrBaseLang)){
            $baselang = Survey::model()->findByPk($mvSurveyIdOrBaseLang)->language;
        }elseif (!empty($mvSurveyIdOrBaseLang)){
            $baselang = $mvSurveyIdOrBaseLang;
        }else{
            $baselang = Yii::app()->getConfig('defaultlang');
        }

        App()->setLanguage($baselang);
    }

    function _isClientTokenDifferentFromSessionToken($clientToken, $surveyid)
    {
        return $clientToken != '' && isset($_SESSION['survey_'.$surveyid]['token']) && $clientToken != $_SESSION['survey_'.$surveyid]['token'];
    }

    function _isSurveyFinished($surveyid)
    {
        return isset($_SESSION['survey_'.$surveyid]['finished']) && $_SESSION['survey_'.$surveyid]['finished'] === true;
    }

    function _surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $bIsSurveyActive, $bSurveyExists)
    {
        $bSurveyPreviewRequireAuth = Yii::app()->getConfig('surveyPreview_require_Auth');
        return $surveyid && $bIsSurveyActive === false && $bSurveyExists && isset($bSurveyPreviewRequireAuth) && $bSurveyPreviewRequireAuth == true &&  !$this->_canUserPreviewSurvey($surveyid);
    }

    function _didSessionTimeout($surveyid)
    {
        return (!isset($_SESSION['survey_'.$surveyid]['step']) && null !== App()->request->getPost('thisstep') );
    }

    function _canUserPreviewSurvey($iSurveyID)
    {
        return Permission::model()->hasSurveyPermission($iSurveyID,'surveycontent','read');
    }

    function _userHasPreviewAccessSession($iSurveyID){
        return (isset($_SESSION['USER_RIGHT_PREVIEW']) && ($_SESSION['USER_RIGHT_PREVIEW'] == $iSurveyID));
    }


}

/* End of file survey.php */
/* Location: ./application/controllers/survey.php */
