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
 * meta
 */
function ujExif_meta() {
    $_tmp = array(
        'name'        => 'Exif',
        'url'         => 'exif',
        'version'     => '0.7.0',
        'developer'   => 'Ultrajam, &copy;' . strftime('%Y'),
        'description' => 'Provides photo Exif data extraction features to Jamroom.',
        'support'     => 'http://www.jamroom.net/phpBB2',
        'category'    => 'plugins'
    );
    return $_tmp;
}

/**
 * init
 */
function ujExif_init() {

    jrCore_register_module_feature('jrCore','quota_support','ujExif','off');
    
    // Listen for the "save_media_file" event and add image exif data
    jrCore_register_event_listener('jrCore','save_media_file','ujExif_save_media_file_listener');
    // also going to need to listen for db_delete_item to delete the exif item along with the image.
    jrCore_register_event_listener('jrCore','db_delete_item','ujExif_db_delete_item_listener');
    
    // Send trigger for listening modules to use the exif data
    jrCore_register_event_trigger('ujExif','extract_exif_data','Allows modules to hook into the exif data extraction and grab the data for their own nefarious purposes.');

    return true;
}

/**
 * ujExif_save_media_file_listener
 * Listens for uploaded images and extracts exif data where possible
 * Provides a trigger for other modules to hook into in order to save the exif information they need in their desired format.
 * Optionally save the entire exif data as a serialized array for later use. Controlled in ujExif config.
 */
function ujExif_save_media_file_listener($_data,$_user,$_conf,$_args,$event)
{
    // See if we are getting an image file upload...
    if (isset($_conf['ujExif_save_exif']) && $_conf['ujExif_save_exif'] == 'on' && !empty($_conf['ujExif_save_modules'])) {
        $_modules = explode(',',$_conf['ujExif_save_modules']);
        if (!is_array($_modules)) {
            $_modules[] = $_conf['ujExif_save_modules'];
        }
        if (!in_array($_args['module'],$_modules)) {
            return $_data;
        }
    } else {
        return $_data;
    }

    $exif = exif_read_data($_args['saved_file']);
    if(!is_array($exif)) {
        // image has no exif data
        return $_data;
    } else {
        $_data['exif'] = $exif;
        // Now exif data is temporarily added, send out trigger for listening modules to use it 
        $_data = jrCore_trigger_event('ujExif','extract_exif_data',$_data,$_args);
        $exif = serialize($exif);
    }
    unset($_data['exif']);
    $_save = array(
        'exif_module'     => $_args['module'],
        'exif_file_name'  => $_args['file_name'],
        'exif_item_id'    => (int) $_args['unique_id'],
        'exif_saved_file' => $_args['saved_file'],
        'exif_data'       => $exif
    );
    $pfx = jrCore_db_get_prefix($_args['module']);
    $existing_exif_id = jrCore_db_get_item_key($_args['module'],$_args['unique_id'],$_args['file_name'].'_exif_id');
    // if it already has an exif_id then it is an update so update the ujExif data
    if (isset($existing_exif_id) && is_numeric($existing_exif_id)) {
    
    
    // ?????????????????? _created is not being updated - bug posted to jamroom forum
        $_cr = array('_created' => time()); 
        jrCore_db_update_item('ujExif',$existing_exif_id,$_save,$_cr);
//  jrCore_db_update_item('ujExif',$existing_exif_id,$x,$_cr);
    } else {
        // else it needs to be created
        
        // need to unset $_data['action_pending_linked_item_id'] for exif update/create ?????? reset after update done
        //$pending = $_data['action_pending_linked_item_id'];
        $aid = jrCore_db_create_item('ujExif',$_save);

        // add the ujExif _item_id to the _image fields
        $_data["{$_args['file_name']}_exif_id"] = $aid;
    }
    return $_data;
}



/**
 * Delete exif entries when DS items are deleted
 * @param $_data array Array of information from trigger
 * @param $_user array Current user
 * @param $_conf array Global Config
 * @param $_args array additional parameters passed in by trigger caller
 * @param $event string Triggered Event name
 * @return array
 */
function ujExif_db_delete_item_listener($_data,$_user,$_conf,$_args,$event)
{
    if ($_args['module'] == 'ujExif') {
        // We don't anything for this module 
        return $_data;
    }
    while (true) {
        // Do a thousand at a time - lower memory usage
        $_sp = array(
            "limit"  => 1000,
            "search" => array(
                "exif_module = {$_args['module']}",
                "exif_item_id = {$_args['_item_id']}"
            ),
            "exclude_jrProfile_keys" => true,
            "exclude_jrUser_keys"    => true,
            'return_keys'            => array('_item_id')
        );
        $_rt = jrCore_db_search_items('ujExif',$_sp);
        if (isset($_rt) && isset($_rt['_items']) && is_array($_rt['_items'])) {
            $_id = array();
            foreach ($_rt['_items'] as $_item) {
                $_id[] = (int) $_item['_item_id'];
            }
            // NOTE: Since exif entries have no media, we set the 3rd param to "false" - this
            // let's the delete function skip checking for associated item media.
            jrCore_db_delete_multiple_items('ujExif',$_id,false);
        }
        else {
            break;
        }
    }
    return $_data;
}

