<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerActiveRecordUpdate;
use LimeSurvey\ObjectPatch\Patcher;
use Answer;
use Question;
use QuestionL10n;
use QuestionAttribute;
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputAnswer,
    TransformerInputQuestion,
    TransformerInputQuestionL10ns,
    TransformerInputQuestionAttribute
};
use DI\FactoryInterface;
use Psr\Container\ContainerInterface;

class PatcherSurvey extends Patcher
{
    /**
     * Constructor
     *
     * @param FactoryInterface $diFactory
     * @param ContainerInterface $diContainer
     */
    public function __construct(FactoryInterface $diFactory, ContainerInterface $diContainer)
    {
        $this->addOpHandlerSurvey($diContainer);
        $this->addOpHandlerLanguageSetting($diContainer);
        $this->addOpHandlerQuestionGroup($diContainer);
        $this->addOpHandlerQuestionGroupL10n($diContainer);
        $this->addOpHandlerQuestionCreate($diContainer);
        $this->addOpHandlerQuestion($diFactory, $diContainer);
        $this->addOpHandlerQuestionL10n($diFactory, $diContainer);
        $this->addOpHandlerQuestionAttribute($diFactory, $diContainer);
        $this->addOpHandlerQuestionAnswer($diFactory, $diContainer);
        $this->addOpHandlerQuestionGroupReorder($diContainer);
    }

    private function addOpHandlerSurvey(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerSurveyUpdate::class
        ));
    }

    private function addOpHandlerLanguageSetting(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerLanguageSettingsUpdate::class
        ));
    }

    private function addOpHandlerQuestionGroup(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerQuestionGroup::class
        ));
    }

    private function addOpHandlerQuestionGroupL10n(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerQuestionGroupL10n::class
        ));
    }

    private function addOpHandlerQuestion(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'question',
                'model' => Question::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestion::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionCreate(ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diContainer->get(
            OpHandlerQuestionCreate::class
        ));
    }

    private function addOpHandlerQuestionL10n(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionL10n',
                'model' => QuestionL10n::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionL10ns::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionAttribute(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionAttribute',
                'model' => QuestionAttribute::model(),
                'transformer' => $diContainer->get(
                    TransformerInputQuestionAttribute::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionAnswer(FactoryInterface $diFactory, ContainerInterface $diContainer): void
    {
        $this->addOpHandler($diFactory->make(
            OpHandlerActiveRecordUpdate::class,
            [
                'entity' => 'questionAnswer',
                'model' => Answer::model(),
                'transformer' => $diContainer->get(
                    TransformerInputAnswer::class
                )
            ]
        ));
    }

    private function addOpHandlerQuestionGroupReorder(
        ContainerInterface $diContainer
    ): void {
        $this->addOpHandler($diContainer->get(
            OpHandlerQuestionGroupReorder::class
        ));
    }
}
