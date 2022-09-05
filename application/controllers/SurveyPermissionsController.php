<?php

use LimeSurvey\Models\Services\PermissionManager;

class SurveyPermissionsController extends LSBaseController
{
    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['index'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    /**
     * Here we have to use the correct layout (NOT main.php)
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->layout = 'layout_questioneditor';
        LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
        LimeExpressionManager::StartProcessingPage(false, true);

        return parent::beforeRender($view);
    }

    /**
     * @param $surveyid
     * @return array|mixed|string|string[]|null
     */
    public function actionIndex($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'read')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'surveypermissions.js');
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['subaction'] = gT("Survey permissions");

        $aData['surveybar']['closebutton']['url'] = 'surveyAdministration/view/surveyid/' . $surveyid; // Close button

        $this->aData = $aData;
        $aBaseSurveyPermissions = Permission::model()->getSurveyBasePermissions();
        //function get table content as array
        //structure should be
        /*
         *
         */
        return $this->render('index', [
            'basePermissions' => $aBaseSurveyPermissions,
            'userCreatePermission' => Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create'),
            'surveyid' => $surveyid
        ]);
    }

    /**
     * Add a user to permission table for this survey.
     * Opens the permission settings site if user could be added.
     *
     * @param $surveyid
     * @return void
     */
    public function actionAddUser($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        // 2.  add the user in permission table
        // 3.  redirect to index if failed, or redirect to 'add permission page'; giving flash message in both cases
    }

    /**
     * Add group users to permission table for this survey.
     * and redirects to settings permission page
     *
     * @param $surveyid
     * @return void
     */
    public function actionAddUserGroup($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        // 2.  add the user in permission table
        // 3.  redirect to index if failed, or redirect to 'add permission page'; giving flash message in both cases
    }

    /**
     * Open settings permission page
     *
     * @param $surveyid int
     * @param $action string the action could be 'user' or 'usergroup'
     * @param $id int userid or groupid depending on the action
     *
     * @return void
     */
    public function actionSettingsPermissions($surveyid, $action, $id)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        if (!in_array($action, ['user', 'usergroup'])) {
            Yii::app()->user->setFlash('error', gT("Unknown action."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        // 2.  set permissions for user table
        // 3.  redirect to index giving flash message in both cases (success and failed)
        $oSurvey = Survey::model()->findByPk($surveyid);
        $PermissionManagerService = new PermissionManager(
            App()->request,
            App()->user,
            $oSurvey,
            App()
        );
        $isUserGroup = $action === 'usergroup';
        if ($isUserGroup) {
            $oUserGroup = UserGroup::model()->findByPk($id);
            $aPermissions = $PermissionManagerService->getPermissionData();
        } else {
            $oUser = User::model()->findByPk($id);
            $aPermissions = $PermissionManagerService->getPermissionData($id);
        }
        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;
        $aData['topBar']['showSaveButton'] = true;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';
        $this->aData = $aData;
        return $this->render(
            'settingsPermission',
            [
                'surveyid' => $surveyid,
                'aPermissions' => $aPermissions,
                'isUserGroup' => $isUserGroup,
                'id' => $id,
                'name' => 'devTEST', //todo: set the correct name
            ]
        );
    }

    /**
     * Save permissions for a user or a usergroup
     *
     * @param $surveyid
     * @return void
     */
    public function actionSavePermissions($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        //get post-params
        $action = Yii::app()->request->getPost('action'); //the action could be 'user' or 'usergroup'
        // 1. save the permissions
        // 2. redirect to overview (index)

    }
}
