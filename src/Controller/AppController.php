<?php

namespace App\Controller;

use App\Entity\User;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\StorageAttributes;
use Stevenmaguire\OAuth2\Client\Provider\Dropbox;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

class AppController extends AbstractController
{


    public function __construct(
        #[Autowire(param: 'env(OAUTH_DROPBOX_CLIENT_ID)')] private string $client,
        #[Autowire(param: 'env(OAUTH_DROPBOX_CLIENT_SECRET)')] private string $secret,
    )
    {
    }

    #[Route('/', name: 'app_homepage')]
    public function index(Request $request, UrlGeneratorInterface $urlGenerator): Response
    {


        /** @var User $user */
        if ($user = $this->getUser()) {
            if ($dropbox = $user->getIdentifiers()['dropbox']??null) {
                $authorizationToken = $dropbox['accessToken']['access_token'];
                $client = new Client($authorizationToken);
                $adapter = new DropboxAdapter($client);

                $filesystem = new Filesystem($adapter, ['case_sensitive' => false]);
                // https://flysystem.thephpleague.com/docs/usage/directory-listings/
                /** @var DirectoryAttributes $content */
                $dirs = [];
                foreach ($filesystem->listContents('/', true)
                             ->filter(fn (StorageAttributes $attributes) => $attributes->isDir())
                         as $content) {
                    $dirs[] = $content->path();
                }
                dd($dirs);
            }
        }

        $token = null;
        $user = null;
        $state = $request->get('state', $request->getSession()->get('oauth2state'));
        $provider = new Dropbox($params = [
            'clientId'          => $this->client,
            'clientSecret'      => $this->secret,
            'redirectUri'       => $urlGenerator->generate('connect_dropbox_check', referenceType: $urlGenerator::ABSOLUTE_URL)
        ]);

        // state is either in the querystring or the session
        if (empty($state)) {
            return $this->render('app/index.html.twig', [
                'user' => $user,
                'redirectUrl' => $params['redirectUri'],
                'clientId' => $this->client,
            ]);
        }

        if ($code = $request->get('code')) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

// Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            // Optional: Now you have a token you can look up a users profile data
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                printf('Hello %s!', $user->getId());

            } catch (\Exception $e) {

                // Failed to get user details
                exit('Oh dear...');
            }


            // Use this to interact with an API on the users behalf
            echo $token->getToken();
        }

    }
}
