<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\Op\OpInterface;

class ValidationErrorItem
{
    /**
     * @var array the error message(s) from the validation error
     */
    public array $systemErrors = [];

    /**
     * @var string the error message for the user
     */
    public string $error = '';

    /**
     * @var string the name of the entity of that operation
     */
    public string $entity;

    /**
     * @var string|null the id of that operation
     */
    public ?string $id;

    /**
     * @var string the name of the op of that operation
     */
    public string $op;

    /**
     * @param string $error
     * @param array $errorMessages
     * @param OpInterface $patchOpData
     */
    public function __construct(
        string $error,
        array $errorMessages,
        OpInterface $patchOpData
    ) {
        $this->error = $error;
        $this->systemErrors = $errorMessages;
        $this->entity = $patchOpData->getEntityType();
        $this->op = $patchOpData->getType()->getId();
        $this->id = $patchOpData->getEntityId();
    }
}
