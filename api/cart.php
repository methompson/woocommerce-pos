<?php

function getCart(){
  $wc_cart = WC()->cart->get_cart_contents();

  //echo "<pre>".print_r($wc_cart, true)."</pre>";
  global $customProducts;

  $cart = array();

  foreach($wc_cart as $p){
    $data = $p['data']->get_data();

    $price = $data['price'];
    $name = $data['name'];

    //Custom Product
    if (in_array($p['product_id'], $customProducts)){
      if ( WC()->session->get($p['product_id']) ){
        $price = WC()->session->get($p['product_id'])['price'];
      }

      if ( WC()->session->get($p['product_id']) ){
        $name .= ' - '.WC()->session->get($p['product_id'])['name'];
      }

    }

    $product = array(
      'key' => $p['key'],
      'item' => array(
        //'key' => $p['key'],
        'product_id' => $p['product_id'],
        'quantity' => $p['quantity'],
        'name' => $name,
        'stock' => $p['data'] -> get_stock_quantity(),
        'price' => $price,
        'image' => wp_get_attachment_image_src($data['image_id'])[0]
      )
    );
    $cart[] = $product;

  }
  header('Content-Type: application/json');
  echo json_encode($cart);
  return;
}

function addToCart(){

  //check that the item is passed and that it exists
  if (!isset($_POST['product_id']) ) {
    http_response_code('400');
    return;
  }

  $product = wc_get_product($_POST['product_id']);
  if (!$product){
    http_response_code('400');
    return;
  }

  //check if quantity is passed, otherwise, default to 1
  if ( isset($_POST['quantity']) && $_POST['quantity'] > 0 ) {
    $quantity = $_POST['quantity'];
  } else {
    $quantity = 1;
  }

  WC()->cart->add_to_cart($_POST['product_id'], $quantity);
}

function addCustomToCart(){
  //check that the item is passed and that it exists
  if (!isset($_POST['product_id']) || !isset($_POST['custom_name']) || !isset($_POST['custom_price'])) {
    http_response_code('400');
    return;
  }

  $product = wc_get_product($_POST['product_id']);
  if (!$product){
    http_response_code('400');
    return;
  }

  //check if quantity is passed, otherwise, default to 1
  if ( isset($_POST['quantity']) && $_POST['quantity'] > 0 ) {
    $quantity = $_POST['quantity'];
  } else {
    $quantity = 1;
  }

  WC()->cart->add_to_cart($_POST['product_id'], $quantity);

  $id = (int)$_POST['product_id'];
  $price = (float)$_POST['custom_price'];

  $customArray = array(
    'price' => $price,
    'name' => sanitize_text_field($_POST['custom_name'])
  );

  WC()->session->set($id, $customArray);
  http_response_code('200');
  return;
}

function updateCart(){
  if ( !isset($_REQUEST['cartdata']) ){
    return;
  }

  $returnValue = '200';

  $wc_cart = WC()->cart->get_cart_contents();

  //echo "<pre>".print_r($wc_cart, true)."</pre>";

  //iterate through the updated cart data
  foreach($_REQUEST['cartdata'] as $update){

    //echo "<pre>".print_r($update, true)."</pre>";
    //var_dump($update);
    //compare updated cart key with server cart key

    //echo "<pre>".print_r($update['item'], true)."</pre>";
    foreach ($wc_cart as $key => $p){
      if ($key == $update['key']){
        //echo "Update ID: ".$update['item']['product_id']."<br>";
        //echo "Cart ID: ".$p['product_id']."<br>";
        //Sanity Check: Make sure that the IDs are the same:
        if ($p['product_id'] != $update['item']['product_id']){
          continue;
        }

        //Check if the values are the same. Do nothing if they are
        if ($p['quantity'] != $update['item']['quantity']){
          //check stock.

          if ( $p['data'] -> get_stock_quantity() > 0 && $update['item']['quantity'] > $p['data'] -> get_stock_quantity() ){
            //echo "Quantity: ".$update['item']['quantity'];
            //echo "Stock: ".$p['data'] -> get_stock_quantity();
            //If update quantity is greater than stock, set quantity to stock
            //quantity. Set return value to 205 to
            //echo "Problem there";
            WC()->cart->set_quantity($key, $p['data'] -> get_stock_quantity());
            $returnValue = '205';
          } else if ($update['item']['quantity'] < 1){
            WC()->cart->remove_cart_item( $key );
            $returnValue = '205';
            //echo "Problem here";
            //
          } else {
            WC()->cart->set_quantity($key, $update['item']['quantity']);
          }

        }


        //echo "<pre>".print_r($p, true)."</pre>";
        echo $p['data'] -> get_stock_quantity();
      }
    }
    //var_dump($p);
  }

  return $returnValue;
}

function deleteFromCart(){
  if ( !isset($_REQUEST['cart_key']) ){
    http_response_code(400);
    return;
  }

  $wc_cart = WC()->cart->get_cart_contents();

  foreach($wc_cart as $key => $p){
    if ($key == $_REQUEST['cart_key']){
      WC()->cart->remove_cart_item( $key );
    }
  }

  http_response_code(204);
  return;
}

//Error reporting
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
require_once($_SERVER['DOCUMENT_ROOT']."/wp-config.php");
require_once(ABSPATH . 'wp-config.php');

$customProducts = array('1610', '2258', '2259', '2260', '2261', '2262');

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])
&& in_array( $_POST['product_id'], $customProducts) ){
  addCustomToCart();
  getCart();
  return;
}

//Add item to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) ){
  addToCart();
  getCart();
  return;
}

//Add Custom Item to Cart

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
  getCart();
  return;
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE'){
  deleteFromCart();
  return;
}

if ($_SERVER['REQUEST_METHOD'] == 'PATCH'){
  $returnValue = updateCart();
  http_response_code($returnValue);
  return;
}


//echo $_SERVER['REQUEST_METHOD']."<br>";
//echo "<pre>".print_r($_SERVER, true)."<pre>";
//echo "<pre>".print_r($_REQUEST, true)."<pre>";
//echo "<pre>".print_r($cart, true)."<pre>";
?>
