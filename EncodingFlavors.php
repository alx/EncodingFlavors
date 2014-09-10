<?php
class EncodingFlavors {

    static function Load() {
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
