<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>
<?php 
require_once('hm_functions.php');
	$auth = $_GET['auth'];
	$bhamashah_id = $_GET['bhamashah_id'];
	$birth_mark = $_GET['birth_mark'];
	$iris_sign_id = str_replace(" ","+",$_GET['iris_s_id']);
	
	$result = queryMysql("SELECT `iris_s_id` FROM `lifeon`");		
if (!empty($result)) {  
   while($row = mysqli_fetch_assoc($result)){
      $iris_s_id = $row["iris_s_id"];
?>
	<script type="text/javascript">
		  var compare_result = null;
		  var params = {
			'host': "iris-cloud.com:8188/uniqbio/irisserver", 
			'signature1': "<?php echo '"' . $iris_sign_id . '"'; ?>",
			'signature2': "<?php echo $iris_s_id; ?>",
			'param1': '' }; // parameters
		
		  // service endpoint
		  var url = 'https://' + params.host + '/compare?access_token=' + <?php echo $auth; ?>;
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
				//compare_result = JSON.stringify(json_response,null,2);    
				//$('#iris_compare').text(compare_result);
				document.getElementById("m_status").value = json_response.status;
			  }
			}
		  }
	</script>
<?php   
		if($_POST['m_status'] == 'MATCH'){
			$response = "Already Registered";
			break;
		}
		else{
			$result1 = queryMysql("INSERT INTO `lifeon`(`s_no`,`bhamashah_id`,`birth_mark`,`iris_s_id`) VALUES (0,'$bhamashah_id','$birth_mark','$iris_sign_id')");
			
			if(result1){
				$response = "Successfully Registered";
			} else{
				echo "ERROR: Could not able to execute $connection. " . mysqli_error($connection);
			}
			// Close connection
			mysqli_close($connection);
		}
    }
// echoing JSON response
echo $response;
}
?>
<body>
<pre id="iris_sign_id"></pre>
<form method="POST">
<input type="text" id="m_status" name="m_status"/>
</form>
</body>
</html>
