<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputQuestionGroup extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'gid' => ['type' => 'int'],
            'sid' => ['type' => 'int'],
            'sortOrder' => ['key' => 'group_order', 'type' => 'int', 'required' => true],
            'randomizationGroup' => 'randomization_group',
            'gRelevance' => 'grelevance',
            'tempId' => true
        ]);
    }
}
