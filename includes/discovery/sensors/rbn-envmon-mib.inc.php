<?php

# FIXME could do with a rewrite to walk tables (see other modules), there is more to be monitored.
# But that'll only work IF we can find RBN-ENVMON-MIB somewhere...
#
# Description for the OIDs is at:
# ftp://ftp.qtech.ru/Ericsson_SmartEdge/manual/11.1.2.4%20%20%20PDF/Operation_and_Maintenance/Performance_Management/SNMP_MIBs/Enterprise_MIBs.pdf

if ($device['os'] == "seos")
{
  echo(" RBN-ENVMON-MIB ");

  $descr_data = snmp_walk($device, ".1.3.6.1.4.1.2352.2.4.1.6.1.2", "-Oqv", "");
  $oid_value_data = snmp_walk($device, ".1.3.6.1.4.1.2352.2.4.1.6.1.3", "-Osqn", "");
  $descr_values = array_map(NULL, explode("\n", $descr_data), explode("\n", $oid_value_data));
  if ($descr_values)
  {
    foreach ($descr_values as $index => $descr_value)
    {
      $descr = $descr_value[0];
      $descr = str_replace("Temperature sensor on", "", $descr);
      #oid_value[0] = oid
      #oid_value[1] = temperature value
      $oid_value = explode(" ", $descr_value[1]);
      if ($descr != "")
      {
        discover_sensor($valid['sensor'], 'temperature', $device, $oid_value[0], $index, 'seos', $descr, 1, 1, NULL, NULL, NULL, NULL, $oid_value[1]);
      }
    }
  }

  $descr_data = snmp_walk($device, ".1.3.6.1.4.1.2352.2.4.1.3.1.2", "-Oqv", "");
  $oid_value_data = snmp_walk($device, ".1.3.6.1.4.1.2352.2.4.1.3.1.4", "-Osqn", "");
  $desired_voltages = snmp_walk($device, ".1.3.6.1.4.1.2352.2.4.1.3.1.3", "-Oqv", "");
  $descr_values = array_map(NULL, explode("\n", $descr_data), explode("\n", $oid_value_data), explode("\n", $desired_voltages));
  if ($descr_values)
  {
    foreach ($descr_values as $index => $descr_value)
    {
      $descr = $descr_value[0];
      list($oid, $value) = explode(" ", $descr_value[1]);
      $desired = $descr_value[2];
      $low_limit = ($desired * 0.85) / 1000;
      $high_limit = ($desired * 1.15) / 1000;
      if ($descr != "" and $value)
      {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'seos', $descr, '1000', 1, $low_limit, NULL, $high_limit, NULL, $value);
      }
    }
  }
}

// EOF
