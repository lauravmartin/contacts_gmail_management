<?php

namespace Drupal\contacts_gmail_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'gmail contact invite' block.
 *
 * @Block(
 *   id = "Import Gmail Contact block",
 *   admin_label = @Translation("Import Gmail Contact "),
 * )
 */
class ImportGmailContactBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  public function build() {

    $google_api_settings = (object) \Drupal::config('google_api.settings');
    $source = $google_api_settings->get('source');
    $scope = '&scope=https://www.googleapis.com/auth/contacts.readonly&response_type=code';
    if($source == '1') {
      $scope = '&scope=https://www.googleapis.com/auth/contacts.other.readonly&response_type=code';
    }
    $gmail_contacts_url = $this->generateLink($google_api_settings,$scope);
    $url_contacts = Url::fromUri($gmail_contacts_url);
    $content = '';
    if (!$_SESSION['contacts']['imported']) {
      $img = '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/512px-Google_%22G%22_Logo.svg.png" height="15" width="15" />';
      $rendered_image = render($img);
      $image_markup = Markup::create($rendered_image);
      $content = '<div class=" button">' . $image_markup . ' '.
        Link::fromTextAndUrl(t('Get Gmail Contacts'), $url_contacts)->toString() .
        '</div>';
    }
    else {
        $_SESSION['contacts']['imported'] = FALSE;
      }
      return array(
        '#title'=>'',
        '#type' => 'markup',
        '#markup' => $content,
      );

  }

  /**
   * Construct the request url sent to Google.
   *
   * @param string $google_api_settings
   * Settings required by google authentication.
   *
   * @return string
   * Url string.
   */
  function generateLink($google_api_settings, $scope) {
    global $base_url;
    $url = 'https://accounts.google.com/o/oauth2/auth';
    $client_id = $google_api_settings->get('client_id');
    $redirect_url = $google_api_settings->get('redirect_url');
    $gmail_url = $url. '?client_id='.$client_id.'&redirect_uri=' . $base_url . $redirect_url . $scope;
    return $gmail_url;
  }
}

