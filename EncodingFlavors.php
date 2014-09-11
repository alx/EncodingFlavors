<?php
class EncodingFlavors {

    static function Load() {
      Plugin::Attach ( 'encode.update' , array( __CLASS__ , 'StartEncodingFlavors' ) );
    }

    static function StartEncodingFlavors() {
      global $video;

      $db = Database::GetInstance();

      // Set MySQL wait_timeout to 10 hours to prevent 'MySQL server has gone away' errors
      $db->Query ("SET @@session.wait_timeout=36000");

      $query = 'SELECT * FROM `'.DB_PREFIX.'encoding_flavors`';
      $result = $db->Query ($query);

      while ($encoding = $db->FetchObj ($result)):
        EncodingFlavors::EncodeVideoWithFlavor($video, $flavor);
      endwhile;
    }

    static function EncodeVideoWithFlavor($video, $flavor) {
      App::LoadClass ('Video');
      global $video, $config;

      $raw_video = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->original_extension;
      $ffmpeg_path = Settings::Get ('ffmpeg');

      $file = UPLOAD_PATH . '/' . $flavor->folder . '/' . $video->filename . '.' . $flavor->extension;

      // Debug Log
      $config->debug_conversion ? App::Log (CONVERSION_LOG, "\nPreparing for: Encoding flavor " . $flavor->name . "...") : null;

      ### Encode raw video to FLV
      $encoding_command = "$ffmpeg_path -i $raw_video " . $flavor->command_options . " $file >> $debug_log 2>&1";

      // Debug Log
      $log_msg = "\n\n\n\n==================================================================\n";
      $log_msg .= "ENCODING FLAVOR : " . $flavor->name . "\n";
      $log_msg .= "==================================================================\n\n";
      $log_msg .= "Encoding Command: $encoding_command";
      $log_msg .= "Encoding Output:\n\n";
      $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Encoding Flavor Command: ' . $encoding_command) : null;
      App::Log ($debug_log, $log_msg);

      ### Execute encoding command
      exec ($encoding_command);

      // Debug Log
      $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Verifying flavor video was created...') : null;

      ### Verify video file was created successfully
      if (!file_exists ($file) || filesize ($file) < 1024*5) throw new Exception ("The flavor file was not created. The id of the video is: $video->video_id");
    }

    static function Info() {
      return array (
        'name'    => 'EncodingFlavors',
        'author'  => 'Alexandre Girard',
        'version' => '1.0.0',
        'site'    => 'http://alexgirard.com/',
        'notes'   => 'Add custom encoding flavors, with various options (watermark, player thumbnail preview, ...)'
      );
    }

    static function Install() {
      $db = Database::GetInstance();

      $create_query = 'CREATE TABLE `'.DB_PREFIX.'encoding_flavors` (';
      $create_query .= '  `encoding_id` bigint(20) NOT NULL AUTO_INCREMENT,';
      $create_query .= '  `name` varchar(255) NOT NULL,';
      $create_query .= '  `folder` varchar(255) NOT NULL,';
      $create_query .= '  `extension` varchar(255) NOT NULL,';
      $create_query .= '  `command_options` text NOT NULL,';
      $create_query .= '  PRIMARY KEY (`encoding_id`)';
      $create_query .= ') DEFAULT CHARSET=utf8';

      $db->Query($create_query);

      $create_query = 'CREATE TABLE `'.DB_PREFIX.'encoding_options` (';
      $create_query .= '  `encoding_option_id` bigint(20) NOT NULL AUTO_INCREMENT,';
      $create_query .= '  `encoding_id` bigint(20) NOT NULL,';
      $create_query .= '  `name` varchar(255) NOT NULL,';
      $create_query .= '  `value` varchar(255) NOT NULL,';
      $create_query .= '  PRIMARY KEY (`encoding_option_id`)';
      $create_query .= ') DEFAULT CHARSET=utf8';

      $db->Query($create_query);
    }

    static function Uninstall() {
      $db = Database::GetInstance();
      $drop_query = 'DROP TABLE IF EXISTS `'.DB_PREFIX.'encoding_flavors`';
      $db->Query($drop_query);
      $drop_query = 'DROP TABLE IF EXISTS `'.DB_PREFIX.'encoding_options`';
      $db->Query($drop_query);
    }

    static function Settings() {
      $db = Database::GetInstance();

      $query = "SELECT * FROM " . DB_PREFIX . "encoding_flavors";
      $result = $db->Query ($query);
      $total = $db->Count ($result);
?>
<h1>Encoding Flavors</h1>

<div class="block">
  <h2>Create new encoding flavor</h2>
  <form method="post">
    <input type="hidden" name="encoding_action" value="update"/>
    <p><label for="encoding_name">Name :</label><br><input name="encoding_name" value="" type="text"/></p>
    <p><label for="encoding_command_options">Command options :</label><br><input name="encoding_command_options" value="" type="text"/></p>
    <p><input value="Create" type="submit"/></p>
  </form>
</div>

<?php if ($total > 0): ?>
<div class="block list">
  <?php while ($row = $db->FetchObj ($result)): ?>
  <?php endwhile ?>
</div>
<?php else: ?>
<div class="block"><strong>No encoding flavor found</strong></div>
<?php endif; ?>

<?php
    }
}
?>
