<?php

$zone_name = "jal.tw";
$zone_file = "/usr/local/etc/namedb/master/jal.tw";
$KSK_file = "/usr/local/etc/namedb/key/Kjal.tw.+008+33567.key";
$ZSK_file = "/usr/local/etc/namedb/key/Kjal.tw.+008+42644.key";

/* Your SOA in zone file must be or like follow format.
@       IN      SOA     jal.tw. jal.jal.tw.     (
                        2015011202      ;serial
                        7200            ;refresh
                        600             ;retry
                        1209600         ;expire
                        2400            ;NegativeCaching
                        )
*/

$serial = exec ("/usr/bin/grep serial $zone_file | /usr/bin/awk '{print $1}'");

// Use nslookup to get online soa record.
//$serial = exec ("nslookup -type=soa jal.tw | grep serial | awk '{print $3}'");

if (empty($serial)){
  echo "Zone file SOA serial not found.\n";
}else {

  echo "=====> " . date("YmdHis") . "\n";

  $today = date("Ymd");
  $serial_day = substr($serial, 0, 8);
  if($today == $serial_day){
    $new_serial = $serial + 1;
  }elseif($serial_day < $today){
    $new_serial = $today . "00";
  }

  $cmd="/usr/bin/sed -i '' -e 's/$serial/$new_serial/g' $zone_file";

  exec($cmd);
  echo "$cmd\nserial\t". gettype($new_serial) . "\t$new_serial" . "\n";
  system("/usr/local/sbin/dnssec-signzone -r /dev/random -o $zone_name -k $KSK_file $zone_file $ZSK_file");
  system("/usr/local/etc/rc.d/named reload");
}
?>
