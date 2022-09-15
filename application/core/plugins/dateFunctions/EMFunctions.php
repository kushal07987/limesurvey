<?php

/**
 * This file is part of dateFunctions plugin
 * @version 1.0.0
 */

namespace dateFunctions;

use LimeExpressionManager;
use Survey;

class EMFunctions
{
    /**
     * Formats a date according to the Survey's date format for the specified language
     * @param string $date : a date in "Y-m-d H:i:s" format. Example: VALIDFROM.
     * @param string|null $language : the language used for localization. Defaults to current session language. If the language is not configured in the survey, the base language will be used. Example: TOKEN:LANGUAGE
     * @return string
     */
    public static function localize_date($date, $language = null)
    {
        if (empty($date)) {
            return '';
        }

        $surveyId = LimeExpressionManager::getLEMsurveyId();
        $survey = Survey::model()->findByPk($surveyId);

        if (empty($survey)) {
            return '';
        }

        if (empty($language)) {
            $language = \Yii::app()->getLanguage();
        }

        // If the specified language is not one of the survey's languages, fallback to the survey's base language.
        if (!in_array($language, $survey->getAllLanguages()) || empty($survey->languagesettings[$language])) {
            $language = $survey->language;
        }

        $dateFormat = $survey->languagesettings[$language]->surveyls_dateformat;
        $dateFormatDetails = getDateFormatData($dateFormat);
        $datetimeobj = new \Date_Time_Converter($date, "Y-m-d H:i:s");
        return $datetimeobj->convert($dateFormatDetails['phpdate']);
    }
}
