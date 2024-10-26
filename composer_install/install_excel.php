<?php
phpinfo();
exit;
	// systemout("composer require phpoffice/phpspreadsheet");
 exec("composer require phpoffice/phpspreadsheet", $lines, $result);
  echo "result = $result<br>";

  echo "Lines<br>\n";
  foreach ($lines as $k => $v) {
    echo "k=$k v=$v<br>\n";
  }	
?>
