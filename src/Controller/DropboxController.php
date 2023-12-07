<?php

namespace App\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\DropboxClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Survos\AuthBundle\Services\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DropboxController extends AbstractController
{

    public function __construct(
        private ClientRegistry $clientRegistry,
//        private AuthService $baseService,
//        private Registry $registry,
//        private RouterInterface $router,
//        private UserProviderInterface $userProvider,
//        private EntityManagerInterface $entityManager,
//        private string $userClass,
    ) {
//        $this->entityManager = $this->registry->getManagerForClass($this->userClass);
        //        dd($this->clientRegistry);
        //        $this->clientRegistry = $this->baseService->getClientRegistry();
    }

    /**
     * Link to this controller to start the "connect" process
     */
    #[Route('/connect/dropbox', 'connect_dropbox_start')]
    public function connectAction()
    {
        // on Symfony 3.3 or lower, $clientRegistry = $this->get('knpu.oauth2.registry');

        // will redirect to Dropbox!
        return $this->clientRegistry
            ->getClient('dropbox') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
//                'files.content.write account_info.write'// the scopes you want to access
            ]);
    }

    /**
     * After going to Dropbox, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     */
    #[Route(path: '/connect/dropbox/check', name: 'connect_dropbox_check')]
    public function connectCheckAction(Request $request)
    {
        /** @var DropboxClient $client */
        $client = $this->clientRegistry->getClient('dropbox');

// OR: get the access token and then user
        $accessToken = $client->getAccessToken();
        $user = $client->fetchUserFromToken($accessToken);
        dd($user, $client);

// access the underlying "provider" from league/oauth2-client
        $provider = $client->getOAuth2Provider();
        try {
            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\DropboxUser $user */
//            $user = $client->fetchUser();
//            dump($user);
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            dd($e->getMessage());
        }


            if ($code = $request->get('code')) {
                $provider = $client->getOAuth2Provider();
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
                // We got an access token, let's now get the user's details
                $xuser = $provider->getResourceOwner($token);
                $userAccessToken = $token->getToken();

                // create the
                dd($xuser, $token, $userAccessToken);
            }

//            return $this->redirectToRoute('app_dropbox', ['code' =>$request->get('code')]);

    }
}
