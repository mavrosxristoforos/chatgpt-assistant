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
defined('JPATH_BASE') or die;

jimport('joomla.form.helper');
jimport('joomla.form.formfield');
jimport('joomla.html.html');

/**
 * Shows a single element with HTML written in the field's hint attribute.
 *
 * @since  1.6
 */
class JFormFieldCustomLabel extends JFormField
{
  /**
   * The form field type.
   *
   * @var        string
   * @since    1.6
   */
  protected $type = 'customlabel';

  public function getInput() {
    return JText::_($this->hint);
  }
}
