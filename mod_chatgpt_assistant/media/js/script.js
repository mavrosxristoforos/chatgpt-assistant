/*------------------------------------------------------------------------
# mod_chatgpt_assistant - ChatGPT Assistant
# ------------------------------------------------------------------------
# author    Christopher Mavros - Mavrosxristoforos.com
# copyright Copyright (C) 2011 Mavrosxristoforos.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: https://mavrosxristoforos.com
# Technical Support:  Forum - https://mavrosxristoforos.com/support/forum
-------------------------------------------------------------------------*/

jQuery(document).ready(function(jQuery) {
  // Enable the chat button
  jQuery("#chatgpt_assistant_button").click(function() {
    jQuery("#chatgpt_assistant_interface").slideToggle();
    ca_startConversation();
  });

  // Send message on button click or Enter key press
  jQuery(".chatgpt_assistant_wrapper .input-container button").click(ca_sendMessage);
  jQuery(".chatgpt_assistant_wrapper .input-container input").keypress(function(e) {
    if (e.which === 13) {
      ca_sendMessage();
    }
  });

  function ca_startConversation() {
    if (jQuery("#chatgpt_assistant_interface .conversation-container .message").length == 0) {
      jQuery("#chatgpt_assistant_interface .conversation-container").append('<div class="message_wrapper"><div class="message pending-message"><div class="ca-dot-typing-wrapper"><div class="ca-dot-typing"></div></div></div></div>');
      setTimeout(function() {
        jQuery('#chatgpt_assistant_interface .conversation-container .pending-message').remove();
        jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message received-message system-message'>" + Joomla.getOptions("mod_chatgpt_assistant").initial_system_message + "</div></div>");
        var container = jQuery("#chatgpt_assistant_interface .conversation-container")[0];
        container.scrollTop = container.scrollHeight;
      }, 300);
    }
    var container = jQuery("#chatgpt_assistant_interface .conversation-container")[0];
    container.scrollTop = container.scrollHeight;
    jQuery("#ca_message").focus();
  }

  // Function to send the message to the server via Ajax
  function ca_sendMessage() {
    var message = jQuery("#ca_message").val();
    if (message.trim() !== "") {
      // Display the sanitized sent message in the conversation container
      var sentMessageText = jQuery("<div>").text(message).html();
      var sentMessageHTML = '<div class="message_wrapper"><div class="message sent-message">' + sentMessageText + '</div></div>';
      jQuery("#chatgpt_assistant_interface .conversation-container").append(sentMessageHTML);

      jQuery("#chatgpt_assistant_interface .conversation-container").append('<div class="message_wrapper"><div class="message pending-message"><div class="ca-dot-typing-wrapper"><div class="ca-dot-typing"></div></div></div></div>');

      // Scroll the container
      var container = jQuery("#chatgpt_assistant_interface .conversation-container")[0];
      container.scrollTop = container.scrollHeight;

      // Perform Ajax request to send the message to the server
      jQuery.ajax({
        url: Joomla.getOptions("system.paths").base+"/index.php?option=com_ajax&module=chatgpt_assistant&method=sendMessage&format=json",
        method: "POST",
        data: { message: message, module_id: jQuery('.chatgpt_assistant_wrapper').data('moduleId') },
        success: function(response) {
          if (response.hasOwnProperty('data')) {
            if (response.data.hasOwnProperty('error')) {
              console.error(response.data.error);
              jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message error-message system-message'>" + response.data.error + "</div></div>");
            }
            else if (response.data.hasOwnProperty('output')) {
              jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message received-message'>" + response.data.output + "</div></div>");
            }
            else {
              console.log(Joomla.JText._("MOD_CHATGPT_ASSISTANT_NO_OUTPUT_PROPERTY_IN_RESPONSE"));
              jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message error-message'>" + Joomla.JText._("MOD_CHATGPT_ASSISTANT_SOMETHING_WENT_WRONG") + "</div></div>");
            }

            // Add the warning without interfering with the rest of the functionality.
            if (response.data.hasOwnProperty('warning')) {
              jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message received-message system-message warning-message'>" + response.data.warning + "</div></div>");
            }
          }
          else {
            console.log(Joomla.JText._("MOD_CHATGPT_ASSISTANT_NO_DATA_PROPERTY_IN_RESPONSE"));
            jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message error-message'>" + Joomla.JText._("MOD_CHATGPT_ASSISTANT_SOMETHING_WENT_WRONG") + "</div></div>");
          }

          // Scroll the container
          var container = jQuery("#chatgpt_assistant_interface .conversation-container")[0];
          container.scrollTop = container.scrollHeight;
        },
        error: function(xhr, status, error) {
          // Handle any errors that occur during the Ajax request
          console.error(error);
          jQuery("#chatgpt_assistant_interface .conversation-container").append("<div class='message_wrapper'><div class='message error-message'>" + error + "</div></div>");

          // Scroll the container
          var container = jQuery("#chatgpt_assistant_interface .conversation-container")[0];
          container.scrollTop = container.scrollHeight;
        },
        complete: function() {
          jQuery('#chatgpt_assistant_interface .conversation-container .pending-message').remove();
        }
      });

      // Clear the input field after sending the message
      jQuery(".input-container input").val("");
    }
  }
});