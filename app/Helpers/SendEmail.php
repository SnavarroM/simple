<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;


/**
 * SendEmail
 * 
 * Clase para manejar el envío de corres mediante el uso del servicio externo de correos
*/
class SendEmail {

    /**
     * Service endpoint
     * 
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * Headers
     * 
     * @var array
     */
    protected $headers;

    /**
     * Email body, encoded with base64(string or html)
     * 
     * @var string
     */
    protected $body;

    /**
     * Service endpoint
     * 
     * @var string
     */
    protected $emailServiceUrl;

    /**
     * Email subject, encoded with base64(subject)
     * 
     * @var string
     */
    protected $subject;

    /**
     * Recipient mail
     * ! has default value
     * @var string
     */
    protected $emailFrom;

    /**
     * List of recipient emails
     *
     * @var array
     */
    protected $emailTo;

    /**
     * List of emails defined with (cc)
     * ? optional
     * @var array
     */
    protected $ccList;

    /**
     * List of emails defined with blind copy, (bcc)
     * ? optional
     * @var array
     */
    protected $bccList;

    /**
     * Email identifier, Ideally indicating the product and the action that triggers the email
     * ! has default value
     * @var string
     */
    protected $category;

    /**
     * Set maximum communication timeout with the mail service
     * ! has default value to 10 seconds
     * 
     * @var int
     */
    protected $timeout;

    /**
     * Files, attachments
     * Array of object json
     * 
     * @var array
     */
    protected $files;

    /**
     * Images, attachments
     * Array of object json
     * 
     * @var array
     */
    protected $images;

    
    public function __construct() {

        $this->emailFrom = env('SERVICIO_CORREO_FROM');
        $this->category = env('APP_MAIN_DOMAIN', 'localhost');
        $this->files = [];
        $this->emailTo = [];
        $this->timeout = 10;

        // set up client Guzzle for htpp requests
        $this->client = new Client();

        $this->emailServiceUrl = env('SERVICIO_CORREO_ENDPOINT');

        // set default headers
        $this->headers = [
            "Content-Type" => "application/json",
            "x-api-key" => env('SERVICIO_CORREO_API_KEY')
        ];
    }

    /**
     * Action that performs the request to the mail service
     * ! Returns true if no errors are detected during sending
     * ! Otherwise returns false
     * 
     * @return boolean
     */
    public function sendEmail() {

        $isSent = true;

        try {
            $data = [
                'headers' => $this->headers,
                "json" => $this->getBodyParams(),
                'timeout' => $this->timeout
            ];

            $response = $this->client->post($this->emailServiceUrl, $data );
            $stream = $response->getBody();
            $contents = json_decode($stream->getContents(), true);

            Log::debug('[SendEmail][API Result] => ', [
                'contents' => $contents
            ]);

            if (array_key_exists('Errors', $contents)) {
                if (count($contents['Errors']) > 0) {
                    $isSent = false;
                    Log::error('[SendEmail][Error Service Email]', [
                        'MessageId' => $contents['MessageId'],
                        'log' => $contents['Errors']
                    ]);
                }
            }

        } catch (RequestException $e) {
            $isSent = false;
            Log::error('[SendEmail][RequestException Service Email]', [
                'log' => $e->getMessage()
            ]);
        } catch (\Excepcion $e) {
            $isSent = false;
            Log::error('[SendEmail][Excepcion Unknown Service Email]', [
                'log' => $e->getMessage()
            ]);
        }
    }

    /**
     * Set the email body
     * This body represents the content of the email in plain text or html
     * ! The value of this attribute must be encoded
     * 
     * @return void
     */
    public function setBody($body) {
        // debe encodearse => json_encode
        $this->body = base64_encode($body);
    }

    /**
     * Set main data structure to request body
     * 
     * todo: check & add "files" on array returned
     * todo: check & add "images" on array returned
     * todo: check & add cc, bcc emails on array returned
     * 
     * @return void
     */
    private function getBodyParams() {
        return [
            "from" => $this->emailFrom,
            "to" => $this->emailTo,
            "body" => $this->body,
            "subject" => $this->subject,
            "category" => $this->category,
        ];
    }

    /**
     * Set the email subject
     * ! The value of this attribute must be encoded
     * 
     * @return void
     */
    public function setSubject($subject) {
        $this->subject = base64_encode($subject);
    }

    /**
     * Email identifier, ideally indicating the product and the action that triggers the email
     * 
     * @return void
     */
    public function setCategory($category) {
        $this->category = $category;
    }

     /**
     * Define the email of the sender of the message
     * Default: env('SERVICIO_CORREO_FROM')
     * 
     * @return void
     */
    public function setEmailFrom($emailFrom) {
        $this->emailFrom = $emailFrom;
    }

    /**
     * Define the email to whom the message was addressed
     * 
     * @return void
     */
    public function setEmailTo($emailTo) {
        $this->emailTo = $emailTo;
    }

    /**
     * Set a list of emails defined as copy
     * 
     * @return void
     */
    public function setCC($ccList) {
        $this->ccList = $ccList;
    }

    /**
     * Set a list of emails defined as blind copy
     * 
     * @return void
     */
    public function setBCC($bccList) {
        $this->bccList = $bccList;
    }

    /**
     * Set file list
     * todo: Not implemented yet
     * 
     * @return void
     */
    public function setFiles($files) {
        $this->files = $files;
    }

    /**
     * Add a single file to array $files
     * todo: Not implemented yet
     * 
     * @return void
     */
    public function addSingleFile($file) {
        $this->files = array_push($this->files, $file);
    }

    /**
     * Set images ?
     *  ! encontrar ejemplos de uso
     * todo: not used
     * 
     * @return void
     */
    public function setImages($images) {
        $this->images = $images;
    }

    /**
     * Set maximum time to wait for the request response
     * 
     * @return void
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    /**
     * Define the request headers
     * 
     * @return void
     */
    public function setHeaders($headers) {
        $default = $this->headers;
        $this->headers = array_merge($default, $headers);
    }
}