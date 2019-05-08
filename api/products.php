<?php

function getProducts(){
  $product_variations = wc_get_products( array(
		'limit' => -1,
    'type' => 'variation'
	) );

  $variable_products = wc_get_products( array(
		'limit' => -1,
    'type' => 'variable'
	) );

  $variations = array();

  foreach($product_variations as $p){
    //echo $p ->get_data()['name'].' - '.$p ->get_data()['parent_id']."<br>";
    $data = array(
      'id' => $p->get_id(),
      'name' => $p->get_name(),
      'price' => $p->get_price()
    );
    $variations[$p ->get_data()['parent_id']][] = $data;
  }

  $products = array();
  foreach($variable_products as $p){
    $data = array(
      'id' => $p->get_id(),
      'name' => $p->get_name(),
      'variations' => $variations[$p->get_id()],
      'img' => wp_get_attachment_image($p -> get_image_id())
    );

    if ( !isset( $products[$p->get_name()] ) ){
      $products[$p->get_name()] = $data;
    } else {
      $flag = true;
      $x = 0;
      while ($flag == true){
        if ( !isset( $products[$p->get_name().$x] ) ){
          $products[$p->get_name().$x] = $data;
          $flag = false;
        }  else {
          ++$x;
        }
      }
    }

  }

  $simple_products = wc_get_products( array(
		'limit' => -1,
    'type' => 'simple'
	) );

  foreach($simple_products as $p){
    $data = array(
      'id' => $p->get_id(),
      'name' => $p->get_name(),
      'variations' => $variations[$p->get_id()],
      'price' => $p->get_price(),
      'img' => wp_get_attachment_image($p -> get_image_id())
    );

    if ( !isset( $products[$p->get_name()] ) ){
      $products[$p->get_name()] = $data;
    } else {
      $flag = true;
      $x = 0;
      while ($flag == true){
        if ( !isset( $products[$p->get_name().$x] ) ){
          $products[$p->get_name().$x] = $data;
          $flag = false;
        }  else {
          ++$x;
        }
      }
    }
  }

  ksort($products);
  $p = array();
  foreach($products as $pr){
    $p[] = $pr;
  }
  //echo "<pre>".print_r($products, true)."<pre>";
  header('Content-Type: application/json');
  echo json_encode($p);
}

require_once($_SERVER['DOCUMENT_ROOT']."/wp-config.php");
require_once(ABSPATH . 'wp-config.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
  getProducts();
  return;
}

?>
