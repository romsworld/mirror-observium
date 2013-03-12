<?php

$storage_cache = array();

$sql  = "SELECT *, `storage`.`storage_id` as `storage_id`";
$sql .= " FROM  `storage`";
$sql .= " LEFT JOIN  `storage-state` ON  `storage`.storage_id =  `storage-state`.storage_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows("SELECT * FROM storage WHERE device_id = ?", array($device['device_id'])) as $storage)
{
  echo("Storage ".$storage['storage_descr'] . ": ");

  $storage_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("storage-" . $storage['storage_mib'] . "-" . safename($storage['storage_descr']) . ".rrd");

  if (!is_file($storage_rrd))
  {
   rrdtool_create($storage_rrd, "--step 300 DS:used:GAUGE:600:0:U DS:free:GAUGE:600:0:U ".$config['rrd_rra']);
  }

  $file = $config['install_dir']."/includes/polling/storage-".$storage['storage_mib'].".inc.php";
  if (is_file($file))
  {
    include($file);
  } else {
    // Generic poller goes here if we ever have a discovery module which uses it.
  }

  if ($debug) {print_r($storage); }

  if ($storage['size'])
  {
    $percent = round($storage['used'] / $storage['size'] * 100);
  }
  else
  {
    $percent = 0;
  }

  echo($percent."% ");

  rrdtool_update($storage_rrd,"N:".$storage['used'].":".$storage['free']);

  if (!is_numeric($storage['storage_polled'])) { dbInsert(array('storage_id' => $storage['storage_id'], 'storage_used' => $storage['used'],
    'storage_free' => $storage['free'], 'storage_size' => $storage['size'], 'storage_units' => $storage['units'], 'storage_perc' => $percent), 'storage-state'); }

  $update = dbUpdate(array('storage_polled' => time(), 'storage_used' => $storage['used'], 'storage_free' => $storage['free'], 'storage_size' => $storage['size'],
    'storage_units' => $storage['units'], 'storage_perc' => $percent), 'storage-state', '`storage_id` = ?', array($storage['storage_id']));

  echo("\n");
}

unset($storage);

?>
