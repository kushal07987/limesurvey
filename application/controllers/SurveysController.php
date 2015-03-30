<?php
namespace ls\controllers;
use Survey;
    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends Controller
    {
        public $layout = 'minimal';
        public $defaultAction = 'publicList';

        public function accessRules() {
            return array_merge([
                ['allow', 'actions' => ['index'], 'users' => ['@']],
                ['allow', 'actions' => ['publicList']],
                
            ], parent::accessRules());
        }
        public function actionOrganize($surveyId)
        {
            $this->layout = 'main';
            $groups = QuestionGroup::model()->findAllByAttributes(array(
                'sid' => $surveyId
            ));
            $this->render('organize', compact('groups'));
        }

        public function actionIndex() {
            $this->layout = 'main';
            $this->render('index', ['surveys' => new \CActiveDataProvider(Survey::model()->accessible())]);
        }

        public function actionPublicList()
        {
            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

            ));
        }
        
        public function actionUpdate($id) {

            $survey = $this->loadModel($id);
            if (App()->request->isPostRequest && $survey = $this->loadModel($id)) {
                var_dump($_POST);
            }
            $this->layout = 'survey';
            $this->survey = $survey;
            $this->render('update', ['survey' => $survey]);
        }

        public function actionActivate($id) {
            $this->layout = 'survey';
            $survey = $this->loadModel($id);
            if (App()->request->isPostRequest) {
                $survey->activate();
                App()->user->setFlash('succcess', "Survey activated.");
                $this->redirect(['surveys/update', 'id' => $survey->sid]);
            }

            $this->render('activate', ['survey' => $survey]);
        }

        public function actionDeactivate($id) {
            $this->layout = 'survey';
            $survey = $this->loadModel($id);
            if (App()->request->isPostRequest) {
                $survey->deactivate();
                App()->user->setFlash('succcess', "Survey deactivated.");
                $this->redirect(['surveys/update', 'id' => $survey->sid]);
            }

            $this->survey = $survey;
            $this->render('deactivate', ['survey' => $survey]);
        }
        public function filters()
        {
            return array_merge(parent::filters(), ['accessControl']);
        }
        
        protected function loadModel($id) {
            $survey = Survey::model()->findByPk($id);
            if (!isset($survey)) {
                throw new \CHttpException(404, "Survey not found.");
            } elseif (!App()->user->checkAccess('survey', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $id])) {
                throw new CHttpException(403);
            }

            if ($this->layout == 'survey') {
                $this->survey = $survey;
            }
            return $survey;
        }

        public function actionRun($id) {
            // Redirect to old method.
            $this->redirect(['survey/index', 'sid' => $id, 'newtest' => 'y']);

        }

        public function actionUnexpire($id) {
            $this->layout = 'survey';

            $survey = $this->loadModel($id);
            if (App()->request->isPostRequest && $survey->unexpire()) {
                App()->user->setFlash('success', gT("Survey expiry date removed."));
                $this->redirect(['surveys/view', 'id' => $id]);
            }
            $this->render('unexpire', ['survey' => $survey]);
        }

        public function actionExpire($id)
        {
            $survey = $this->loadModel($id);

            if (App()->request->isPostRequest) {
//                $survey->deactivate();
//                App()->user->setFlash('succcess', "Survey deactivated.");
//                $this->redirect(['surveys/view', 'id' => $survey->sid]);


            }
            $this->layout = 'survey';
            $this->survey = $survey;
            $this->render('expire', ['survey' => $survey]);
        }

    }
?>
