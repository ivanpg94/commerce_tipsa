<?php

namespace Drupal\commerce_tipsa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;

class generatelabel extends FormBase
{
  public function getFormId()
  {
    return 'generate_label_tipsa';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Generar etiqueta Tipsa'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $requestUri = $request->getRequestUri();
    //$complete = (substr($requestUri, -20, -15));
    $order = explode ('orders/', $requestUri);

    $num = explode ('/', $order[1]);
    $order = $num[0];
//variables
    $form = \Drupal::formBuilder()->getForm('Drupal\commerce_tipsa\Form\CommerceTipsaForm');
    $api_url = ($form['api_url_settings']['api_url']['#value']);
    $api_agencia = ($form['api_url_settings']['api_agency']['#value']);
    $api_user = ($form['api_url_settings']['api_user']['#value']);
    $api_password = ($form['api_url_settings']['api_password']['#value']);


      $requestorder = \Drupal::service('request_stack')->getCurrentRequest();
      $requestUri = $requestorder->getRequestUri();
      $urlcut = explode("/generate_label", $requestUri);
      $urlcut2 = explode("orders/", $urlcut[0]);
      $orderid = intval($urlcut2[1]);

      $database = \Drupal::database();
      $query = $database->select('tipsa', 'm')
        ->condition('pedido', $orderid)
        ->fields('m');

      $data = $query->execute()->fetchAssoc();

      if(!isset($data['pedido'])){
        $check = 'nada';
      }else{
        $check = (int)$data['pedido'];
      }

      // $check = (int)$data['pedido'];

      if($check != $orderid) {
        $login = \Drupal::service('commerce_tipsa.iniciar_sesion')->login($api_url, $api_agencia, $api_user, $api_password);

        $login = explode("<v1:strSesion>", $login);
        $login = explode("</v1:strSesion>", $login[1]);
        $id_session = $login[0];

        //metodo graba envio
        $grabaenvio = \Drupal::service('commerce_tipsa.graba_Envio')->grabaEnvio($id_session, $api_url, $api_agencia, $api_user);

        $albaran = explode("<v1:strAlbaranOut>", $grabaenvio);
        $albaran = explode("</v1:strAlbaranOut>", $albaran[1]);
        $albaran = $albaran[0];

        $query = $database->insert('tipsa');
        $query->fields(['pedido', 'albaran']);
        $query->values([$orderid, $albaran]);
        $query->execute();

      }


    $login = \Drupal::service('commerce_tipsa.iniciar_sesion')->login($api_url, $api_agencia, $api_user, $api_password);
    $login = explode("<v1:strSesion>", $login);
    $login = explode("</v1:strSesion>", $login[1]);
    $id_session = $login[0];

    $database = \Drupal::database();
    $query = $database->select('tipsa', 'm')
      ->condition('pedido', $order)
      ->fields('m');
    $data = $query->execute()->fetchAssoc();
    $albaran = $data['albaran'];

    //mostrar albaran en la pagina del pedido

    $order = \Drupal::routeMatch()->getParameter('commerce_order');
    $order->field_numero_de_albaran->setValue($albaran);
    $id = $order->order_id->value;
    $numero_albaran = $order->field_numero_de_albaran->value;
    $query = $database->upsert('commerce_order__field_numero_de_albaran');
    $query->fields([
      'bundle',
      'deleted',
      'entity_id',
      'revision_id',
      'langcode',
      'delta',
      'field_numero_de_albaran_value',
    ]);
    $query->values([
      'physical',
      0,
      $id,
      $id,
      'und',
      0,
      $numero_albaran,
    ]);
    $query->key('entity_id');
    $query->execute();
    //fin mostrar albaran en pagina pedido

    //metodo consulta etiqueta
    $consetiqueta = \Drupal::service('commerce_tipsa.cons_Etiqueta')->consultaEtiqueta($id_session, $albaran, $api_url, $api_agencia);

    $pdf = explode("<v1:strEtiqueta>", $consetiqueta);
    $pdf = explode("</v1:strEtiqueta>", $pdf[1]);
    $pdf = strval($pdf[0]);
    $pdf_decode = base64_decode($pdf);

    //creacion del pdf
    /** @var \Drupal\Core\Extension\ExtensionList $extension_list */
    $extension_list = \Drupal::service('extension.list.module');
    $filepath = $extension_list->getPath('commerce_tipsa') . '/assets/invoice.pdf';

    $file = File::create([
      'filename' => basename($filepath),
      'uri' => 'public://invoices/' . basename($filepath),
      'status' => 1,
      'uid' => 1,
    ]);
    $file->save();
    \Drupal::entityTypeManager()->getStorage('commerce_order')->resetCache();

    $directory = 'public://invoices';
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $file_system->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $file_system->copy($filepath, $directory . '/' . basename($filepath), FileSystemInterface::EXISTS_REPLACE);

    /** @var \Drupal\file\FileUsage\DatabaseFileUsageBackend $file_usage */
    $file_usage = \Drupal::service('file.usage');
    $file_usage->add($file, 'commerce_tipsa', 'node', 1);

    $data = $pdf_decode;
    /** @var \Drupal\file\FileRepositoryInterface $fileRepository */
    $fileRepository = \Drupal::service('file.repository');
    $fileRepository->writeData($data, "public://invoices/invoice.pdf", FileSystemInterface::EXISTS_REPLACE);



//redirigir a la etiqueta

    $invoice = '/sites/default/files/invoices/invoice.pdf';
    $url = Url::fromUserInput($invoice);

    $form_state->setRedirectUrl($url);


  }
}
