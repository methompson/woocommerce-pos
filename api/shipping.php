<?php

require_once('config.php');

function getMethods(){

  $default = 'local_pickup';

  $methods = array();
  $currentShipping = WC()->session->get('chosen_shipping_methods')[0];

  foreach ( WC()->cart->get_shipping_packages() as $p ){
    //$shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $p );
    foreach (WC_Shipping_Zones::get_zone_matching_package( $p )->get_shipping_methods() as $m){

      if ($m->id != 'free_shipping'){
        if (strpos($currentShipping, $m->id) !== false){
          $selected = 'checked';
        } else {
          $selected = '';
        }
        $methods[$m->id] = array(
          'id' => $m->id,
          'instance_id' => $m->get_instance_id(),
          'title' => $m->title,
          'selected' => $selected
        );
      }
    }
  }

  header('Content-Type: application/json');
  echo json_encode($methods);
  return;
}

function setMethod($method = ''){
  if ( !isset($_POST['shipping_method']) && $method == ''){
      return;
  }

  if ( isset($_POST['shipping_method']) ){
    $method = $_POST['shipping_method'];
  }

  $shipping_method = array( $_POST['shipping_method'] );
  foreach ( $shipping_method as $i => $value ) {
      $chosen_shipping_methods[ $i ] = wc_clean( $value );
  }

  WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
  WC()->cart->calculate_totals();
  return;

}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  setMethod();
  return;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
  getMethods();
  return;
}

 ?>
