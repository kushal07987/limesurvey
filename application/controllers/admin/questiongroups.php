<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
 *
 */

/**
 * questiongroup
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @access public
 */
class questiongroups extends Survey_Common_Action
{

    /**
     * questiongroup::import()
     * Function responsible to import a question group.
     *
     * @access public
     * @return void
     */
    function import()
    {
        $action = $_POST['action'];
        $iSurveyID = $surveyid = $aData['surveyid'] = (int) $_POST['sid'];
        $survey = Survey::model()->findByPk($iSurveyID);

        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid));
        }

        if ($action == 'importgroup') {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = $aPathInfo['extension'];

            if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                $fatalerror = sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024).'<br>';
            } elseif (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                $fatalerror = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
            }

            // validate that we have a SID
            if (!returnGlobal('sid')) {
                            $fatalerror .= gT("No SID (Survey) has been provided. Cannot import question.");
            }

            if (isset($fatalerror)) {
                @unlink($sFullFilepath);
                Yii::app()->user->setFlash('error', $fatalerror);
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/'.$surveyid));
            }

            Yii::app()->loadHelper('admin/import');

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            if (strtolower($sExtension) == 'lsg') {
                $aImportResults = XMLImportGroup($sFullFilepath, $iSurveyID);
            } else {
                Yii::app()->user->setFlash('error', gT("Unknown file extension"));
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/'.$surveyid));
            }
            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($iSurveyID);

            if (isset($aImportResults['fatalerror'])) {
                unlink($sFullFilepath);
                Yii::app()->user->setFlash('error', $aImportResults['fatalerror']);
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/'.$surveyid));
            }

            unlink($sFullFilepath);

            $aData['display'] = $importgroup;
            $aData['surveyid'] = $iSurveyID;
            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $sExtension;
            //$aData['display']['menu_bars']['surveysummary'] = 'importgroup';
            $aData['sidemenu']['state'] = false;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'import_view', $aData);
        }
    }

    /**
     * Import a question group
     *
     */
    function importView($surveyid)
    {
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($iSurveyID);

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {

            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['questiongroups'] = true;

            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid; // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['savebutton']['text'] = gt('Import');
            $aData['surveyid'] = $surveyid;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'importGroup_view', $aData);
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid));
        }
    }

    /**
     * questiongroup::add()
     * Load add new question group screen.
     * @return
     */
    function add($surveyid)
    {
        /////
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($iSurveyID);
        $aViewUrls = $aData = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            Yii::app()->session['FileManagerContext'] = "create:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'questiongroup.js');

            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['surveyid'] = $surveyid;
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['grplangs'] = $survey->allLanguages;
            $aData['baselang'] = $survey->language; ;

            $aData['sidemenu']['state'] = false;
            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
            $aData['surveybar']['importquestiongroup'] = true;
            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid; // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['saveandclosebutton']['form'] = true;
            $this->_renderWrappedTemplate('survey/QuestionGroups', 'addGroup_view', $aData);
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Insert the new group to the database
     *
     * @access public
     * @param int $surveyid
     * @return void
     */
    public function insert($surveyid)
    {
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            Yii::app()->loadHelper('surveytranslator');

            $oGroup = new QuestionGroup;
            $oGroup->sid = $surveyid;
            $oGroup->group_order = getMaxGroupOrder($surveyid); ;
            $oGroup->randomization_group = Yii::app()->request->getPost('randomization_group');
            $oGroup->grelevance = Yii::app()->request->getPost('grelevance');
            if ($oGroup->save()) {
                $newGroupID = $oGroup->gid;
            } else {
                Yii::app()->setFlashMessage(CHtml::errorSummary($oGroup), 'error');
                $this->getController()->redirect(array("admin/questiongroups/sa/add/surveyid/$surveyid"));
            }
            $sSurveyLanguages = Survey::model()->findByPk($surveyid)->getAllLanguages();
            foreach ($sSurveyLanguages as $sLanguage) {
                $oGroupLS = new QuestionGroupL10n;
                $oGroupLS->gid = $newGroupID;
                $oGroupLS->group_name = Yii::app()->request->getPost('group_name_'.$sLanguage, "");
                $oGroupLS->description = Yii::app()->request->getPost('description_'.$sLanguage, "");
                $oGroupLS->language = $sLanguage;
                $oGroupLS->save();
            
            }
            Yii::app()->setFlashMessage(gT("New question group was saved."));
            Yii::app()->setFlashMessage(sprintf(gT('You can now %sadd a question%s in this group.'), '<a href="'.Yii::app()->createUrl("admin/questions/sa/newquestion/surveyid/$surveyid/gid/$newGroupID").'">', '</a>'), 'info');
            if (Yii::app()->request->getPost('close-after-save') === 'true') {
                $this->getController()->redirect(array("admin/questiongroups/sa/view/surveyid/$surveyid/gid/$newGroupID"));
            } else if (Yii::app()->request->getPost('saveandnew', '') !== '') {
                $this->getController()->redirect(array("admin/questiongroups/sa/add/surveyid/$surveyid"));
            } else if (Yii::app()->request->getPost('saveandnewquestion', '') !== '') {
                $this->getController()->redirect(array("admin/questions/sa/newquestion/", 'surveyid' => $surveyid, 'gid' => $newGroupID));
            } else {
                // After save, go to edit
                $this->getController()->redirect(array("admin/questiongroups/sa/edit/surveyid/$surveyid/gid/$newGroupID"));
            }

        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Action to delete a question group.
     *
     * @access public
     * @return void
     */
    public function delete($iSurveyId, $iGroupId)
    {
        $iSurveyId = sanitize_int($iSurveyId);

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'delete')) {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyId);

            $iGroupId = sanitize_int($iGroupId);
            $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupId, $iSurveyId);

            if ($iGroupsDeleted > 0) {
                fixSortOrderGroups($iSurveyId);
                Yii::app()->setFlashMessage(gT('The question group was deleted.'));
            } else {
                            Yii::app()->setFlashMessage(gT('Group could not be deleted'), 'error');
            }
            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyId);
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$iSurveyId));
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    public function view($surveyid, $gid)
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = $surveyid;
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['gid'] = $gid;
        $baselang = $survey->language;
        $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        $aData['condarray'] = $condarray;

        $oQuestionGroup = QuestionGroup::model()->findByPk($gid);
        $grow           = $oQuestionGroup->attributes;

        $grow = array_map('flattenText', $grow);
                                                 
        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['grow'] = $grow;

        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $oQuestionGroup->questionGroupL10ns[$baselang]->group_name;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['questiongroupbar']['buttons']['view'] = true;

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = false;

        $this->_renderWrappedTemplate('survey/QuestionGroups', 'group_view', $aData);
    }

    /**
     * questiongroup::edit()
     * Load editing of a question group screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @return void
     */
    public function edit($surveyid, $gid)
    {
        $surveyid = $iSurveyID = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        $gid = sanitize_int($gid);
        $aViewUrls = $aData = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->session['FileManagerContext'] = "edit:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            // TODO: This is not an array, but a string "en"
            $aBaseLanguage = $survey->language;

            $aLanguages = $survey->allLanguages;

            $grplangs = array_flip($aLanguages);

            // Check out the intgrity of the language versions of this group
            $egresult = QuestionGroupL10n::model()->findAllByAttributes(array('gid' => $gid));
            foreach ($egresult as $esrow) {
                $esrow = $esrow->attributes;

                // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE
                if (!in_array($esrow['language'], $aLanguages)) {
                    QuestionGroupL10n::model()->deleteAllByAttributes(array('gid' => $gid, 'language' => $esrow['language']));
                } else {
                    $grplangs[$esrow['language']] = 'exists';
                }

                if ($esrow['language'] == $aBaseLanguage) {
                    $basesettings = $esrow;
                }
            }

            // Create groups in missing languages
            while (list($key, $value) = each($grplangs)) {
                if ($value != 'exists') {
                    $basesettings['language'] = $key;
                    $groupLS = new QuestionGroupL10n;
                    foreach ($basesettings as $k => $v) {
                                            $group->$k = $v;
                    }
                    $groupLS->save();
                }
            }
            $first = true;
            $oQuestionGroup = QuestionGroup::model()->findByAttributes(array('gid' => $gid));
            foreach ($aLanguages as $sLanguage) {
                $oResult = QuestionGroupL10n::model()->findByAttributes(array('gid' => $gid, 'language' => $sLanguage));
                $aData['aGroupData'][$sLanguage] = array_merge($oResult->attributes, $oQuestionGroup->attributes);
                $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
                if ($first) {
                    $aTabTitles[$sLanguage] .= ' ('.gT("Base language").')';
                    $first = false;
                }
            }                  
            $aData['oQuestionGroup'] = $oQuestionGroup;
            $aData['sidemenu']['questiongroups'] = true;
            $aData['questiongroupbar']['buttonspreview'] = true;
            $aData['questiongroupbar']['savebutton']['form'] = true;
            $aData['questiongroupbar']['saveandclosebutton']['form'] = true;
            $aData['questiongroupbar']['closebutton']['url'] = 'admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$gid; // Close button

            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'editgroup';
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;
            $aData['tabtitles'] = $aTabTitles;
            $aData['aBaseLanguage'] = $aBaseLanguage;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title.":".$iSurveyID.")";

            ///////////
            // sidemenu
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['explorer']['state'] = true;
            $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
            $aData['sidemenu']['explorer']['qid'] = false;

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'editGroup_view', $aData);
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }

    }

    /**
     * Reorder the questiongroups based on the new order in the adminpanel
     *
     * @param type $surveyid
     * @return void
     */
    public function updateOrder($surveyid)
    {
        $grouparray = Yii::app()->request->getPost('grouparray', []);
        foreach ($grouparray as $aQuestiongroup) {
            
            //first set up the ordering for questiongroups
            $oQuestiongroups = QuestionGroup::model()->findAll("gid=:gid AND sid=:sid", [':gid'=> $aQuestiongroup['gid'], ':sid'=> $surveyid]);
            array_map(function($oQuestiongroup) use ($aQuestiongroup)
            {
                $oQuestiongroup->group_order = $aQuestiongroup['group_order'];
                $oQuestiongroup->save();
            }, $oQuestiongroups);

            
            foreach ($aQuestiongroup['questions'] as $aQuestion) {
                $oQuestions = Question::model()->findAll("qid=:qid AND sid=:sid", [':qid'=> $aQuestion['qid'], ':sid'=> $surveyid]);
                array_map(function($oQuestion) use ($aQuestion)
                {
                    $oQuestion->question_order = $aQuestion['question_order'];
                    $oQuestion->gid = $aQuestion['gid'];
                    $oQuestion->save();
                }, $oQuestions);
            }
            Question::updateSortOrder($aQuestiongroup['gid'], $surveyid);
        }
    }

    /**
     * Reorder the questiongroups based on the new order in the adminpanel
     *
     * @param type $surveyid
     * @return void
     */
    public function updateOrderWithQuestions($surveyid)
    {
        $grouparray = Yii::app()->request->getPost('grouparray', []);
        foreach ($grouparray as $aQuestiongroup) {
            $oQuestiongroups = QuestionGroup::model()->findAll("gid=:gid AND sid=:sid", [':gid'=> $aQuestiongroup['gid'], ':sid'=> $surveyid]);
            array_map(function($oQuestiongroup) use ($aQuestiongroup)
            {
                $oQuestiongroup->group_order = $aQuestiongroup['group_order'];
                $oQuestiongroup->save();
            }, $oQuestiongroups);
        }
    }

    /**
     * Provides an interface for updating a group
     *
     * @access public
     * @param int $gid
     * @return void
     */
    public function update($gid)
    {
        $gid = (int) $gid;
        $group = QuestionGroup::model()->findByAttributes(array('gid' => $gid));
        $surveyid = $group->sid;
        $survey = Survey::model()->findByPk($surveyid);

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->loadHelper('surveytranslator');

            foreach ($survey->allLanguages as $grplang) {
                if (isset($grplang) && $grplang != "") {
                    $group_name = $_POST['group_name_'.$grplang];
                    $group_description = $_POST['description_'.$grplang];

                    $group_name = html_entity_decode($group_name, ENT_QUOTES, "UTF-8");
                    $group_description = html_entity_decode($group_description, ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name = fixCKeditorText($group_name);
                    $group_description = fixCKeditorText($group_description);

                    $aData = array(
                        'randomization_group' => $_POST['randomization_group'],
                        'grelevance' => $_POST['grelevance'],
                    );
                    $group = QuestionGroup::model()->findByPk($gid);
                    foreach ($aData as $k => $v) {
                        $group->$k = $v;
                    }
                    $ugresult = $group->save();

                    $aData = array(
                        'group_name' => $group_name,
                        'description' => $group_description,
                    );
                    $condition = array(
                        'language' => $grplang,
                        'gid' => $gid,
                    );
                    $oGroupLS = QuestionGroupL10n::model()->findByAttributes($condition);
                    foreach ($aData as $k => $v) {
                                            $oGroupLS->$k = $v;
                    }
                    $ugresult2 = $oGroupLS->save();


                    if ($ugresult && $ugresult2) {
                        $groupsummary = getGroupList($gid, $surveyid);
                    }
                }
            }

            Yii::app()->setFlashMessage(gT("Question group successfully saved."));

            if (Yii::app()->request->getPost('close-after-save') === 'true') {
                            $this->getController()->redirect(array('admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$gid));
            }

            $this->getController()->redirect(array('admin/questiongroups/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid));
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/QuestionGroups', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
