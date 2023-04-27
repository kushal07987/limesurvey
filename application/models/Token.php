<?php
/**
 *
 * For code completion we add the available scenario's here
 * Attributes
 * @property int      $tid
 * @property string   $firstname
 * @property string   $lastname
 * @property string   $email
 * @property string   $emailstatus
 * @property string   $token
 * @property string   $language
 * @property string   $blacklisted
 * @property string   $sent
 * @property string   $remindersent
 * @property int      $remindercount
 * @property string   $completed
 * @property int      $usesleft
 * @property DateTime $validfrom
 * @property DateTime $validuntil
 *
 * Relations
 * @property Survey $survey The survey this token belongs to.
 *
 * Scopes
 * @method Token incomplete() incomplete() Select only uncompleted tokens
 * @method Token usable() usable() Select usable tokens: valid daterange and usesleft > 0
 *
 */

use \LimeSurvey\PluginManager\PluginEvent;

/**
 * Class Token
 *
 * @property integer $tid Token ID
 * @property string $participant_id Participant ID
 * @property string $firstname Participant's first name
 * @property string $lastname Participant's last name
 * @property string $email Participant's e-mail address
 * @property string $emailstatus Participant's e-mail address status: OK/bounced/OptOut
 * @property string $token Participant's token
 * @property string $language Participant's language eg: en
 * @property string $blacklisted Whether participant is blacklisted: (Y/N)
 * @property string $sent
 * @property string $remindersent
 * @property integer $remindercount
 * @property string $completed Participant completed status (N:Not completed; Q:Locked with quota; 'YYYY-MM-DD hh:mm': date of completion)
 * @property integer $usesleft How many uses left to fill questionnaire for this participant
 * @property string $validfrom
 * @property string $validuntil
 * @property integer $mpid //TODO Describe me!
 *
 * @property Survey $survey
 * @property SurveyLink $surveylink
 * @property Response[] $responses
 * @property CDbTableSchema $tableSchema
 */
abstract class Token extends Dynamic
{

    /**
     * Set defaults
     * @inheritdoc
     */
    public function init()
    {
        // Set the default values
        $this->usesleft = 1;
        $this->completed = "N";
    }
    /** @inheritdoc */
    public function attributeLabels()
    {
        $labels = array(
            'tid' => gT('Token ID'),
            'partcipant_id' => gT('Participant ID'),
            'firstname' => gT('First name'),
            'lastname' => gT('Last name'),
            'email' => gT('Email address'),
            'emailstatus' => gT('Email status'),
            'token' => gT('Token'),
            'language' => gT('Language code'),
            'blacklisted' => gT('Blacklisted'),
            'sent' => gT('Invitation sent date'),
            'remindersent' => gT('Last reminder sent date'),
            'remindercount' =>gT('Total numbers of sent reminders'),
            'completed' => gT('Completed'),
            'usesleft' => gT('Uses left'),
            'validfrom' => gT('Valid from'),
            'validuntil' => gT('Valid until'),
        );
        foreach (decodeTokenAttributes($this->survey->attributedescriptions) as $key => $info) {
            $labels[$key] = $info['description'];
        }
        return $labels;
    }

    /** @inheritdoc
     * Delete related SurveyLink if it's deleted
     */
    public function beforeDelete()
    {
        $result = parent::beforeDelete();
        if ($result && isset($this->surveylink)) {
            if (!$this->surveylink->delete()) {
                throw new CException('Could not delete survey link. Token was not deleted.');
            }
            return true;
        }
        return $result;
    }

    /** @inheritdoc
     * Delete related SurveyLink at same time
     */
    public function deleteAllByAttributes($attributes, $condition = '', $params = array())
    {
        $builder=$this->getCommandBuilder();
        $participantCriteria=$builder->createCriteria($condition,$params);
        $participantCriteria->select = array('tid','participant_id');
        $participantCriteria->addCondition('participant_id is not null');
        $oParticipantToDelete = self::model($this->dynamicId)->findAll($participantCriteria);
        $result = parent::deleteAllByAttributes($attributes, $condition, $params);
        if($result && !empty($oParticipantToDelete)) {
            /* Get the participant not deleted : we must not delete survey link */
            $oParticipantNotDeleted = self::model($this->dynamicId)->findAll($participantCriteria);
            $tidToDelete = array_diff(CHtml::listData($oParticipantToDelete,'tid','tid'),CHtml::listData($oParticipantNotDeleted,'tid','tid'));
            if(!empty($tidToDelete)) {
                SurveyLink::model()->deleteAllByAttributes(array('token_id'=>$tidToDelete,'survey_id'=>$this->dynamicId));
            }
        }
        return $result;
    }

    /**
     * @param integer $surveyId
     * @param array $extraFields
     * @return CDbTableSchema
     */
    public static function createTable($surveyId, array $extraFields = array())
    {
        $surveyId = intval($surveyId);
        // Specify case sensitive collations for the token
        $sCollation = '';
        if (Yii::app()->db->driverName == 'mysql' || Yii::app()->db->driverName == 'mysqli') {
            $sCollation = "COLLATE 'utf8mb4_bin'";
        }
        if (Yii::app()->db->driverName == 'sqlsrv'
            || Yii::app()->db->driverName == 'dblib'
            || Yii::app()->db->driverName == 'mssql') {

            $sCollation = "COLLATE SQL_Latin1_General_CP1_CS_AS";
        }
        $fields = array(
            'tid' => 'pk',
            'participant_id' => 'string(50)',
            'firstname' => 'string(150)',
            'lastname' => 'string(150)',
            'email' => 'text',
            'emailstatus' => 'text',
            'token' => "string(35) {$sCollation}",
            'language' => 'string(25)',
            'blacklisted' => 'string(17)',
            'sent' => "string(17) DEFAULT 'N'",
            'remindersent' => "string(17) DEFAULT 'N'",
            'remindercount' => 'integer DEFAULT 0',
            'completed' => "string(17) DEFAULT 'N'",
            'usesleft' => 'integer DEFAULT 1',
            'validfrom' => 'datetime',
            'validuntil' => 'datetime',
            'mpid' => 'integer'
        );

        foreach ($extraFields as $extraField) {
            $fields[$extraField] = 'text';
        }

        // create fields for the custom token attributes associated with this survey
        $tokenattributefieldnames = Survey::model()->findByPk($surveyId)->getTokenAttributes();
        foreach ($tokenattributefieldnames as $attrname=>$attrdetails) {
            if (!isset($fields[$attrname])) {
                $fields[$attrname] = 'text';
            }
        }

        $db = \Yii::app()->db;
        $sTableName = "{{tokens_{$surveyId}}}";

        $db->createCommand()->createTable($sTableName, $fields);

        /**
         * The random component in the index name is needed because Postgres is being the dorky kid and 
         * complaining about duplicates when renaming the table and trying to use the same index again 
         * on a new token table (for example on reactivation)
         */
        $db->createCommand()->createIndex("idx_token_token_{$surveyId}_".rand(1, 50000), $sTableName, 'token');
        
        // MSSQL does not support indexes on text fields so not needed here
        switch (Yii::app()->db->driverName){
            case 'mysql':
            case 'mysqli':
                $db->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                break;
            case 'pgsql':
                $db->createCommand()->createIndex('idx_email_'.$surveyId.'_'.rand(1, 50000), $sTableName, 'email', false);
                break;
        }

        // Refresh schema cache just in case the table existed in the past, and return if table exist
        return $db->schema->getTable($sTableName, true);
    }

    /**
     * @param string $token
     * @return Token
     */
    public function findByToken($token)
    {
        return $this->findByAttributes(array(
            'token' => $token
        ));
    }

    /**
     * Generates a token for this object.
     * @throws CHttpException
     */
    public function generateToken()
    {
        $iTokenLength = $this->survey->tokenlength;
        $this->token = $this::generateRandomToken($iTokenLength);
        $counter = 0;
        while (!$this->validate(array('token'))) {
            $this->token = $this::generateRandomToken($iTokenLength);
            $counter++;
            // This is extremely unlikely.
            if ($counter > 10) {
                throw new CHttpException(500, 'Failed to create unique token in 10 attempts.');
            }
        }
    }

    /**
     * Creates a random token string without special characters
     *
     * @param integer $iTokenLength
     * @return string
     */
    public static function generateRandomToken($iTokenLength)
    {
        return str_replace(array('~', '_'), array('a', 'z'), Yii::app()->securityManager->generateRandomString($iTokenLength));
    }

    /**
     * Sanitize token show to the user (replace sanitize_helper sanitize_token)
     * @param string $token to sanitize
     * @return string sanitized token
     */
    public static function sanitizeToken($token)
    {
        // According to Yii doc : http://www.yiiframework.com/doc/api/1.1/CSecurityManager#generateRandomString-detail
        return preg_replace('/[^0-9a-zA-Z_~]/', '', $token);
    }

    /**
     * Sanitize string for any attribute
     * @param string $attribute to sanitize
     * @return string sanitized attribute
     */
    public static function sanitizeAttribute($attribute)
    {
        return filter_var($attribute, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    /**
     * Generates a token for all token objects in this survey.
     * Syntax: Token::model(12345)->generateTokens();
     * @return integer[]
     * @throws Exception
     */
    public function generateTokens()
    {
        if ($this->scenario != '') {
            throw new \Exception("This function should only be called like: Token::model(12345)->generateTokens");
        }
        $surveyId = $this->dynamicId;
        $iTokenLength = isset($this->survey) && is_numeric($this->survey->tokenlength) ? $this->survey->tokenlength : 15;

        $tkresult = Yii::app()->db->createCommand("SELECT tid FROM {{tokens_{$surveyId}}} WHERE token IS NULL OR token=''")->queryAll();
        //Exit early if there are not empty tokens
        if (count($tkresult) === 0) {
            return array(0, 0);
        }

        // Do NOT replace the following select with ActiveRecord as it uses too much memory
        $ntresult=Yii::app()->db->createCommand()->select('token')->from($this->tableName())-> where("token IS NOT NULL and token<>''")->queryColumn();
        // select all existing tokens
        foreach ($ntresult as $tkrow) {
            $existingtokens[$tkrow] = true;
        }        
        $newtokencount = 0;
        $invalidtokencount = 0;
        $newtoken = null;
        foreach ($tkresult as $tkrow) {
            $bIsValidToken = false;
            while ($bIsValidToken == false && $invalidtokencount < 50) {
                $newtoken = $this::generateRandomToken($iTokenLength);
                if (!isset($existingtokens[$newtoken])) {
                    $existingtokens[$newtoken] = true;
                    $bIsValidToken = true;
                    $invalidtokencount = 0;
                } else {
                    $invalidtokencount++;
                }
            }
            if ($bIsValidToken) {
                $this->updateByPk($tkrow['tid'], array('token' => $newtoken));
                $newtokencount++;
            } else {
                break;
            }
        }

        return array($newtokencount, count($tkresult));

    }
    /**
     * @inheritdoc
     * @return Token
     */
    public static function model($className = null)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * @param int $surveyId
     * @param string $scenario
     * @return Token Description
     */
    public static function create($surveyId, $scenario = 'insert')
    {
        return parent::create($surveyId, $scenario);
    }

    public function relations()
    {
        $result = array(
            'responses' => array(self::HAS_MANY, 'Response_'.$this->dynamicId, array('token' => 'token')),
            'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}"),
            'surveylink' => array(self::BELONGS_TO, 'SurveyLink', array('participant_id' => 'participant_id'), 'on' => "survey_id = {$this->dynamicId}")
        );
        return $result;
    }

    /** @inheritdoc */
    public function rules()
    {
        $aRules = array(
            array('token', 'unique', 'allowEmpty' => true),
            array('token', 'length', 'min' => 0, 'max'=>36),
            array('token', 'filter', 'filter' => array(self::class, 'sanitizeToken')),
            array('firstname', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('lastname', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('language', 'LSYii_Validators', 'isLanguage'=>true),
            array('language', 'length', 'min' => 2, 'max'=>25, 'allowEmpty'=>true),
            array(implode(',', $this->tableSchema->columnNames), 'safe'),
            /* pseudo date : force date or specific string ? */
            array('remindersent', 'length', 'min' => 0, 'max'=>17),
            array('remindersent', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('completed', 'length', 'min' => 0, 'max'=>17),
            array('remindersent', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('remindercount', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('email', 'filter', 'filter'=>'trim'),
            array('email', 'LSYii_EmailIDNAValidator', 'allowEmpty'=>true, 'allowMultiple'=>true, 'except'=>'allowinvalidemail'),
            array('emailstatus', 'default', 'value' => 'OK'),
            array('emailstatus', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('usesleft', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true, 'min'=>-2147483647, 'max'=>2147483647),
            array('mpid', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('blacklisted', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('validfrom', 'date','format'=>['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m','yyyy-M-d'],'allowEmpty'=>true),
            array('validuntil','date','format'=>['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m','yyyy-M-d'],'allowEmpty'=>true),
        );
        foreach (decodeTokenAttributes($this->survey->attributedescriptions) as $key => $info) {
            $aRules[] = array(
                $key, 'filter',
                'filter' => array(self::class, 'sanitizeAttribute'),
                'on'=>'register'
            );
            $aRules[] = array(
                $key,
                'LSYii_Validators',
                'except'=>'finalSubmit,register'
            );
        }
        return $aRules;
    }

    /** @inheritdoc */
    public function scopes()
    {
        $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));
        return array(
            'incomplete' => array(
                'condition' => "completed = 'N'"
            ),
            'usable' => array(
                'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now' AND usesleft > 0"
            ),
            'editable' => array(
                'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now'"
            ),
            'empty' => array(
                'condition' => 'token is null or token = ""'
            )
        );
    }

    /**
     * @return CDbDataReader|mixed
     */
    public function summary()
    {
        $criteria = $this->getDbCriteria();
        $criteria->select = array(
            "COUNT(*) as count",
            "COUNT(CASE WHEN (token IS NULL OR token='') THEN 1 ELSE NULL END) as invalid",
            "COUNT(CASE WHEN (sent!='N' AND sent<>'') THEN 1 ELSE NULL END) as sent",
            "COUNT(CASE WHEN (emailstatus LIKE 'OptOut%') THEN 1 ELSE NULL END) as optout",
            "COUNT(CASE WHEN (completed!='N' and completed<>'') THEN 1 ELSE NULL END) as completed",
            "COUNT(CASE WHEN (completed='Q') THEN 1 ELSE NULL END) as screenout",
        );
        $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria);
        return $command->queryRow();
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{tokens_'.$this->dynamicId.'}}';
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId() {
        return $this->getDynamicId();
    }
}
