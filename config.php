<?php
/**
 * Jamroom 5 ujExif module
 *
 * copyright 2013 by Ultrajam - All Rights Reserved
 * http://www.ultrajam.net
 *
 */

// make sure we are not being called directly
defined('APP_DIR') or exit();

/**
 * ujExif_config
 */
function ujExif_config()
{
    // Save Serialized Exif Data - really need to specify modules 
    $_tmp = array(
        'name'     => 'save_exif',
        'default'  => 'off',
        'type'     => 'checkbox',
        'validate' => 'onoff',
        'required' => 'on', 
        'label'    => 'save exif',
        'help'     => 'Enabling this option will serialize and save the exif array to the datastore.',
        'section'  => 'general settings',
        'order'    => 1
    );
    jrCore_register_setting('ujExif',$_tmp);

    // Modules to save exif for 
    $_tmp = array(
        'name'     => 'save_modules',
        'type'     => 'text',
        'default'  => '',
        'validate' => 'printable',
        'label'    => 'Image Modules',
        'help'     => 'Add a comma separated list of the module names for which to extract exif data and store serialized array.',
        'section'  => 'general settings',
        'order'    => 2
    );
    jrCore_register_setting('ujExif',$_tmp);

//------------------------------
// Bootstrap Docs
//------------------------------
    // Bootstrap version
    $_versions = ujBootstrap_get_versions();
    $_tmp = array(
        'name'     => 'bootstrap_version',
        'label'    => 'bootstrap version',
        'type'     => 'select',
        'options'  => $_versions,
        'default'  => '3.0.0',
        'help'     => 'Select the bootstrap version to use as a base.'
    );
    jrCore_register_setting('ujExif',$_tmp);
    return true;
}

