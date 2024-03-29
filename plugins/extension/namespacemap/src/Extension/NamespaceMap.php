<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.namespacemap
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Extension\NamespaceMap\Extension;

use JNamespacePsr4Map;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! namespace map creator / updater.
 *
 * @since  4.0.0
 */
final class NamespaceMap extends CMSPlugin
{
    /**
     * The namespace map file creator
     *
     * @var JNamespacePsr4Map
     */
    private $fileCreator = null;

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $subject  The object to observe
     * @param   JNamespacePsr4Map    $map      The namespace map creator
     * @param   array                $config   An optional associative array of configuration settings.
     *                                         Recognized key values include 'name', 'group', 'params', 'language'
     *                                         (this list is not meant to be comprehensive).
     *
     * @since   4.0.0
     */
    public function __construct(DispatcherInterface $subject, JNamespacePsr4Map $map, $config = array())
    {
        $this->fileCreator = $map;

        parent::__construct($subject, $config);
    }

    /**
     * Update / Create map on extension install
     *
     * @param   Installer  $installer  Installer instance
     * @param   integer    $eid        Extension id
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterInstall($installer, $eid)
    {
        // Check that we have a valid extension
        if ($eid) {
            // Update / Create new map
            $this->fileCreator->create();
        }
    }

    /**
     * Update / Create map on extension uninstall
     *
     * @param   Installer  $installer  Installer instance
     * @param   integer    $eid        Extension id
     * @param   boolean    $removed    Installation result
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUninstall($installer, $eid, $removed)
    {
        // Check that we have a valid extension and that it has been removed
        if ($eid && $removed) {
            // Update / Create new map
            $this->fileCreator->create();
        }
    }

    /**
     * Update map on extension update
     *
     * @param   Installer  $installer  Installer instance
     * @param   integer    $eid        Extension id
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUpdate($installer, $eid)
    {
        // Check that we have a valid extension
        if ($eid) {
            // Update / Create new map
            $this->fileCreator->create();
        }
    }
}
