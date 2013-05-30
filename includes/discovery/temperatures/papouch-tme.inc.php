<?php

if ($device['os'] == "papouch")
{
  echo("Papouch TME ");

  $descr = snmp_get($device, "SNMPv2-SMI::enterprises.18248.1.1.3.0", "-Oqv");
  $temperature  = snmp_get($device, "SNMPv2-SMI::enterprises.18248.1.1.1.0", "-Oqv") / 10;

  if ($descr != "" && is_numeric($temperature) && $temperature > "0")
  {
    $temperature_oid = ".1.3.6.1.4.1.18248.1.1.1.0";
    $descr = trim(str_replace("\"", "", $descr));
    discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, "1", 'papouch-tme', $descr, '10', '1', NULL, NULL, NULL, NULL, $temperature);
    # FIXME type above should be papouch without -tme, but we'd need to rename the rrds in the upgrade process as to not lose history
  }
}

?>
