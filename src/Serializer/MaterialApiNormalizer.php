<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Api\ApiContext;
use App\Api\RouteMap;
use App\Entity\Company;
use App\Entity\ConsignmentItem;
use App\Entity\Data\File;
use App\Entity\ItemGroup;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Entity\OrderSource;
use App\Entity\Project;
use App\Entity\Supplier;
use App\Entity\Task;
use App\Services\CurrentUserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MaterialApiNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private ObjectNormalizer $objectNormalizer;
    private UrlGeneratorInterface $generator;
    private RequestStack $requestStack;
    private RouteMap $routeMap;
    private TagAwareCacheInterface $serializer_cache;
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        ObjectNormalizer $objectNormalizer,
        UrlGeneratorInterface $generator,
        RequestStack $requestStack,
        RouteMap $routeMap,
        TagAwareCacheInterface $serializer_cache,
        CurrentUserProvider $currentUserProvider
    ) {
        $this->objectNormalizer = $objectNormalizer;
        $this->generator = $generator;
        $this->requestStack = $requestStack;
        $this->routeMap = $routeMap;
        $this->serializer_cache = $serializer_cache;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    /**
     * @param Material $material
     */
    public function normalize($material, string $format = null, array $context = [])
    {
        $config = $this->requestStack->getMasterRequest()->attributes->get('_api_context', new ApiContext([]));
        $groups = $config->groups;
        $selfRoute = $config->selfRoute;
        
        $key = $material->getId() . implode('_', $groups);
        
        return $this->serializer_cache->get($key, function (ItemInterface $item) use ($material, $format, $groups, $selfRoute) {
            $item->tag(str_replace('\\', '_', Company::class) . '_' . $material->getCompany()->getId());
            $item->tag(str_replace('\\', '_', Material::class) . '_' . $material->getId());
            if ($material->getItemGroup()) {
                $item->tag(str_replace('\\', '_', ItemGroup::class) . '_' . $material->getItemGroup()->getId());
            }
            foreach ($material->getMaterialLocations() as $materialLocation) {
                $item->tag(str_replace('\\', '_', MaterialLocation::class) . '_' . $materialLocation->getId());
                if ($materialLocation->getProject()) {
                    $item->tag(str_replace('\\', '_', Project::class) . '_' . $materialLocation->getProject()->getId());
                }
            }
            /** @var OrderSource $orderSource */
            foreach ($material->getOrderSources() as $orderSource) {
                $item->tag(str_replace('\\', '_', OrderSource::class) . '_' . $orderSource->getId());
                $item->tag(str_replace('\\', '_', Supplier::class) . '_' . $orderSource->getSupplier()->getId());
            }
            foreach ($material->getTasks() as $task) {
                $item->tag(str_replace('\\', '_', Task::class) . '_' . $task->getId());
            }
            foreach ($material->getConsignmentItems() as $consignmentItem) {
                $item->tag(str_replace('\\', '_', ConsignmentItem::class) . '_' . $consignmentItem->getId());
            }
            foreach ($material->getFiles() as $file) {
                $tag = str_replace('\\', '_', File::class . '_' . File::fromArray($file)->getRelativePath());
                $tag = str_replace('/', '_', $tag);
                $item->tag($tag);
            }
            
            $data = $this->objectNormalizer->normalize($material, $format, ['groups' => $groups]);
    
            $data['_links'] = [];
            //TODO: company also has e.g. /api/keyys/... instead of /api/companies/...
            // TODO: use RouteMap
            if (null !== $selfRoute) {
                $data['_links']['self'] = $this->generator->generate(
                    $selfRoute,
                    [
                        'id' => $material->getId()
                    ]
                );
            }
            return $data;
        });
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Material;
    }
    
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
