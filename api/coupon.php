<?php

function getCoupons(){
  global $woocommerce;
  $discounts = $woocommerce->cart->get_coupon_discount_totals();
  //echo "<pre>".print_r($discounts, true)."</pre>";
  header('Content-Type: application/json');
  echo json_encode($discounts);
}

function addCoupon(){
  if (!isset($_POST['coupon_code'])){
    return;
  }

  global $woocommerce;
  $result = $woocommerce->cart->apply_coupon($_POST['coupon_code']);
  //$result = $woocommerce->cart->apply_coupon('tptestb');

  //echo $result;
}

function deleteCoupon(){
  if ( !isset($_REQUEST['coupon_code']) ){
    http_response_code(400);
    return;
  }

  global $woocommerce;
  $result = $woocommerce->cart->remove_coupon($_REQUEST['coupon_code']);

  if ($result != 1){
    http_response_code(400);
    return;
  }

  http_response_code(204);
  return;
}

require_once($_SERVER['DOCUMENT_ROOT']."/wp-config.php");
require_once(ABSPATH . 'wp-config.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
  getCoupons();
  return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  addCoupon();
  getCoupons();
  return;
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE'){
  deleteCoupon();
  return;
}

?>
