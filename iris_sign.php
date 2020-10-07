<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<!--<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>--->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
<?php
require_once('hm_functions.php');
	$auth = $_GET['auth'];
	$iris_sign_id =  urlencode($_GET['iris_s_id']);
	//$auth = "Y0iWcR9j810wfn12euT6LIE289jGoYyIULDQesdlKu";
	//$iris_sign_id = "xJSiaer6OpDkKPwNtaeuPgZyksqLaTxjhsNz4QsYyuFO/07BA679qt0Ya//UwonAdLZs9mxo/QuHox/lLM7Gi5tEx9L2BZ/WcPwUgtsL2nhzYjcp6yIlhC2NegvqG7STD93XGE5N7kPKUz6pcAINzxM/hBhsS6cGHyr3vCzVVUQc2ySePUyVjCMRlwL7lXGicJRmcG3DlT6STMBEEaLAuOWKNckoYPbCMHEDT4IjqMSw3uGS44Qrn7JwyIu9EgIPV4op2ZdoVm/qHYMQdE4pwb/z4DW1gRUooFZyy43xtbCDffwUrRvuXLaNyhRhvaEueeo0yLKC+X/b94ah41xmmq0wGF2N61ksqBsIGMQf+lHU/KDGSRBornlei12un3EJj41wKixEBHdgIbpEVfdo4ao9TS6XRP5rPsK3mNt5gEZtKbL662G6A3a90rZ3everETTKe8l2z3Ytncv8j4TES9aesHT/J2bIAiydcwJKFt9cW8bPqSRjFFPNQxzyClBvdSclcvj3yeYTZ+SrZNdFSqWhw/1m8FRU9K5QIRlbUlpeDqF7iI+dn2fZUWuRr2TKdSTXAbm6sDjgE5anXGQJuSKSpb0d1cMZsdR6RYuos7d1h98YKcSN7M+AKaSLRvAP7DsGWWGlL7KTL0yJimRRAPjczUsRumdWME9t6iEam3Gs6yz5LoHEKIwUYnsAiwCFX/1DQz03Gf1q75SMf6xKQn0Ng7qneLewretenZ2GmRAlRAm9kxBt7HacU/pN4GJwTwrxTpsPpAxjsAJ7d/OMbSRGwPFNIWzlRGiDviQ4VCDMyIOIiabFy3RipRosLWCvFKlB8oA8I7ok6UWl9YdUoadmUsIb6+XGlFX4yNBjodSdBs73T/mmdctmmZd12YdWeUWp1bdWsCx8FfUTBUKatR3OGEoxS4PjGerk5GEVfwGQ27GyIF4DpTD8qDupi1eheV13uETgTven34Lk0XyERsjSVsxwX832SD1ON/v9VZc=";
?>
<script type="text/javascript" >
function display_result(iris_s_id, bhamashah_id){

 var i_s_id_1 = iris_s_id;
 var i_s_id_2 = decodeURIComponent(<?php echo '"' . $iris_sign_id . '"'; ?>);
  
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'signature1': i_s_id_1,
    'signature2': i_s_id_2,
    'param1': '' }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/compare?access_token=<?php echo $auth; ?>' ;
  var payload = '{ "iris_signature1": "' + params.signature1
        + '", "iris_signature2": "' + params.signature2 + '" }';
  
  // construct payload
  var xmlhttp = new XMLHttpRequest();

  xmlhttp.open('POST', url, true);
  // add required headers
  xmlhttp.setRequestHeader('Content-Type', 'application/json');
  xmlhttp.setRequestHeader('Accept', 'application/json');
  
  xmlhttp.send(payload);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {
        var json_response = JSON.parse(xmlhttp.responseText);
        compare_result = JSON.stringify(json_response);
		if(json_response.status === "MATCH") {
        	$('#result_m').html("MATCH FOUND");
			$('#result_id').html("Bhamashah Id :- " + bhamashah_id);
		} 
		else {
			//$('#result_m').html("NO MATCH FOUND");
		}
      }
    }
  }
}
</script>
</head>

<body>
<?php 

	$result = queryMysql("SELECT `bhamashah_id`, `iris_s_id` FROM `lifeon`");				
if (!empty($result)) {      
	//$response['bhamashah_id'] = array();
	$x= 1;
   while($row = mysqli_fetch_assoc($result)){
      //$bhamashah_id = $row["bhamashah_id"];
	  $bhamashah_id = $row["bhamashah_id"];
      $iris_s_id = $row["iris_s_id"];
	  //$match_resp = file_get_contents('match.php?i_s_id_1='.$iris_sign_id.'&i_s_id_2='.$iris_s_id.'&auth='.$auth);
		echo '<script type="text/javascript">    display_result("'. $row["iris_s_id"] .'", "'. $bhamashah_id .'");      </script>';
		$x++;
	}
}
?>

<p id="result_m"></p>
<p id="result_id"></p>
</body>
</html>
