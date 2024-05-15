<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\Models\Services\SurveyAggregateService;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};

class OpHandlerSurveyDeactivate implements OpHandlerInterface
{
    use OpHandlerExceptionTrait;
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

    protected TransformerInputSurvey $transformer;

    public function __construct(TransformerInputSurvey $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isSurveyDeactivate = $op->getEntityType() === "surveyDeactivate";

        return $isUpdateOperation && $isSurveyDeactivate;
    }

    /**
     * @param OpInterface $op
     * @return void
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyDeactivateService = $diContainer->get(
            SurveyAggregateService::class
        );
        $surveyDeactivateService->deactivate($op->getEntityId(), $op->getProps());
    }

    /**
     * Checks if patchs is valid for this operation
     * @param OpInterface $op
     * @return  array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->transformer->validate(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );

        return $this->getValidationReturn(
            gT('Could not save survey'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
