<?php

namespace Drupal\commerce_tipsa;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\RequestStack;


class iniciar_sesion extends ControllerBase
{
  protected $currentUser;

  /**
   * CustomService constructor.
   * @param AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  public function login($api_url, $api_agencia, $api_user, $api_password)
  {
    $data = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema">
            <soap:Body>
            <LoginWSService___LoginCli2>
            <strCodAge>'.$api_agencia.'</strCodAge>
            <strCod>'.$api_user.'</strCod>
            <strPass>'.$api_password.'</strPass>
            <strIdioma>'. 'es' .'</strIdioma>
            </LoginWSService___LoginCli2>
            </soap:Body>
            </soap:Envelope>';

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $api_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/xml',
        'Accept' => 'application/xml',
      ),
    ));

    $response = curl_exec($curl);

    // Valida si se ha producido errores y muestra el mensaje de error
    if($errno = curl_errno($curl)) {
      $error_message = curl_strerror($errno);
      echo "cURL error ({$errno}):\n {$error_message}";
    }

    curl_close($curl);

//    print '<pre>';
 //   print_r($response);
//    print '</pre>';

    return $response;
  }
}
