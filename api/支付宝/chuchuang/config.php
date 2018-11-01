<?php
$config = array (
		'alipay_public_key_file' => dirname(__FILE__)."/key/alipay_rsa2_public_key.pem",
		'merchant_private_key_file' => dirname( __FILE__ ). "/key/rsa2_private_key.pem",
		'merchant_public_key_file' => dirname( __FILE__ ). "/key/rsa2_public_key.pem",
		'charset'	=> "GBK",
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		'app_id' => ALIPAY_APP_ID
);