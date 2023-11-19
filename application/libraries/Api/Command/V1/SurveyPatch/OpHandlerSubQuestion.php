<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use Question;
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputQuestion,
    TransformerInputQuestionL10ns
};
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    QuestionAggregateService,
    QuestionAggregateService\QuestionService,
    QuestionAggregateService\SubQuestionsService
};
use LimeSurvey\ObjectPatch\{Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeCreate,
    OpType\OpTypeUpdate
};

/**
 * Class OpHandlerSubQuestion can handle create and update
 * of subquestions which belong to a single question.
 */
class OpHandlerSubQuestion implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerQuestionTrait;

    protected QuestionAggregateService $questionAggregateService;
    protected SubQuestionsService $subQuestionsService;
    protected QuestionService $questionService;
    protected TransformerInputQuestion $transformer;
    protected TransformerInputQuestionL10ns $transformerL10ns;

    public function __construct(
        QuestionAggregateService $questionAggregateService,
        SubQuestionsService $subQuestionsService,
        QuestionService $questionService,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputQuestion $transformer
    ) {
        $this->questionAggregateService = $questionAggregateService;
        $this->subQuestionsService = $subQuestionsService;
        $this->questionService = $questionService;
        $this->transformer = $transformer;
        $this->transformerL10ns = $transformerL10n;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return (
                $op->getType()->getId() === OpTypeUpdate::ID
                || $op->getType()->getId() === OpTypeCreate::ID
            )
            && $op->getEntityType() === 'subquestion';
    }

    /**
     * Handle subquestion create or update operation.
     * Attention: subquestions not present in the patch will be deleted.
     * Expects a patch structure like this for update:
     * {
     *     "patch": [{
     *             "entity": "subquestion",
     *             "op": "update",
     *             "id": 722, //parent qid
     *             "props": {
     *                 "0": {
     *                     "qid": 728,
     *                     "title": "SQ001new",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "subger1updated",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "sub1updated",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "qid": 729,
     *                     "title": "SQ002new",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "subger2updated",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "sub2updated",
     *                             "language": "en"
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * Expects a patch structure like this for create:
     * {
     *     "patch": [{
     *             "entity": "subquestion",
     *             "op": "create",
     *             "id": 722,
     *             "props": {
     *                 "0": {
     *                     "tempId": "456789",
     *                     "title": "SQ011",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "germanized1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "englishized",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "title": "SQ012",
     *                     "tempId": "345678",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "germanized2",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "englishized2",
     *                             "language": "en"
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): array
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $preparedData = $this->prepareSubQuestions(
            $op,
            $this->transformer,
            $this->transformerL10ns,
            $op->getProps(),
            ['subquestions']
        );
        //be careful here! if for any reason the incoming data is not prepared
        //as it should, all existing subquestions will be deleted!
        if (count($preparedData) === 0) {
            throw new OpHandlerException(
                'No data to create or update a subquestion'
            );
        }
        $questionId = $op->getEntityId();
        $question = $this->questionService->getQuestionBySidAndQid(
            $surveyId,
            $questionId
        );
        $this->subQuestionsService->save(
            $question,
            $preparedData
        );
        return $this->getSubQuestionNewIdMapping($question, $preparedData);
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        //when is the patch (the operation a valid operation)?
        //--> update:  props should include qid (which means update)
        //--> create:  props should include tempId (which means create)
        $props = $op->getProps();
        return array_key_exists('qid', $props) || array_key_exists('tempId', $props);
    }
}
