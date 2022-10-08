<?php

namespace Drupal\chuck_norris\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Chuck Norris module form.
 */
class ChuckNorrisForm extends FormBase {

  const RANDOM_JOKE_URL = 'https://matchilling-chuck-norris-jokes-v1.p.rapidapi.com/jokes/random';

  /**
   * @var ClientInterface
   */
  protected ClientInterface $http_client;

  /**
   * Creates a ChuckNorrisForm object.
   *
   * @param ClientInterface $client
   */
  public function __construct(ClientInterface $client) {
    $this->http_client = $client;
  }

  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chuck_norris_chuck_norris';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['label'] = [
      '#type' => 'markup',
      '#markup' => '<h4>Do you want to hear a Chuck Norris joke?</h4>',
    ];
    // @todo: Create custom css for buttons.
    $form['buttons']['yes'] = [
      '#type' => 'submit',
      '#value' => t('Yes I do!'),
      '#attributes' => [
        'class' => ['button'],
      ],
      '#ajax' => [
        'callback' => '::generateJoke',
      ],
      '#joke' => TRUE,
    ];
    $form['joke'] = [
      '#type' => 'markup',
      '#markup' => '<div class="joke"></div>'
    ];
    $form['buttons']['no'] = [
      '#type' => 'submit',
      '#value' => t('No...'),
      '#attributes' => [
        'class' => ['button'],
      ],
      '#ajax' => [
        'callback' => '::generateJoke',
      ],
      '#joke' => FALSE,
    ];
    $form['#attached']['library'][] = 'chuck_norris/chuck_norris';

    return $form;
  }

  /**
   * Return a Chuck Norris joke.
   *
   * @param $build
   * @param $form_state
   * @return AjaxResponse
   */
  public function generateJoke(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (isset($form_state->getTriggeringElement()['#joke'])) {
      if ($form_state->getTriggeringElement()['#joke']) {
        // Gotta get that joke from Chuck Norris API.
        $api_key = $this->config('chuck_norris.settings')->get('api_key');
        try {
          $res = $this->http_client->request('GET', self::RANDOM_JOKE_URL, [
            'headers' => [
              'X-RapidAPI-Key' => $api_key,
              'Accept' => 'application/json',
            ],
          ]);
          $res_decoded = json_decode($res->getBody()->getContents(), true);
        }
        catch (RequestException $exception) {
          \Drupal::logger('chuck_norris')->error($exception->getMessage());
        }
        $joke_text = $res_decoded['value'] ?? 'Are you sure you entered a valid API key in module settings?';
        $response->addCommand(new HtmlCommand(
          '.joke',
          '<div class="chuck_norris_joke">' . $joke_text . '</div>'
        ));
      }
      else {
        $response->addCommand(new HtmlCommand(
          '.joke',
          '<div class="chuck_norris_joke">Sorry that you feel this way.</div>'
        ));
      }
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('<front>');
  }

}
