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

require_once dirname(__FILE__) . '/helper.php';
$helper = new ModChatgptAssistantHelper();

require JModuleHelper::getLayoutPath('mod_chatgpt_assistant');