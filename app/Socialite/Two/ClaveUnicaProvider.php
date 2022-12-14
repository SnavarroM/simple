<?php

namespace App\Socialite\Two;

use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;


class ClaveUnicaProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['openid', 'run', 'name'];

    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(env('CU_AUTH_URL', 'https://accounts.claveunica.gob.cl/openid/authorize'), $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return env('CU_TOKEN_URL', 'https://accounts.claveunica.gob.cl/openid/token');
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post(env('CU_USER_INFO', 'https://accounts.claveunica.gob.cl/openid/userinfo'), [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['RolUnico']['numero'],
            'name' => implode(' ',$user['name']['nombres']) . ' ' . implode(' ', $user['name']['apellidos']),
            'first_name' => implode(' ',$user['name']['nombres']),
            'last_name' => implode(' ', $user['name']['apellidos']),
            'primer_apellido' => $user['name']['apellidos'][0],
            'segundo_apellido' => count($user['name']['apellidos']) > 1 ? $user['name']['apellidos'][1] : '',
            'run' => $user['RolUnico']['numero'],
            'dv' => $user['RolUnico']['DV'],
            'email' => isset($user['email']) ? $user['email'] : null
        ]);
    }

    public function cerrarSesionAndGetRedirect($redirectUrl)
    {
        return env('CU_URL_CIERRE_SESION', 'https://accounts.claveunica.gob.cl/api/v1/accounts/app/logout?redirect')."=".$redirectUrl;
    }
}
