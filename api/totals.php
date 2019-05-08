<?php

require_once($_SERVER['DOCUMENT_ROOT']."/wp-config.php");
require_once(ABSPATH . 'wp-config.php');

function getTotals(){
  global $woocommerce;

  $t = $woocommerce->cart->get_totals();

  $totals = array(
    'subtotal' => $t['subtotal'],
    'shipping' => $t['shipping_total'],
    'tax' =>      $t['total_tax'],
    'total' =>    $t['total']
  );

  header('Content-Type: application/json');
  echo json_encode($totals);
  return;

  //echo "<pre>".print_r($totals, true)."</pre>";
  //echo "<pre>".print_r($woocommerce->cart->get_totals(), true)."</pre>";
  //echo "<pre>".print_r($woocommerce->cart, true)."</pre>";
}

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
  getTotals();
  return;
}

?>
