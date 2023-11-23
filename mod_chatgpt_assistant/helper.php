<?php
/*------------------------------------------------------------------------
# mod_chatgpt_assistant - ChatGPT Assistant
# ------------------------------------------------------------------------
# author    Christopher Mavros - Mavrosxristoforos.com
# copyright Copyright (C) 2011 Mavrosxristoforos.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: https://mavrosxristoforos.com
# Technical Support:  Forum - https://mavrosxristoforos.com/support/forum
-------------------------------------------------------------------------*/
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Response\JsonResponse;
use \Joomla\CMS\Language\Text;
use \Joomla\Registry\Registry;

class ModChatgptAssistantHelper {

  public static function sendMessageAjax() {
    // Get the input data from the Ajax request
    $input = Factory::getApplication()->input;
    $message = $input->get('message', '', 'string');

    $lang = Factory::getApplication()->getLanguage();
    $lang->load('mod_chatgpt_assistant', JPATH_BASE);

    $moduleId = $input->get('module_id', -1, 'int');
    if ($moduleId > 0) {
      $db = Factory::getDBO();
      $paramstring = $db->setQuery('SELECT `params` FROM `#__modules` WHERE `id` = "'.$db->escape($moduleId).'"')->loadResult();
      $mod_params = ($paramstring) ? new Registry($paramstring) : new Registry();
    }
    else {
      print json_encode(array('error'=>Text::_('MOD_CHATGPT_ASSISTANT_MODULE_NOT_FOUND')));
      return;
    }

    // Your OpenAI API credentials
    $openaiApiKey = $mod_params->get('openai_api_key', '');

    $session = Factory::getSession();
    $messages = $session->get('chatgpt_assistant_messages', array(['role' => 'system', 'content' => $mod_params->get('initial_model_instruction', 'You are a helpful assistant.')]));

    $messages[] = ['role' => 'user', 'content' => sprintf($mod_params->get('user_question_format', '%s'), $message)];
    $session->set('chatgpt_assistant_messages', $messages);

    // Initialize response data
    $responseData = ['output'=>''];

    $chat_limit = $mod_params->get('chat_limit', 10);
    if ($chat_limit > 0) {
      // Count user messages
      $responseData['message_count'] = 0;
      foreach($messages as $message) {
        if ($message['role'] == 'user') {
          $responseData['message_count']++;
        }
      }

      if ($responseData['message_count'] == $chat_limit - 1) {
        $responseData['warning'] = $mod_params->get('limit_reach_warning', 'System: We appreciate your questions. However, we would like to inform you that you have 1 more message to reach the maximum limit of messages to our automated assistant. If you need further assisatnce, please feel free to use our contact form. We are more than happy to help you there.');
        $messages[] = ['role'=>'system', 'content'=>$mod_params->get('limit_reach_instruction', 'This is your penultimate response. Kindly ask the user to use the contact form for further assistance.')];
        $session->set('chatgpt_assistant_messages', $messages);
      }
      else if ($responseData['message_count'] > $chat_limit) { // Not equal, so that we can allow that one last message that we mentioned in the warning.
        $responseData['error'] = $mod_params->get('limit_reach_error', 'System: Thank you for your questions! However, you have reached the maximum limit of messages to our automated assistant. If you need further assisatnce, please feel free to use our contact form. We are more than happy to help you there.');

        // Remove the last message from the array.
        array_pop($messages);
        $session->set('chatgpt_assistant_messages', $messages);
      }
    }

    // If there's no error, send the data over.
    if (!isset($responseData['error'])) {

      // Create the request payload for the OpenAI API
      $requestPayload = [
          'model' => $mod_params->get('chat_model', 'gpt-3.5-turbo'),
          'messages' => $messages,
          'max_tokens' => (int) $mod_params->get('max_tokens', 50),
          'temperature' => (float) $mod_params->get('temperature', 0.7),
          'n' => 1, // Adjust the number of responses to generate
          'stop' => ['\n'] // Specify the stopping condition for the response generation
      ];

      // Make a POST request to the OpenAI API
      $curl = curl_init();

      curl_setopt_array($curl, [
          CURLOPT_URL => $mod_params->get('openai_api_endpoint', 'https://api.openai.com/v1/chat/completions'),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => $mod_params->get('curl_opt_timeout', 120),
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($requestPayload),
          CURLOPT_HTTPHEADER => [
              'Authorization: Bearer ' . $openaiApiKey,
              'Content-Type: application/json'
          ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      // Handle the OpenAI API response
      if ($err) {
          // Error occurred during the API request
          $responseData['error'] = sprintf(Text::_('MOD_CHATGPT_ASSISTANT_API_ERROR_OCCURRED'), $err);
      } else {
          // Decode the API response
          $apiResponse = json_decode($response, true);
          $responseData['apiResponse'] = $apiResponse;

          if (isset($apiResponse['error']['message'])) {
            $responseData['error'] = sprintf(Text::_('MOD_CHATGPT_ASSISTANT_API_ERROR_OCCURRED'), $apiResponse['error']['message']);
          } else if (isset($apiResponse['choices'][0]['message'])) {
              $messages[] = $apiResponse['choices'][0]['message'];
              $session->set('chatgpt_assistant_messages', $messages);
              $responseData['output'] = sprintf($mod_params->get('assistant_response_format', '%s'), $apiResponse['choices'][0]['message']['content']);
          } else {
              $responseData['error'] = Text::_('MOD_CHATGPT_ASSISTANT_UNABLE_TO_GET_VALID_RESPONSE');
          }
      }
    }

    if ((isset($responseData['error'])) && ($mod_params->get('show_debug', 0))) {
      $responseData['requestPayload'] = $requestPayload;
      $responseData['curlResponse'] = $response;
      $responseData['fullError'] = $err;
    }

    return $responseData;
  }

}