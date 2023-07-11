<?php

namespace Drupal\commerce_tipsa;

use Drupal\Core\Session\AccountInterface;

class graba_Envio
{
  protected $currentUser;

  /**
   * CustomService constructor.
   * @param AccountInterface $currentUser
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  function grabaEnvio($id_session, $api_url, $api_agencia, $api_user)
  {
    $form = \Drupal::formBuilder()->getForm('Drupal\commerce_tipsa\Form\CommerceTipsaForm');
    $api_url_action = ($form['api_url_settings']['api_url_action']['#value']);

    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $requestUri = $request->getRequestUri();
    $urlcut = explode("/generate_label", $requestUri);
    $urlcut2 = explode("orders/", $urlcut[0]);
    $orderid = $urlcut2[1];
    $price = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($orderid)->getTotalPrice()->getNumber();
    $price = round($price, 2);

    $email = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($orderid)->getEmail();

    $entityManager = \Drupal::entityTypeManager();

    //customer data
    $order = $entityManager->getStorage('commerce_order')->load($orderid);
    $country_code = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['country_code'];
    $city = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['administrative_area'];
    $locality = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['locality'];
    $postal_code = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['postal_code'];
    $address_line1 = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['address_line1'];
    $address_line2 = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['address_line2'];

    if($country_code == 'PT'){
      $cp = explode("-", $postal_code);
      $postal_code = '6'.$cp[0];
    }
    if(!isset($address_line2)){
      $address_line2='';
    }
    $name = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['given_name'];
    $surname = $order->shipments->entity->shipping_profile->entity->address->getValue()[0]['family_name'];

    //shop data
    $store = $entityManager->getStorage('commerce_store')->load(2);
    $shop_country_code = $store->address->getValue()[0]['country_code'];
    $shop_administrative_area = $store->address->getValue()[0]['administrative_area'];
    $shop_locality = $store->address->getValue()[0]['locality'];
    $shop_postal_code = $store->address->getValue()[0]['postal_code'];
    $shop_address_line1 = $store->address->getValue()[0]['address_line1'];
    $shop_address_line2 = $store->address->getValue()[0]['address_line2'];
    $shop_name = $store->name->getValue()[0]['value'];

    if(!isset($shop_address_line2)){
      $shop_address_line2='';;
    }
    //date
    $date = date('Y-m-d');
    $hour = date('H:i:s');

    //tipo de servicio
    $tipo_servicio = '49';
    if($country_code = 'PT'){
      $tipo_servicio = '14';
    }
    //reembolso
    $reembolsovalor = $order->payment_gateway->entity->get('plugin');
    $reembolso = 0;
    if($reembolsovalor== 'manual') {
      $reembolso = round($price, 2);
    }

    //observaciones
    $observaciones = '';

    //persona contacto
    $personacontacto = '';


    $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
   <soapenv:Header>
      <tem:ROClientIDHeader>
         <!--Optional:-->
         <tem:ID>'.$id_session.'</tem:ID>
      </tem:ROClientIDHeader>
   </soapenv:Header>
   <soapenv:Body>
      <tem:WebServService___GrabaEnvio18>
	<strCodAgeCargo>'. $api_agencia .'</strCodAgeCargo>
	<strCodAgeOri>'. $api_agencia .'</strCodAgeOri>
	<dtFecha>'.$date.'T'.$hour.'.9687036+01:00</dtFecha>
         <strCodAgeDes />
	<strCodTipoServ>'.$tipo_servicio.'</strCodTipoServ>
	<strCodCli/>
	<strCodCliDep/>
	<strNomOri>'. $shop_name .'</strNomOri>
	<strTipoViaOri/>
	<strDirOri>'. $shop_address_line1 .'</strDirOri>
	<strNumOri>'.$shop_address_line2.'</strNumOri>
	<strPisoOri/>
	<strPobOri>'.$shop_country_code.'</strPobOri>
	<strCPOri>'. $shop_postal_code .'</strCPOri>
	              <strCodProOri>'.$shop_locality.'</strCodProOri>
	<strTlfOri/>
         <strNomDes>'.$name.' '.$surname.'</strNomDes>
         <strTipoViaDes />
         <strDirDes>'. $address_line1 .' '.$address_line2.'</strDirDes>
         <strPisoDes />
         <strPobDes>'.$city.'</strPobDes>
         <strCPDes>'.$postal_code.'</strCPDes>
         <strCodProDes>'.$country_code.'</strCodProDes>
         <strTlfDes />
         <intDoc>1</intDoc>
         <intPaq>1</intPaq>
         <dPesoOri>7</dPesoOri>
         <dAltoOri>9</dAltoOri>
         <dAnchoOri>5</dAnchoOri>
         <dLargoOri>9</dLargoOri>
         <dReembolso>'.$reembolso.'</dReembolso>
         <dValor>0</dValor>
	<dAnticipo>0</dAnticipo>
	<dCobCli>0</dCobCli>
	<strObs>'.$observaciones.'</strObs>
	<boSabado>true</boSabado>
	<boRetorno>false</boRetorno>
	<boGestOri>false</boGestOri>
	<boGestDes>false</boGestDes>
	<boAnulado>false</boAnulado>
	<boAcuse>false</boAcuse>
	<strRef/>
	<strCodSalRuta/>
	<dBaseImp>0</dBaseImp>
	<dImpuesto>0</dImpuesto>
	<boPorteDebCli>false</boPorteDebCli>
         <strPersContacto>'.$personacontacto.'</strPersContacto>
  <boDesSMS>false</boDesSMS>
	<boDesEmail>false</boDesEmail>
	<strDesMoviles/>
	<strDesDirEmails>'. $email .'</strDesDirEmails>
	<strCodPais>'. $country_code .'</strCodPais>
	<dValorFactInt>0</dValorFactInt>
	<boInsert>true</boInsert>
	<strAlbaran/>
	<strConcAgru/>
	<strCargosAduaneros/>
	<strCodDirAgrupDes/>
	<boSeguroFranquicia>false</boSeguroFranquicia>
	<strContenido>type1</strContenido>
	<strCodPuntoRec/>
	<strCodDevolucion/>
	<strRefAgrupaFact/>
      </tem:WebServService___GrabaEnvio18>
   </soapenv:Body>
</soapenv:Envelope>';

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

//    print '<article>';
//    print_r($response);
//    print '</article>';

    return $response;
  }
}
