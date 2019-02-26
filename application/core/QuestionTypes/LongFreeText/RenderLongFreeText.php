<?php



/**
 * RenderClass for Boilerplate Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderLongFreeText extends QuestionBaseRenderer
{
    public function getMainView()
    {
        return '/survey/questions/answer/longfreetext/answer';
    }
    
    public function getRows()
    {
        return;
    }

    public function render($sCoreClasses = '')
    {
        $answer = '';
        $inputnames = [];
        $kpclass = "";
        $extraclass = "";
        $maxlength = "";
        $withColumn = false;
        $inputsize = null;

        $drows = $this->setDefaultIfEmpty(
            $this->getQuestionAttribute('display_rows'), 
            ($this->oQuestion->type == Question::QT_T_LONG_FREE_TEXT  ? 4 : 30)
        );

        if ($this->oQuestion->survey->nokeyboard == 'Y') {
            $this->includeKeypad();
            $kpclass     = "text-keypad";
            $extraclass .= " inputkeypad";
        } 

        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        if (intval(trim($this->getQuestionAttribute('maximum_chars'))) > 0) {
            $maxlength = intval(trim($this->getQuestionAttribute('maximum_chars')));
            $extraclass .= " ls-input-maxchars";
        }
    
        // text_input_width can not be empty, except with old survey (wher can be empty or up to 12 see bug #11743
        if (trim($this->getQuestionAttribute('text_input_width')) != '') {
            $col         = ($this->getQuestionAttribute('text_input_width') <= 12) ? $this->getQuestionAttribute('text_input_width') : 12;
            $extraclass .= " col-sm-".trim($col);
            $withColumn = true;
        }
        
        if (ctype_digit(trim($this->getQuestionAttribute('input_size')))) {
            $inputsize = trim($this->getQuestionAttribute('input_size'));
            $extraclass .= " ls-input-sized";
        }

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'extraclass'             => $extraclass,
            'coreClass'              => "ls-answers answer-item text-item ".$sCoreClasses,
            'withColumn'             => $withColumn,
            'kpclass'                => $kpclass,
            'name'                   => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'drows'                  => $drows,
            'checkconditionFunction' => 'checkconditions(this.value, this.name, this.type)',
            'dispVal'                => htmlspecialchars($this->mSessionValue),
            'inputsize'              => $inputsize,
            'maxlength'              => $maxlength,
        ), true);

        if (!empty($this->getQuestionAttribute('time_limit', 'value'))) {
            $answer .= $this->getTimeSettingRender();
        }

        $inputnames[] = $this->sSGQA;
        
        $this->registerAssets();
        return array($answer, $inputnames);
    }
}
