<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyTrait;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeDelete;

class OpHandlerAnswerDelete implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected QuestionAggregateService $questionAggregateService;

    /**
     * @param QuestionAggregateService $questionAggregateService
     */
    public function __construct(
        QuestionAggregateService $questionAggregateService
    ) {
        $this->questionAggregateService = $questionAggregateService;
    }

    /**
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        $isDeleteOperation = $op->getType()->getId() === OpTypeDelete::ID;
        $isAnswerEntity = $op->getEntityType() === 'answer';

        return $isAnswerEntity && $isDeleteOperation;
    }

    /**
     * Deletes an answer from the question.
     * This is the expected structure:
     * "patch": [
     *          {
     *              "entity": "answer",
     *              "op": "delete",
     *              "id": "12345",
     *         }
     *  ]
     *
     * @param OpInterface $op
     * @return void
     * @throws PermissionDeniedException
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $this->questionAggregateService->deleteAnswer(
            $this->getSurveyIdFromContext($op),
            $op->getEntityId()
        );
    }
}
