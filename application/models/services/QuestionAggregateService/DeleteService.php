<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Condition;
use Question;
use QuestionL10n;

use LimeSurvey\Models\Services\{
    Proxy\ProxyExpressionManager,
    Exception\PersistErrorException,
    Exception\QuestionHasConditionsException
};

/**
 * Question Aggregate Delete Service
 */
class DeleteService
{
    private Question $modelQuestion;
    private QuestionL10n $modelQuestionL10n;
    private Condition $modelCondition;
    private ProxyExpressionManager $proxyExpressionManager;

    public function __construct(
        Question $modelQuestion,
        QuestionL10n $modelQuestionL10n,
        Condition $modelCondition,
        ProxyExpressionManager $proxyExpressionManager
    ) {
        $this->modelQuestion = $modelQuestion;
        $this->modelQuestionL10n = $modelQuestionL10n;
        $this->modelCondition = $modelCondition;
        $this->proxyExpressionManager = $proxyExpressionManager;
    }

    /**
     * Function responsible for deleting a question.
     *
     * @param int $questionId
     * @throws PersistErrorException
     * @throws QuestionHasConditionsException
     * @return void
     */
    public function delete($questionId)
    {
        $question = $this->modelQuestion->findByPk($questionId);
        if (empty($question)) {
            throw new PersistErrorException();
        }

        $this->proxyExpressionManager
            ->revertUpgradeConditionsToRelevance($questionId);

        // Check if any other questions have conditions which rely on this question.
        // - Don't delete if there are.
        $conditions = $this->modelCondition
                        ->findAllByAttributes(['cqid' => $questionId]);
        // There are conditions dependent on this question
        if (!empty($conditions)) {
            $message = gT(
                'Question could not be deleted. '
                . 'There are conditions for other questions that rely '
                . 'on this question. '
                . 'You cannot delete this question until those conditions '
                . 'are removed.'
            );
            throw new QuestionHasConditionsException($message);
        }

        $this->modelQuestionL10n
            ->deleteAllByAttributes(['qid' => $questionId]);

        $question->delete();
    }
}
