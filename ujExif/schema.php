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
 * ujExif_db_schema 
 */
function ujExif_db_schema()
{
    // This module uses a Data Store - create it.  The Data store holds
    // all information (key value pairs) from the file exif data.
    jrCore_db_create_datastore('ujExif','exif');

    return true;
}
?>