<?php
class ModelInstall extends Model {
	public function mysql($data) {
		$connection = mysql_connect($data['db_host'], $data['db_user'], $data['db_password']);
		
		mysql_select_db($data['db_name'], $connection);
		
		mysql_query("SET NAMES 'utf8'", $connection);
		mysql_query("SET CHARACTER SET utf8", $connection);
		
		$file = DIR_APPLICATION . 'shopilex.sql';
	
		if ($sql = file($file)) {
			$query = '';

			foreach($sql as $line) {
				$tsl = trim($line);

				if (($sql != '') && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != '#')) {
					$query .= $line;
  
					if (preg_match('/;\s*$/', $line)) {
						$query = str_replace("DROP TABLE IF EXISTS `si_", "DROP TABLE IF EXISTS `" . $data['db_prefix'], $query);
						$query = str_replace("CREATE TABLE `si_", "CREATE TABLE `" . $data['db_prefix'], $query);
						$query = str_replace("INSERT INTO `si_", "INSERT INTO `" . $data['db_prefix'], $query);
											
						$result = mysql_query($query, $connection);
  
						if (!$result) {
							#printf("MySQL server version: %s\n", mysql_get_server_info());
							printf("%s\n", $result);
							#die(mysql_error());
						}
	
						$query = '';
					}
				}
			}
			
			mysql_query("SET CHARACTER SET utf8", $connection);
	
			mysql_query("SET @@session.sql_mode = 'MYSQL40'", $connection);
		
			mysql_query("DELETE FROM from `" . $data['db_prefix'] . "user` WHERE user_id = '1'");
		
			mysql_query("INSERT INTO `" . $data['db_prefix'] . "user` SET user_id = '1', user_group_id = '1', username = '" . mysql_real_escape_string($data['username']) . "', password = '" . mysql_real_escape_string(md5($data['password'])) . "', status = '1', email = '" . mysql_real_escape_string($data['email']) . "', date_added = NOW()", $connection);

			mysql_query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_email'", $connection);
			mysql_query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_email', value = '" . mysql_real_escape_string($data['email']) . "'", $connection);
			
			mysql_query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_url'", $connection);
			mysql_query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_url', value = '" . mysql_real_escape_string(HTTP_SHOPILEX) . "'", $connection);
			
			mysql_query("UPDATE `" . $data['db_prefix'] . "product` SET `viewed` = '0'", $connection);
			
			mysql_close($connection);	
		}		
	}	
}
?>
