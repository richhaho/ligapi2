<?php

declare(strict_types=1);

namespace App\Api;

use App\Exceptions\Api\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class DtoArgumentResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;
    
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return 0 === strpos($argument->getType(), 'App\\Api\\Dto\\');
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $argument->getType(),
            'json'
        );
    
//        $violations = $this->validator->validate($dto);
//
//        if (count($violations) > 0) {
//            throw ValidationException::fromViolationList($violations);
//        }

        yield $dto;
    }
}
