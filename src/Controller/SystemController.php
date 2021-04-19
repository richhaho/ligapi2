<?php


namespace App\Controller;


use App\Services\CleverReachService;
use App\Services\MercureService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/system", name="api_system_")
 */
class SystemController
{
    private CleverReachService $cleverReachService;
    
    public function __construct(CleverReachService $cleverReachService)
    {
        $this->cleverReachService = $cleverReachService;
    }
    
    
    // TODO: rausnehmen
    /**
     * @Route(path="/fixtures", name="load_fixtures", methods={"GET"})
     */
    public function delete(
        KernelInterface $kernel
    ): Response
    {
        $environment = $kernel->getEnvironment();
        if ($environment === 'dev') {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            
            $input = new ArrayInput([
                'command' => 'doctrine:fixtures:load',
                '-n' => '-n'
            ]);
            
            $output = new BufferedOutput();
            $application->run($input, $output);
        }
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(name="ping", methods={"GET"})
     */
    public function index(MercureService $mercureService): Response
    {
        $mercureService->sendMessage('123',['32' => 'test 123']);
        
        return JsonResponse::fromJsonString('{ "ping": "pong" }');
    }
    
}
