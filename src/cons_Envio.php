<?php

namespace Drupal\commerce_tipsa;

use Drupal\Core\Session\AccountInterface;

class cons_Envio
{
  protected $currentUser;

  /**
   * CustomService constructor.
   * @param AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  function consultaEnvio($id_session, $api_agencia, $albaran)
  {
    $form = \Drupal::formBuilder()->getForm('Drupal\commerce_tipsa\Form\CommerceTipsaForm');
    $api_url_action = ($form['api_url_settings']['api_url_action']['#value']);

    $xml = '<?xml version="1.0" encoding="utf-8"?>
          <soap:Envelope
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema">
            <soap:Header>
              <ROClientIDHeader xmlns="http://tempuri.org/">
                <ID>'.$id_session.'</ID>
              </ROClientIDHeader>
            </soap:Header>
            <soap:Body>
              <WebServService___ConsEnvio>
                <strCodAgeCargo>'.$api_agencia.'</strCodAgeCargo>
                <strCodAgeOri>'.$api_agencia.'</strCodAgeOri>
                <strAlbaran>'.$albaran.'</strAlbaran>
              </WebServService___ConsEnvio>
            </soap:Body>
          </soap:Envelope>';


    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $api_url_action,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $xml,
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

  //  print '<section>';
  //  print_r($response);
  //  print '</section>';

    return $response;


  }
}
