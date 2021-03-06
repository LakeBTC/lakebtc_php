<?php
  function sign($method, $params){

    $accessKey = "foo@bar.com"; 
    $secretKey = "mysecretkey"; 

    $mt = explode(' ', microtime());
    $ts = $mt[1] . substr($mt[0], 2, 6);

    $signature = urldecode(http_build_query(array(
      'tonce' => $ts,
      'accesskey' => $accessKey,
      'requestmethod' => 'post',
      'id' => 1,
      'method' => $method,
      'params' => implode(',', $params),
    )));
    var_dump($signature);

    $hash = hash_hmac('sha1', $signature, $secretKey);

    return array(
      'ts' => $ts,
      'hash' => $hash,
      'auth' => base64_encode($accessKey.':'. $hash),
    );
  }

  function request($method, $params){
    $sign = sign($method, $params);

    $options = array( 
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ' . $sign['auth'],
        'Json-Rpc-Tonce: ' . $sign['ts'],
      ),
  );

    $postData = json_encode(array(
      'method' => $method,
      'params' => $params,
      'id' => 1,
    ));
    print($postData);

    $headers = array(
      'Authorization: Basic ' . $sign['auth'],
      'Json-Rpc-Tonce: ' . $sign['ts'],
    );        
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 
      'LakeBTC Trader; '.php_uname('a').'; PHP/'.phpversion().')'
    );

    curl_setopt($ch, CURLOPT_URL, 'https://www.lakebtc.com/api_v1');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $res = curl_exec($ch);
    return $res;
  }

  try { 
    var_dump(request('getAccountInfo', array()));
    var_dump(request('buyOrder', array(523.45, 1.23, 'USD')));
  } catch (Exception $e) {                
    echo "Error:".$e->getMessage();         
  } 

?>
