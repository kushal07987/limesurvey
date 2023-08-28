<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\V1\SurveyPatch\PatcherSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use DI\FactoryInterface;

class SurveyPatch implements CommandInterface
{
    use AuthPermissionTrait;

    protected AuthSession $authSession;
    protected FactoryInterface $diFactory;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        AuthSession $authSession,
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    ) {
        $this->authSession = $authSession;
        $this->diFactory = $diFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey patch command
     *
     * Apply patch and respond with update patch to be applied to the source (if any).
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $id = (string) $request->getData('_id');
        $patch = $request->getData('patch');

        if (
            !$this->authSession
                ->checkKey($sessionKey)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $patcher = $this->diFactory->make(
            PatcherSurvey::class
        );
        try {
            $patcher->applyPatch($patch, ['id' => $id]);
        } catch (ObjectPatchException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory
            ->makeSuccess(true);
    }
}
