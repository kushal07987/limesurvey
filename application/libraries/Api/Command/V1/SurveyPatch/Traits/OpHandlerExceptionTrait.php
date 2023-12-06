<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\Op\OpInterface;

trait OpHandlerExceptionTrait
{
    /**
     * @param OpInterface $op
     * @param string $name
     * @return void
     * @throws OpHandlerException
     */
    private function throwNoValuesException(OpInterface $op, string $name = '')
    {
        if ($name !== '') {
            $msg = sprintf(
                'No values to update for %s in entity %s',
                $name,
                $op->getEntityType()
            );
        } else {
            $msg = sprintf(
                'No values to update for entity %s',
                $op->getEntityType()
            );
        }

        throw new OpHandlerException($msg);
    }

    /**
     * @param OpInterface $op
     * @param string $param
     * @return void
     * @throws OpHandlerException
     */
    private function throwRequiredParamException(
        OpInterface $op,
        string $param
    ) {
        throw new OpHandlerException(
            sprintf(
                'Required parameter "%s" is missing. Entity "%s"',
                $param,
                $op->getEntityType()
            )
        );
    }

    private function throwTransformerValidationErrors($errors, $op)
    {
        if (is_array($errors)) {
            throw new OpHandlerException(
                sprintf(
                    'Entity "%s" with id "%s" errors: "%s"',
                    $op->getEntityType(),
                    (
                        !is_array($op->getEntityId())
                        ? $op->getEntityId()
                        : print_r($op->getEntityId(), true)
                    ),
                    $errors[0]
                )
            );
        }
    }
}
