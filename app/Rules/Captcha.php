<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class Captcha implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(is_null($value))
            return false;

        try {
            $client = new Client([
                'base_uri' => 'https://recaptchaenterprise.googleapis.com/v1beta1/',
                'timeout'  => 10,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $response = $client->request('POST', 'projects/'.env('RECAPTCHA_ID_CUENTA').'/assessments?key='.env('RECAPTCHA_API_KEY'), [
                'json' => [
                    'event' => [
                        'siteKey' => env('RECAPTCHA_SITE_KEY'),
                        'token' => $value
                    ]
                ]
            ]);
            $result = $response->getBody();
            $reCaptchaResult = json_decode($result->getContents(), true);
            Log::info("### RECAPTCHA ###");
            Log::info($reCaptchaResult['tokenProperties']['hostname']);
            Log::info($reCaptchaResult['score']);
            Log::info($reCaptchaResult['tokenProperties']['valid']);
            Log::info("### FIN RECAPTCHA ###");
            if($reCaptchaResult['tokenProperties']['valid'] && $reCaptchaResult['score'] > env('RECAPTCHA_SCORE', 0.5)){
                $reCaptchaResult = true;
            }
            

        } catch (RequestException $e) {
            Log::info('[RECAPTCHA] Ha ocurrido en error al itentar comunicarse con google y validar el recaptcha.', []);
            $reCaptchaResult = false;
            Log::info($e->getMessage());
        }

        return $reCaptchaResult;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // return trans('validation.captcha');
        return 'Hubo un error en el captcha';
    }
}
