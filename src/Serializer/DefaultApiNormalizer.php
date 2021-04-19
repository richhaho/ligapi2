<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Api\ApiContext;
use App\Api\RouteMap;
use App\Entity\Material;
use App\Services\CurrentUserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DefaultApiNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private ObjectNormalizer $objectNormalizer;
    private UrlGeneratorInterface $generator;
    private RequestStack $requestStack;
    private RouteMap $routeMap;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        ObjectNormalizer $objectNormalizer,
        UrlGeneratorInterface $generator,
        RequestStack $requestStack,
        RouteMap $routeMap,
        CurrentUserProvider $currentUserProvider
    ) {
        $this->objectNormalizer = $objectNormalizer;
        $this->generator = $generator;
        $this->requestStack = $requestStack;
        $this->routeMap = $routeMap;
        $this->currentUserProvider = $currentUserProvider;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $config = $this->requestStack->getMasterRequest()->attributes->get('_api_context', new ApiContext([]));
        $groups = $config->groups;
        $selfRoute = $config->selfRoute;
        
        $data = $this->objectNormalizer->normalize($object, $format, ['groups' => $groups]);

        $data['_links'] = [];
        //TODO: company also has e.g. /api/keyys/... instead of /api/companies/...
        // TODO: use RouteMap
        if (null !== $selfRoute) {
            $data['_links']['self'] = $this->generator->generate(
                $selfRoute,
                [
                    'id' => $object->getId()
                ]
            );
        }
        return $data;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return
            !$data instanceof Material &&
            is_object($data)
                &&
                    (
                        0 === strpos(get_class($data), 'App\\Entity\\') ||
                        0 === strpos(get_class($data), 'Proxies\\__CG__\\App\\Entity')
                    )
                && false === strpos(get_class($data), 'App\\Entity\\Data')
            ;
    }
    
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
