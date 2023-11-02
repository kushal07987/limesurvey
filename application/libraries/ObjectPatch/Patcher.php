<?php

namespace LimeSurvey\ObjectPatch;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;

class Patcher
{
    private $opHandlers = [];

    /**
     * Apply patch
     *
     * @throws ObjectPatchException
     */
    public function applyPatch($patch, $context = []): array
    {
        $returnedData = [];
        $operationsApplied = 0;
        if (is_array($patch) && !empty($patch)) {
            foreach ($patch as $patchOpData) {
                $op = OpStandard::factory(
                    $patchOpData['entity'] ?? null,
                    $patchOpData['op'] ?? null,
                    $patchOpData['id'] ?? null,
                    $patchOpData['props'] ?? null,
                    $context ?? null
                );
                $returnedData[] = $this->handleOp($op);
                $operationsApplied++;
            }
        }
        return $this->reorganizeReturnedData($returnedData, $operationsApplied);
    }

    /**
     * Add operation handler
     *
     */
    public function addOpHandler(OpHandlerInterface $opHandler): void
    {
        $this->opHandlers[] = $opHandler;
    }

    /**
     * Apply operation
     *
     * @param OpInterface $op
     * @return array
     * @throws ObjectPatchException
     */
    private function handleOp(OpInterface $op): array
    {
        $handled = false;
        $returnedData = [];
        foreach ($this->opHandlers as $opHandler) {
            if (!$opHandler->canHandle($op)) {
                continue;
            }
            if ($opHandler->isValidPatch($op)) {
                $return = $opHandler->handle($op);
                $returnedData = is_array($return) ? $return : [];
            } else {
                throw new ObjectPatchException(
                    sprintf(
                        'Invalid patch for handler (entityType: %s)',
                        $op->getEntityType()
                    )
                );
            }
            $handled = true;
            break;
        }

        if (!$handled) {
            throw new ObjectPatchException(
                sprintf(
                    'No operation handler found for "%s":"%s":"%s"',
                    $op->getEntityType(),
                    $op->getType()->getId(),
                    print_r($op->getEntityId(), true)
                )
            );
        }
        return $returnedData;
    }

    /**
     * Moves mappings of tempIds and their real ids of same object types
     * together for a more structured return to the client.
     * @param array $returnedData
     * @param int $operationsApplied
     * @return array
     */
    private function reorganizeReturnedData(
        array $returnedData,
        int $operationsApplied
    ): array {
        $organizedData = [];
        $knownMaps = [
            'questionGroupsMap',
            'questionsMap',
            'subquestionsMap',
            'answersMap'
        ];
        foreach ($returnedData as $data) {
            foreach ($knownMaps as $knownMap) {
                $mapping = $this->searchKeyInArray($data, $knownMap);
                if ($mapping !== null) {
                    if (array_key_exists($knownMap, $organizedData)) {
                        $organizedData[$knownMap] = array_merge(
                            $organizedData[$knownMap],
                            $mapping
                        );
                    } else {
                        $organizedData[$knownMap] = $mapping;
                    }
                }
            }
        }
        $organizedData['operationsApplied'] = $operationsApplied;
        return $organizedData;
    }

    /**
     * Recursive array search by key for multidimensional arrays.
     * Returns the value, if key was found.
     * @param $array
     * @param $key
     * @return mixed|null
     */
    private function searchKeyInArray($array, $key)
    {
        foreach ($array as $k => $v) {
            if ($k === $key) {
                return $v;
            } elseif (is_array($v)) {
                $result = $this->searchKeyInArray($v, $key);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null; // Key not found
    }
}
