<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputAnswer extends Transformer
{
    public function __construct(
        TransformerInputAnswerL10ns $transformerInputAnswerL10ns
    ){
        $this->setDataMap([
            'aid' => ['type' => 'int'],
            'qid' => ['type' => 'int'],
            'oldCode' => 'oldcode',
            'code' => true,
            'sortOrder' => ['key' => 'sortorder', 'type' => 'int'],
            'assessmentValue' => ['key' => 'assessment_value', 'type' => 'int'],
            'scaleId' => ['key' => 'scale_id', 'type' => 'int'],
            'tempId' => true,
            'l10ns' => [
                'key' => 'answeroptionl10n',
                'collection' => true,
                'transformer' => $transformerInputAnswerL10ns
            ]
        ]);
    }

    public function transformAll($collection)
    {
        $collection = parent::transformAll($collection);
        $output = [];
        if (is_array($collection)) {
            foreach ($collection as $index => $answer) {
                // second array index needs to be the scaleId
                $scaleId = array_key_exists(
                    'scale_id',
                    $answer
                ) ? $answer['scale_id'] : 0;
                $index = array_key_exists(
                    'aid',
                    $answer
                ) ? $answer['aid'] : $index;
                $output[$index][$scaleId] = $answer;
            }
        }
        return $output;
    }
}
