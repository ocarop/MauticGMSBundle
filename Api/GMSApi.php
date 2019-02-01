<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticGMSBundle\Api;

use Mautic\SmsBundle\Api\AbstractSmsApi;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\CoreBundle\Helper\PhoneNumberHelper;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Monolog\Logger;
use Joomla\Http\HttpFactory;
use Mautic\PluginBundle\Exception\ApiErrorException;

class GMSApi extends AbstractSmsApi {

    /**
     * @var \Services_Twilio
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $sendingPhoneNumber;
    protected $integration;
    protected $keys;

    /**
     * TwilioApi constructor.
     *
     * @param TrackableModel    $pageTrackableModel
     * @param PhoneNumberHelper $phoneNumberHelper
     * @param IntegrationHelper $integrationHelper
     * @param Logger            $logger
     */
    public function __construct(TrackableModel $pageTrackableModel, PhoneNumberHelper $phoneNumberHelper, IntegrationHelper $integrationHelper, Logger $logger) {

        $this->logger = $logger;
        //error_log('ejecutando constructor nueva version');
        $integration = $integrationHelper->getIntegrationObject('Twilio');
        $this->integration = $integration;

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $this->sendingPhoneNumber = $integration->getIntegrationSettings()->getFeatureSettings()['sending_phone_number'];

            $keys = $integration->getDecryptedApiKeys();
            $this->keys = $keys;
        }
        /*
            error_log('GMS username ' . $this->keys['username']);
            error_log('GMS password ' . $this->keys['password']);
        */    
        parent::__construct($pageTrackableModel);
    }

    /**
     * @param string $number
     * @param string $content
     *
     * @return bool|string
     */
    public function sendSms($number, $content) {
        if ($number === null) {
            return false;
        }

        try {
            $request_url = 'https://clientes.gms.es/webservice/servidor.php';
            /*
            error_log('GMS sendingPhoneNumber ' . $this->sendingPhoneNumber);
            error_log('GMS destino ' . $number);
            error_log('GMS texto ' . $content);
             */

            //mrkticSms 
            //77dOsB688gw
            $parameters = [
                'funcion' => 'envioSms',
                'login' => $this->keys['username'],
                'password' => md5($this->keys['password']),
                'remitente' => $this->sendingPhoneNumber,
                'texto' => $content,
                'destino' => $number
            ];

            $requestSettings = [
                'ssl_verifypeer' => 'false',
                'return_raw' => 'true' // needed to get the HTTP status code in the response
            ];

            $request = $this->integration->makeRequest($request_url, $parameters, 'GET', $requestSettings);

            
            //al hacer sendrequest return_raw, lo que viene es un objeto joomla httpsession
            $status = $request->code;
            $message = '';
              //}
           
            //error_log('respuesta GMS API code ' . $status);

            switch ($status) {
                case 200:
                    // request ok
                    $message = $request->body;
                    break;
                case 400:
                    $message = 'Bad request';
                    break;
                case 403:
                    $message = 'Token invalid';
                    break;
                case 404:
                    $message = 'The requested object does not exist';
                    break;
                case 500:
                    $message = 'Internal server error';
                    break;
                default:
                    $message = $request->body;
                    break;
            }

            //error_log('respuesta GMS API ' . $message);
            if (strpos($message, '50000') !== false){
                //es el codigo de correcto en la plataforma de GMS
                return true;
            } else {
                //throw new ApiErrorException($message);
                $this->logger->addWarning($message);
                return $message;                
            }
        } catch (Exception $e) {
            $this->logger->addWarning(
                    $e->getMessage(), ['exception' => $e]
            );
            return $e->getMessage();
        }
    }

}
