<?php

/**
 * Handle form submissions.
 *
 * @since 1.1.3
 */

namespace ChadwickMarketing\SocialLite\base\forms;

use ChadwickMarketing\SocialLite\base\UtilsProvider;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;
use ChadwickMarketing\SocialLite\base\notifications\BioLinkNotifications;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

class BioLinkForms {

    use UtilsProvider;

    const OPTION_NAME_TOKEN = SOCIAL_LITE_OPT_PREFIX . '-token';

    /** Get form by id.
     *
     * @param string $id
     *
     * @return array
     */

    public function getForm($formId) {

        $form = current(array_filter(BioLinkData::instance()->getBioLinkData()['content'], function ($item) use ($formId) {
            return $item['id'] === $formId;
        }))['content']['EditContactForm'];

        if (empty($form)) {
            return false;
        }

        return $form;

    }

    /**
     * Get form fields by form id.
     *
     * @param int $formId
     *
     * @return array
     */
    public function getFormFields($formId) {

        return $this->getForm($formId)['fields'];

    }

    /**
     * Get form addressee by form id.
     *
     * @param int $formId
     */

    public function getFormAddressee($formId) {

        return $this->getForm($formId)['__private__sendTo'];

    }

    /**
     * Function to encode or decode a token.
     *
     * @param string $action encode or decode
     * @param string $time  timestamp
     *
     * @return string
     *
     */

    public function decodeEncodeToken($action, $time) {

        $output = false;
        $encodeMethod = 'AES-256-CBC';
        $token = get_option($this->OPTION_NAME_TOKEN);

        if (is_array($token) && count($token) === 3) {

              $secretKey = $token[0];
              $secretIv = $token[1];
              $secretOffset = $token[2];

              $key = hash('sha256', $secretKey);
              $iv = substr(hash('sha256', $secretIv), 0, 16);

              if ($action === 'encode') {

                $string = intval($time) + $secretOffset;
                $output = openssl_encrypt($string, $encodeMethod, $key, 0, $iv);
                $output = base64_encode($output);

              } else {

                $output = openssl_decrypt(base64_decode($time), $encodeMethod, $key, 0, $iv);
                $output = intval($output) - $secretOffset;

              }
         } else {

            $token = [ uniqid(mt_rand()), uniqid(mt_rand()), rand(999, time()) ];

            add_option( $this->OPTION_NAME_TOKEN, $token );

         }

        return $output;
      }

    /**
     * Get a token.
     *
     * @return string
     */

    public function getToken() {

        return $this->decodeEncodeToken('encode', time());

    }

    /**
     * Validate a token.
     *
     * @param string $token
     *
     * @return bool
    */

    public function validateToken($token) {

        $currentTime = time();

        $tokenTime = $this->decodeEncodeToken('decode', $token);

        return ($tokenTime <= $currentTime) && ($tokenTime > $currentTime - 5);

    }

    /**
     * Validate a form.
     *
     * @param array $form
     *
     * @return bool
     */
    public function validateFormFields($formId, $fields) {

        $formFields = $this->getFormFields($formId);

        foreach ($formFields as $fieldKey => $fieldValue) {

            if ($fieldValue['required'] && $fieldValue['visible'] && empty($fields[$fieldKey])) {

                return false;

            }

        }

        foreach ($fields as $fieldKey => $fieldValue) {

            if (!array_key_exists($fieldKey, $formFields)) {

                return false;

            } else {

                if ($fieldKey === 'email') {

                    if (!filter_var($fieldValue, FILTER_VALIDATE_EMAIL)) {

                        return false;

                    }

                }

            }

        }

        return true;

    }

    /**
     * Handle form submission.
     *
     * @param string $token
     * @param array $formData
     */
    public function handleFormSubmission($formData) {

        $formId = $formData['id'];
        $fields = $formData['fields'];
        $token = $formData['token'];

        if (!$this->validateToken($token) ) {

            return false;

        }

        if (!$this->validateFormFields($formId, $fields)) {

            return false;

        }

        BioLinkNotifications::instance()->sendMailNotification($this->getFormAddressee($formId), $fields);

        return true;

    }




    /**
     * New instance.
     */
    public static function instance() {
        return new BioLinkForms();
    }


}