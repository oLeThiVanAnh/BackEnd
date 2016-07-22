<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Get the custom data from the component
$query->select('custom_data')
      ->from('#__extensions')
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$custom_data = $db->loadResult();
$custom_data = ($custom_data == '') ? array() : json_decode($custom_data, true);

// Check data keys
if (!isset($custom_data['uninstall']['libraries'])) {
    $custom_data['uninstall']['libraries'] = array();
}

// Get the libraries
$libraries = $custom_data['uninstall']['libraries'];
$installer = new JInstaller();

// Uninstall libraries
foreach($libraries AS $lib_name)
{
    $query->clear();
    $query->select('extension_id')
          ->from('#__extensions')
          ->where('element = ' . $db->quote($lib_name))
          ->where('type = ' . $db->quote('library'));

    $db->setQuery((string) $query);
    $lib_id = (int) $db->loadResult();

    if ($lib_id) {
        $installer->uninstall('library', $lib_id);
    }
}
