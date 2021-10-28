<?php

/**
 * @file
 * Contains \Drupal\contacts_gmail_management\Controller.
 */

namespace Drupal\contacts_gmail_management\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;


/**
 * Class ImportGmailContactController.
 *
 * @package Drupal\contacts_gmail_management\Controller
 */
class ImportGmailContactController extends ControllerBase {
  public function getOAuthCode() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $content = [];
    if (isset($_GET["code"])) {
      $google_api_settings = (object) \Drupal::config('google_api.settings');
      $_SESSION['gmail_auth_code'] = $_GET['code'];
      $response = [];
      $accessToken = $this->getAccessToken($_GET['code'], $google_api_settings);
      if($accessToken == '') {
         \Drupal::messenger()->addMessage($this->t('Error trying to authenticate with google api, please check your connection'), 'error');
      }
      else {
        try {
          $response = $this->getContacts($accessToken, $google_api_settings);
          $content = $response;
          $header = [
            t('Image'),
            t('Name'),
            t('Email'),
          ];
          return [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $content,
          ];
        }
        catch (\Exception $e){
          \Drupal::messenger()->addMessage($this->t('Error ocurred'), 'error');
        }
      }
    }
    return [
      '#type' => 'markup',
      '#markup' => '',
    ];
  }

  /**
   * Get Access Token
   *   Method to get Google api access token.
   * @param $oauthCode
   *  Code returned from Google OAUTH.
   * @param $gapi_settings
   *  Google Api Module Setting.
   * @return string
   *  Return generated accessToken string.
   */
  public function getAccessToken($oauthCode, $gapi_settings) {
    global $base_url;
    $accessTokenUrl = 'https://accounts.google.com/o/oauth2/token';
    $params = [
      'code' => $oauthCode,
      'client_id' => $gapi_settings->get('client_id'),
      'client_secret' => $gapi_settings->get('client_secret'),
      'redirect_uri' => $base_url . $gapi_settings->get('redirect_url'),
      'grant_type' => 'authorization_code',
    ];
    $client = new Client();
    try {
      $accessTokenResponse = $client->post($accessTokenUrl, ['form_params' => $params]);
      $accessToken = json_decode($accessTokenResponse->getBody()->getContents(),true)['access_token'];
    }
    catch (\Exception $e){
      return $accessToken = '';
    }

    return $accessToken;
  }

  /**
   * getContacts
   *  Method to get address book contacts or other according to settings.
   *
   * @param $accessToken
   *  Generated accessToken string.
   * @param $google_api_settings
   *   Google Api Module Setting.
   *
   * @return array
   *   Return contacts array.
   */
  public function getContacts($accessToken, $google_api_settings) {
    $source = $google_api_settings->get('source');
    $contacts = [];
    if($source == '0') {
      $peopleApiUrl = 'https://people.googleapis.com/v1/people/me/connections?personFields=coverPhotos%2Cphotos%2Cnames%2CemailAddresses';
      $contacts = $this->getContactsResponse($accessToken, $peopleApiUrl);
    }
    else {
      $peopleApiUrl = 'https://people.googleapis.com/v1/otherContacts?readMask=photos%2Cnames%2CemailAddresses&sources=READ_SOURCE_TYPE_CONTACT&sources=READ_SOURCE_TYPE_PROFILE';
      $contacts = $this->getOtherContactsResponse($accessToken,$peopleApiUrl);
    }
    $_SESSION['contacts']['imported'] = TRUE;
    return $contacts;
  }

  /**
   * getContactsResponse
   *  Method to get Gmail address book contacts.
   *
   * @param $access_token
   *  Generated accessToken string.
   * @param $peopleApiUrl
   *  Url to request
   *
   * @return array
   *   Return contacts array.
   */
  public function getContactsResponse($access_token,$peopleApiUrl){
    $contacts = [];
    $client = new Client();
    $data = $client->get($peopleApiUrl,[
      'headers' => [
        'Accept' => 'application/json,media',
        'Authorization' => 'Bearer ' . $access_token ,
      ],]);
    $result = json_decode((string)$data->getBody());
    foreach ($result->connections as $item) {
        if (isset($item->emailAddresses[0]->value) && filter_var($item->emailAddresses[0]->value, FILTER_VALIDATE_EMAIL)) {
        $object = [
          'photo' => new FormattableMarkup('<img width="36" height="36"  src="@image" style="border-radius: 50%"/>', ['@image' => $item->photos[0]->url]),
          'name' => isset($item->names[0]->displayName) ? $item->names[0]->displayName : $item->emailAddresses[0]->value,
          'email' => $item->emailAddresses[0]->value,
        ];
        array_push($contacts, $object);
      }
    }
    return $contacts;
  }

  /**
   * getOtherContactsResponse
   *  Get Gmail Other contacts.
   *
   * @param $access_token
   *  Generated accessToken string.
   * @param $peopleApiUrl
   *  Url to request.
   *
   * @return array
   */
  public function getOtherContactsResponse($access_token,$peopleApiUrl) {
    $contacts = [];
    $client = new Client();
    $data = $client->get($peopleApiUrl,[
      'headers' => [
        'Accept' => 'application/json,media',
        'Authorization' => 'Bearer ' . $access_token ,
      ],]);
    $result = json_decode((string)$data->getBody());
    foreach ($result->otherContacts as $item) {
        if (isset($item->emailAddresses[0]->value) && filter_var($item->emailAddresses[0]->value, FILTER_VALIDATE_EMAIL)) {
        $object = [
          'photo' => new FormattableMarkup('<img width="36" height="36"  src="@image" style="border-radius: 50%"/>', ['@image' => $item->photos[0]->url]),
          'name' => (isset($item->names[0]->displayName) || $item->names[0]->displayName != '') ? $item->names[0]->displayName : $item->emailAddresses[0]->value,
          'email' => $item->emailAddresses[0]->value,
        ];
        array_push($contacts, $object);
      }
    }
    return $contacts;
  }
}


