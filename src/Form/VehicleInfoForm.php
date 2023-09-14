<?php

namespace Drupal\vehicle_info\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for vehicle info.
 */
class VehicleInfoForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vehicle_info_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['year'] = [
      '#type' => 'select',
      '#title' => $this->t('Year'),
      '#options' => $this->getYearOptions(),
      '#empty_option' => $this->t('- Select Year -'),
      '#ajax' => [
        'callback' => [$this, 'updateMakes'],
        'event' => 'change',
        'wrapper' => 'make-field-wrapper',
      ],
    ];
    $form['makes'] = [
      '#type' => 'select',
      '#title' => $this->t('Makes'),
      '#options' => !empty($form_state->getValue('year')) ? $this->getMakes($form_state->getValue('year')) : [],
      '#prefix' => '<div id="make-field-wrapper">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => [$this, 'updatemodels'],
        'event' => 'change',
        'wrapper' => 'model-field-wrapper',
      ],
    ];
    $selectedValue = $form_state->getValue('makes');
    $options = $form['makes']['#options'];
    $selectedLabel = '';
    if (isset($options[$selectedValue])) {
      $selectedLabel = $options[$selectedValue];
    }
    $data = [
      'year' => $form_state->getValue('year'),
      'makes' => $form_state->getValue('makes'),
      'label' => $selectedLabel,
    ];
    $form['model'] = [
      '#type' => 'select',
      '#title' => $this->t('Model'),
      '#options' => !empty($form_state->getValue('makes')) ? $this->getModels($data) : [],
      '#prefix' => '<div id="model-field-wrapper">',
      '#suffix' => '</div>',
    ];
    return $form;
  }

  /**
   * Function to update makes.
   */
  public function updateMakes(array $form, FormStateInterface $form_state) {
    return $form['makes'];
  }

  /**
   * Function to update models.
   */
  public function updateModels(array $form, FormStateInterface $form_state) {
    return $form['model'];
  }

  /**
   * Function to get year options.
   */
  private function getYearOptions() {
    // Replace this with your logic to generate year options (e.g., 1995-2023).
    $years = range(1995, 2023);
    $options = array_combine($years, $years);
    // Convert the years to strings to avoid type mismatch.
    $options = array_map('strval', $options);
    return $options;
  }

  /**
   * Function to get makes.
   */
  public function getMakes($year) {

    $client = \Drupal::httpClient();
    $request = $client->get('https://www.autozone.com/ecomm/b2c/v1/ymme/makes/' . $year);
    if ($request->getStatusCode() == 200) {
      $data = json_decode($request->getBody(), TRUE);
      // Extract the "makes" values from the API
      // response and return them as options.
      $makes = [];
      foreach ($data as $make) {
        $makes[$make['makeId']] = $make['make'];
      }
      return $makes;
    }
  }

  /**
   * Function to get models.
   */
  public function getModels($make) {

    $client = \Drupal::httpClient();
    $request = $client->get('https://www.autozone.com/ecomm/b2c/v1/ymme/models/' . $make['year'] . '/' . $make['label'] . '/' . $make['makes']);

    if ($request->getStatusCode() == 200) {
      $data = json_decode($request->getBody(), TRUE);
      // Extract the "makes" values from the API
      // response and return them as options.
      $models = [];
      foreach ($data as $model) {
        $models[$model['modelId']] = $model['model'];
      }
      return $models;

    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // submit.
  }

}
