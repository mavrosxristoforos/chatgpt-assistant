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

// Include the Joomla JHTMLHelper class
JLoader::register('JHtml', JPATH_LIBRARIES . '/cms/html/html.php');

// Load jQuery and module's JavaScript file
JHtml::_('jquery.framework');
JHtml::script('media/mod_chatgpt_assistant/js/script.min.js');

$document = JFactory::getDocument();
if ($params->get('load_css', '1')) {
  // Add CSS styles for the chat button and interface
  JHtml::stylesheet('media/mod_chatgpt_assistant/css/style.min.css');
}

if ($params->get('include_fontawesome', '1')) {
  $document->addStyleSheet($params->get('fontawesome_cdn', ''), array(), array("integrity"=>"sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg", "crossorigin"=>"anonymous"));
}
if ($params->get('custom_css', '') != '') {
  $document->addStyleDeclaration($params->get('custom_css', ''));
}

foreach(['MOD_CHATGPT_ASSISTANT_NO_DATA_PROPERTY_IN_RESPONSE', 'MOD_CHATGPT_ASSISTANT_SOMETHING_WENT_WRONG', 'MOD_CHATGPT_ASSISTANT_NO_OUTPUT_PROPERTY_IN_RESPONSE'] as $ls) {
  JText::script($ls);
}

$session = JFactory::getSession();
$messages = $session->get('chatgpt_assistant_messages', array(['role' => 'system', 'content' => $params->get('initial_model_instruction', 'You are a helpful assistant.')]));

$document->addScriptOptions('mod_chatgpt_assistant', array('initial_system_message'=>$params->get('initial_system_message', 'Hello and welcome to our chat! How may I help you today?')));

?>

<div class="chatgpt_assistant_wrapper" data-module-id="<?php print $module->id; ?>">
    <div id="chatgpt_assistant_button" class="chat-button ca-button-pos-<?php print $params->get('button_pos', 'floating-right'); ?>">
        <i class="button-icon <?php print $params->get('button_icon', 'fas fa-comments'); ?>" title="<?php print JText::_('MOD_CHATGPT_ASSISTANT_BUTTON_LABEL'); ?>"></i>
    </div>

    <div id="chatgpt_assistant_interface" class="chat-interface" style="display: none;">
        <div class="chatgpt_assistant_interface_wrapper">
            <div class="conversation-container">
                <?php
                if (count($messages) > 0) {
                  // Add the initial system message
                  print '<div class="message_wrapper"><div class="message received-message system-message">'.$params->get('initial_system_message', 'Hello and welcome to our chat! How may I help you today?').'</div></div>';
                  foreach($messages as $message) {
                    if ($message['role'] == 'user') {
                      print '<div class="message_wrapper"><div class="message sent-message">'.$message['content'].'</div></div>';
                    }
                    else if ($message['role'] == 'assistant') {
                      print '<div class="message_wrapper"><div class="message received-message">'.sprintf($params->get('assistant_response_format', '%s'), $message['content']).'</div></div>';
                    }
                    // Ignore system messages
                  }
                } ?>
            </div>
            <div class="input-container input-group">
                <input type="text" id="ca_message" class="form-control" placeholder="<?php print JText::_('MOD_CHATGPT_ASSISTANT_PLACEHOLDER'); ?>" autofocus/>
                <button class="btn btn-primary"><?php print JText::_('MOD_CHATGPT_ASSISTANT_SEND_BUTTON'); ?></button>
            </div>
          </div>
    </div>
</div>