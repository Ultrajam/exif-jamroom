Exif Data Extraction for Jamroom 5
==================================

v0.8.0-beta tested on Jamroom 5 Beta 9

ujExif is a Jamroom 5 module which can extract exif data from uploaded photos, and optionally store it for other modules to use.

It triggers a listener so your modules can grab and use the exif data during image upload/save.

You can optionally select to save the full extracted exif data to the ujExif database table for specified modules.
An exif_id is saved as an _image_exif field with the jrImage data so a large array doesn't encumber the image, yet can be retrieved whenever it is needed by other modules now or in the future. Note: You almost certainly do not want to do this, and this should be considered a deprecated feature).
##Example - ujGoogleMaps grabs lat/lng gps data

ujGoogleMaps listens for exif data attached to photos uploaded to jrGallery.<br>
It uses the data to extract the photo gps coordinates and adds latitude and longitude fields to the gallery image for displaying on a map.

ujGoogleMaps has the following code in its init function:
<pre>
jrCore_register_event_listener('ujExif','extract_exif_data','ujGoogleMaps_deal_with_exif_data_listener');
</pre>


And the following function in its include.php:

<pre>
function ujGoogleMaps_deal_with_exif_data_listener($_data,$_user,$_conf,$_args,$event) {
    if ($_args['module'] == 'jrGallery') { // check we only do this for specified modules
        $exif = $_data['exif'];
		if(!$exif['GPSLatitude']) { 
		    return $_data;
		} else {
		    $lat = $exif['GPSLatitude']; 
			$lng = $exif['GPSLongitude'];
			
		    // Do stuff with GPS coordinates here
		    
		    // Then add back into the data array
		    // fields will be created as gallery_image_lat and gallery_image_lng
		    $_data["{$_args['file_name']}_lat"] = $lat;
			$_data["{$_args['file_name']}_lng"] = $lng;
		}
    }
    return $_data;
}
</pre>

When the gallery image is then saved to the database it will have additional fields created as gallery_image_lat and gallery_image_lng.


Here is an example of the $_data array received by ujGoogleMaps_deal_with_exif_data_listener.
<pre>
Array
(
    [gallery_image_time] => 1366464263
    [gallery_image_name] => IMG_0352.JPG.jpg
    [gallery_image_size] => 1239863
    [gallery_image_type] => image/jpeg
    [gallery_image_extension] => jpg
    [gallery_image_access] => 1
    [gallery_image_width] => 2048
    [gallery_image_height] => 1536
    [exif] => Array
        (
            [FileName] => jrGallery_26_gallery_image.jpg
            [FileDateTime] => 1366464263
            [FileSize] => 1239863
            [FileType] => 2
            [MimeType] => image/jpeg
            [SectionsFound] => ANY_TAG, IFD0, THUMBNAIL, EXIF, GPS
            [COMPUTED] => Array
                (
                    [html] => width="2048" height="1536"
                    [Height] => 1536
                    [Width] => 2048
                    [IsColor] => 1
                    [ByteOrderMotorola] => 1
                    [ApertureFNumber] => f/2.8
                    [Thumbnail.FileType] => 2
                    [Thumbnail.MimeType] => image/jpeg
                )

            [Make] => Apple
            [Model] => iPhone 3GS
            [Orientation] => 1
            [XResolution] => 72/1
            [YResolution] => 72/1
            [ResolutionUnit] => 2
            [Software] => 4.2.1
            [DateTime] => 2011:01:20 08:46:08
            [YCbCrPositioning] => 1
            [Exif_IFD_Pointer] => 206
            [GPS_IFD_Pointer] => 576
            [THUMBNAIL] => Array
                (
                    [Compression] => 6
                    [XResolution] => 72/1
                    [YResolution] => 72/1
                    [ResolutionUnit] => 2
                    [JPEGInterchangeFormat] => 808
                    [JPEGInterchangeFormatLength] => 9621
                )

            [ExposureTime] => 1/60
            [FNumber] => 14/5
            [ExposureProgram] => 2
            [ISOSpeedRatings] => 80
            [ExifVersion] => 0221
            [DateTimeOriginal] => 2011:01:20 08:46:08
            [DateTimeDigitized] => 2011:01:20 08:46:08
            [ComponentsConfiguration] => 
            [ShutterSpeedValue] => 4885/827
            [ApertureValue] => 4281/1441
            [MeteringMode] => 1
            [Flash] => 32
            [FocalLength] => 77/20
            [SubjectLocation] => Array
                (
                    [0] => 1023
                    [1] => 767
                    [2] => 614
                    [3] => 614
                )

            [FlashPixVersion] => 0100
            [ColorSpace] => 1
            [ExifImageWidth] => 2048
            [ExifImageLength] => 1536
            [SensingMethod] => 2
            [ExposureMode] => 0
            [WhiteBalance] => 0
            [SceneCaptureType] => 0
            [Sharpness] => 1
            [GPSLatitudeRef] => N
            [GPSLatitude] => Array
                (
                    [0] => 43/1
                    [1] => 1974/100
                    [2] => 0/1
                )

            [GPSLongitudeRef] => W
            [GPSLongitude] => Array
                (
                    [0] => 1/1
                    [1] => 162/100
                    [2] => 0/1
                )

            [GPSTimeStamp] => Array
                (
                    [0] => 7/1
                    [1] => 45/1
                    [2] => 5233/100
                )

        )

)

</pre>


It uses the GPS data from the photo to add a location to photos uploaded to jrGallery so that it can display groups of photos on a map. Your module will likely have a different usage because ujGoogleMaps is really cool and you should check it out if you need to use photo locations on your site or in your modules - it will save you a lot of time.


##Installation

Upload the module to your server.

Go to the module info tab in the admin control panel and enable it.

Enable ujExif for each module that you want to use Exif data by adding the module name to 'Image Modules' in ujExif config.

If you want to save the full Exif data to the datastore for future use you can also enable that in config (Note: You almost certainly do not need to do this and should consider this a deprecated feature).

## License 
MIT