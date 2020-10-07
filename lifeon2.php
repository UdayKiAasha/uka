<!DOCTYPE html>
<html>
<head>
<title>UdayKiAasha</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="favicon.ico">  
<link rel="stylesheet" href="https://www.w3schools.com/w3css/3/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body,h1,h2,h3,h4,h5,h6 {font-family: "Raleway", Arial, Helvetica, sans-serif}
</style>
<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
<script type="text/javascript">

function show_status() {
  var status = null;
  // make hidden <pre> visible
  $('#server_status').text('waiting for the response from https://iris-cloud.com...');
  $('#server_status').show();
  // code to check server status
  var xmlhttp = new XMLHttpRequest();
  var url = "https://iris-cloud.com:8188/uniqbio/irisserver/";
  xmlhttp.open('GET',url,true);
  xmlhttp.send(null);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {
        status = xmlhttp.responseText;
        // set status data
        $('#server_status').text(status);
		request_auth();
      }
      else {
         //alert("Error (server status) - " + xmlhttp.responseText);
         $('#server_status').text("ERROR - " + xmlhttp.responseText);
      }
    }
  };
}
// function to get oauth2 bearer access_token
function request_auth() {
  var bearer = null;
  $('#server_auth').text('waiting for the response from https://iris-cloud.com...');
  $('#server_auth').show();
  var setting = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'clientId': 'CLIENT-WEB-DEMO',        // your client_id
    'clientSecret': 'AyOZ3gLT35vzTEDk9mgqv2' }; // your client_secret

  var authHost     = "https://" + setting.host;
  var resourceHost = "https://" + setting.host;

  var tokenEndpoint = authHost + "/token";
  var authEndpoint = authHost + "/authorize";
  
  var xmlhttp = new XMLHttpRequest();
  var url = authEndpoint + '?grant_type=code&client_id='
        + setting.clientId + '&scope=all&redirect_uri=none';

  xmlhttp.open('GET',url,true);
  xmlhttp.send(null);

  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {     
        var xmlhttp2 = new XMLHttpRequest();
        var url2 = tokenEndpoint + '?grant_type=authorization_code&client_id='
            + setting.clientId + '&client_secret='
            + setting.clientSecret + '&code=' + xmlhttp.responseText;

        xmlhttp2.open('POST',url2,true);
        xmlhttp2.send(null);
        xmlhttp2.onreadystatechange = function() {
          if (xmlhttp2.readyState == 4) {
            if ( xmlhttp2.status == 200) {
              // this is your oauth2 access token
              bearer = JSON.parse(xmlhttp2.responseText).access_token;
              $('#server_auth').text(bearer);
            }
            else {
              //alert("Error (server status) - " + xmlhttp2.responseText);
              $('#server_auth').text("ERROR - " + xmlhttp2.responseText);
            }
          }
        };
      }
      else {
         //alert("Error (server status) - " + xmlhttp.responseText);
         $('#server_auth').text("ERROR - " + xmlhttp.responseText);
       }
    }
  };
};

// function to create an Iris Signature from an eye image
// requires a valid oauth2 bearer access_token (see above example)
function gen_iris_signature(bearer) {
  var iris_sign = null;
  $('#img_processed').attr('src', null);
  $('.prompt1').text('Click here to see server response');
  $('.prompt1').hide();
  $('#iris_sign').text('waiting for the response from https://iris-cloud.com...');
  $('#iris_sign').show(); // unhide div to show access token
  var setting = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'eye1_image': '/images/eyes_l.jpg',        // sample image
    'eye2_image': '/images/eye2_480x320.jpg',        // sample image
    'param1': '' }; // parameters

  // service endpoint
  var url = 'https://' + setting.host + '/encode?access_token=' + bearer;
  iris_base = document.getElementById("base").innerHTML;
  var payload = '{\
"tag_device_id": "demo_device",\
"image_count": 1,\
"process_only": false,\
"reason_encode": "enroll",\
"image_def": {\
  "width": 480,\
  "height": 360,\
  "eye_type": "left",\
  "image_tag": "eye1_480x320",\
  "image_format": "jpg",\
  "gaze_flag": 0,  \
  "image_buffer" : {\
    "length": 46384 ,\
    "data": "' + iris_base +  '"\
  }\
}}';
  
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
        // json response data contains processed iris image, meta-data and iris signature
        var json_response = JSON.parse(xmlhttp.responseText);
        iris_sign = JSON.stringify(json_response,null,2);   
		$('#iris_sign').hide(); // un-hide by clicking show repsonse	
        $('#iris_sign').text(iris_sign);
		$('#iris_sign_id').text(json_response.iris_signature.data);
		//$('#iris_img').text(json_response.iris_image_processed.image_buffer.data);
        $('#img_processed').attr('src', 'data:image/gif;base64,' 
            + json_response.iris_image_processed.image_buffer.data);
        $('#img_processed').show();
		$('.prompt1').show();
		view_match(json_response.iris_signature.data);
      }
      else {
        //alert("Error (server status) - " + xmlhttp.responseText);
        $('#iris_sign').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};
/*
function match_iris(iris_sign_id) {
	var auth = document.getElementById("server_auth").innerHTML;
    var iris_s_id = iris_sign_id;
    $.ajax({
      url: 'iris_sign.php?iris_s_id='+iris_s_id+'&auth='+auth,
      success: function( data ) {
        var parsed = JSON.parse(data);
		var i_arr = [];
		for(var x in parsed){
			var compare_result = null;
			  $('#iris_compare').text('waiting for the response from https://iris-cloud.com...');
			  $('#iris_compare').show(); // unhide div to show comparison result
			  var params = {
				'host': "iris-cloud.com:8188/uniqbio/irisserver", 
				'signature1': x['iris_s_id'],
				'signature2': iris_sign_id,
				'param1': '' }; // parameters
			
			  // service endpoint
			  var url = 'https://' + params.host + '/compare?access_token=' + document.getElementById("server_auth").innerHTML;
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
					compare_result = JSON.stringify(json_response,null,2);    
					$('#iris_compare').text(compare_result.status);
				  }
				  else {
					$('#iris_compare').text("ERROR - " + xmlhttp.responseText);
				  }
				}
			  }
		  //i_arr.push(parsed[x]);
		}
        // set the button value to the new status.
        //$('#addFriendBtn').unbind('click');
		
      }
    }
    );
  }
  */
function add_iris() {
	var auth = document.getElementById("server_auth").innerHTML;
   	var bhamashah_id1 = document.getElementById("bhamashah_id").value;
	var birth_mark = document.getElementById("birth_mark").value;
	var iris_s_id = document.getElementById("iris_sign_id").innerHTML;
    $.ajax({
      url: 'add_iris.php?iris_s_id='+iris_s_id+'&bhamashah_id='+bhamashah_id1+'&birth_mark='+birth_mark+'&auth='+auth,
      success: function( data ) {
        $('#mother_name').text('Mother Name: '+data);
		
        // set the button value to the new status.
        //$('#addFriendBtn').unbind('click');
      }
    }
    );
  }


function view_bhamashah() {
    $.ajax({
      url: 'iris_sign.php?iris_s_id='+iris_s_id+'&auth='+auth,
      success: function( data ) {
	  var json_response = JSON.parse(data);
        $('#mother_name').html('Mother Name: '+ json_response.hof_Details.NAME_ENG);
		$('#bhamashah').html('Bhamashah ID: '+ json_response.hof_Details.BHAMASHAH_ID);
		$('#address').html('Address: '+ json_response.hof_Details.ADDRESS);
		$('#mobile').html('Mobile: '+ json_response.hof_Details.MOBILE_NO);
        // set the button value to the new status.
        //$('#addFriendBtn').unbind('click');
		compare_signatures(document.getElementById("server_auth").innerHTML);
      }
    }
    );
  }
  
function view_match(iris_sign_id) {
	var auth = document.getElementById("server_auth").innerHTML;
    var iris_s_id = iris_sign_id;
    $.ajax({
      url: 'iris_sign.php?iris_s_id='+iris_s_id+'&auth='+auth,
      success: function(data) {
	  //var json_response = JSON.parse(data);
        $('#mother_name').html('Match Result: '+ data);
		
        // set the button value to the new status.
        //$('#addFriendBtn').unbind('click');
		//compare_signatures(document.getElementById("server_auth").innerHTML);
      }
    }
    );
  }
  
// performs 1:N iris identification
// requires previously encoded Iris Signature for left/right eyes for matching (probes)
// requires a valid oauth2 bearer access_token (see above example)
function identify_iris(bearer) {
  var match_results = null;
  $('#iris_identify').text('waiting for the response from https://iris-cloud.com...');
  $('#iris_identify').show(); // unhide div to show access token
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'signature_right': "YTS39ZQ9ptHm0s5VWtQqxJIGrLgDedRfVWCvIuZoXBMpIkEEbxcsR8S3oyHCnwujO384SQviNA2Ud+nyEE7BmNdIALyqicDUS0fDuXdu+voCj++LXbi2G7HM3I/jrB7jVYET2t5yteDJ9nSDGH3np3aT7MVFYuxACMegG3iBHKStDhldAN4Iwd5K4s5ERbKtkuB6jQyDm4rgzy04YFAqgsvwkukgtJ4V+EOIoe7l49uEdDSFZwkessNsjfAGp9Ha1O9rtrJbSLlDOtzXObR3/QkW9sW03vOkoQSFise36Ug1AysjU/i2EMuixQ0UIJ/FPuvZ0MZ3uQN/wQCXTqNA9etXSLdjSnIkP8xV0jS4nsRu9Qe5COMaTY217Hg6xAm2obG0zYpDgXPcZOyOIE6LJTRyBddJQfzl9cJCCKTOhzzOyTJf4PuzrUU4rCh1mZH5dUqFKoAsJdtongRzok2E1LuzDO0wVbIa3p7X0JQvjDV0StkdPTCXCpAJTwwTQaA6v/Vd+osi0uWyPadvmna9lGLFXn4ePcL0W1YY53b7fChCfKOy0F/P5eTVBQSukdbcgDDy03jv6HgvnfW1urkXHwcLQm3AEntyOJIWvlF+2Ni8tgXyZQ74bBI7lxn429S9G6yq20PWaWWHSgL+rW+9TggL42VvxTO1xs2JDCgb9rhBKcp72651mAf33WP7RkXrkFiXllim1DxIHCRizaK+26v6jQqDrVUbQydYHSzFIGMGCcqUz9USIPTnW+iguW+zPYlsKliK3/ggr+QMDEts5pn5dYZpV0gC+gIX1zpMAg89WAtVqje9MC5g0OJHNSYUcHx8rHrvaodBA5aqfgp7inJAdB2lsW96OT+itnJBqxtmzICsc8NEJ/EkVmyEoF1WSiYYD3BIMmfBjsHtnD4pb+VDu6RXnJL6iGdv2q0C8cMh2R/Fhe/TVTT1qZ43B0iR+PX4TyKlpnx+jJpKNr382Y8qNTW3TtbHJ1si9WS5lUM=",        // iris signature - right eye
    'signature_left': "T9RCU24XtJNzS+XAb/66+DXpg3hFHR+nyUGdqv7Sq1TndakDIm1lQapChbf5L4YNSLkxqWx61glb4RIqafZBQW+PYwd6P0cvRPpN52Mmn9UihJ8wF2sdNyTgJv8cX/38aCdYSgmy5UJabUi5bArUtIEqeepEgaaxeYAGaQD9O1yi4ufn1Ca8DdzRupyDZK5vD9BV8tQX9on1U8cWUYgTQxgrnQ7vasjxOGsCrFSffa02A5ru6wudzuv8i4pqgtAijVI/mYN2iyigmdVXWdhJ6GnJHfWE41z4Qa4vqE1v7Z9pUrlaBF3S+ptE6j/XWxDRcUkospyTbGdvIoUljFLe4gARI3Ry+m4AMjzuuSIAsflxjU1lO7UnxUEaFoiNKocYxViNZFKVp9vTa46lEt2leBJEPJaoxyDg4WKwTdLi2NPRfAJWLdRSPaiBBbpkOUmUBepXtaJMrfcvwaoH1rV79xPOaRdSzlcJg23R2R7a9uZfBdE8MfiLvfSyGmubqDNk0Nhd9xSe4VFEUbJfoYAlt8g2i1Lcz5J+88kE4Xi15hfwyCmvhvx0HpVz43HHeXr0qXbcXfu1TB+C91hL2XvKKKqyoVtaGZa7bhgqS14iBad2lFJnrFcX2X1g2MgjRsyG8FlHUhBfKl8seMfIAgFpLKTTY4ju0RdDCYGTs+0dyPD3NQjAA/A78zIUnYtcr5Xt+hfLq11/0BYbz4l3S3UUXshddPH0kdhO5EYF4cUmQUBqnTwpMekmtwWZv1LFKbXzjob2nKup2zIKpUclt9l38CXQNE/HjnPRO4p5WBEq4GgIcAkztpgPQV13Cka/Meddpn2jXrczAVGd4wYCpj787oxYjVTl1yfL3wUk2NZFU37cWKuKfgmsGBz6A0sb+6UJNdjYoow6441ieDoro3i9yy6HyqTN5864UOjnKQTslUjM9zDdeaoqhw8KJ9odSNlFRDJmVJMmGri/eBbpaFPFzKCNH0KrBbGIF1pWS3KVAYo=",        // iris signature - left eye
    'param1': '' }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/identify?access_token=' + bearer;
  var payload = '{ "iris_signature1": "' + params.signature_right 
    + '", "iris_signature2": "' + params.signature_left + '" }';
  
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
        match_results = JSON.stringify(json_response,null,2);
        $('#iris_identify').text(match_results);
      }
      else {
        //alert("Error (server status) - " + xmlhttp.responseText);
        $('#iris_identify').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

// performs 1:1 iris verification
// requires previously encoded Iris Signature for left/right eyes for verification (probes)
// requires a valid oauth2 bearer access_token (see above example)
function verify_iris(bearer) {
  var verify_results = null;
  $('#iris_verify').text('waiting for the response from https://iris-cloud.com...');
  $('#iris_verify').show(); // unhide div to show verification result
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
  'subject_id' : "cede356e-30b3-4441-91ae-700d164b6df2",
    'signature_right': "YTS39ZQ9ptHm0s5VWtQqxJIGrLgDedRfVWCvIuZoXBMpIkEEbxcsR8S3oyHCnwujO384SQviNA2Ud+nyEE7BmNdIALyqicDUS0fDuXdu+voCj++LXbi2G7HM3I/jrB7jVYET2t5yteDJ9nSDGH3np3aT7MVFYuxACMegG3iBHKStDhldAN4Iwd5K4s5ERbKtkuB6jQyDm4rgzy04YFAqgsvwkukgtJ4V+EOIoe7l49uEdDSFZwkessNsjfAGp9Ha1O9rtrJbSLlDOtzXObR3/QkW9sW03vOkoQSFise36Ug1AysjU/i2EMuixQ0UIJ/FPuvZ0MZ3uQN/wQCXTqNA9etXSLdjSnIkP8xV0jS4nsRu9Qe5COMaTY217Hg6xAm2obG0zYpDgXPcZOyOIE6LJTRyBddJQfzl9cJCCKTOhzzOyTJf4PuzrUU4rCh1mZH5dUqFKoAsJdtongRzok2E1LuzDO0wVbIa3p7X0JQvjDV0StkdPTCXCpAJTwwTQaA6v/Vd+osi0uWyPadvmna9lGLFXn4ePcL0W1YY53b7fChCfKOy0F/P5eTVBQSukdbcgDDy03jv6HgvnfW1urkXHwcLQm3AEntyOJIWvlF+2Ni8tgXyZQ74bBI7lxn429S9G6yq20PWaWWHSgL+rW+9TggL42VvxTO1xs2JDCgb9rhBKcp72651mAf33WP7RkXrkFiXllim1DxIHCRizaK+26v6jQqDrVUbQydYHSzFIGMGCcqUz9USIPTnW+iguW+zPYlsKliK3/ggr+QMDEts5pn5dYZpV0gC+gIX1zpMAg89WAtVqje9MC5g0OJHNSYUcHx8rHrvaodBA5aqfgp7inJAdB2lsW96OT+itnJBqxtmzICsc8NEJ/EkVmyEoF1WSiYYD3BIMmfBjsHtnD4pb+VDu6RXnJL6iGdv2q0C8cMh2R/Fhe/TVTT1qZ43B0iR+PX4TyKlpnx+jJpKNr382Y8qNTW3TtbHJ1si9WS5lUM=",  // right eye
    'signature_left': "T9RCU24XtJNzS+XAb/66+DXpg3hFHR+nyUGdqv7Sq1TndakDIm1lQapChbf5L4YNSLkxqWx61glb4RIqafZBQW+PYwd6P0cvRPpN52Mmn9UihJ8wF2sdNyTgJv8cX/38aCdYSgmy5UJabUi5bArUtIEqeepEgaaxeYAGaQD9O1yi4ufn1Ca8DdzRupyDZK5vD9BV8tQX9on1U8cWUYgTQxgrnQ7vasjxOGsCrFSffa02A5ru6wudzuv8i4pqgtAijVI/mYN2iyigmdVXWdhJ6GnJHfWE41z4Qa4vqE1v7Z9pUrlaBF3S+ptE6j/XWxDRcUkospyTbGdvIoUljFLe4gARI3Ry+m4AMjzuuSIAsflxjU1lO7UnxUEaFoiNKocYxViNZFKVp9vTa46lEt2leBJEPJaoxyDg4WKwTdLi2NPRfAJWLdRSPaiBBbpkOUmUBepXtaJMrfcvwaoH1rV79xPOaRdSzlcJg23R2R7a9uZfBdE8MfiLvfSyGmubqDNk0Nhd9xSe4VFEUbJfoYAlt8g2i1Lcz5J+88kE4Xi15hfwyCmvhvx0HpVz43HHeXr0qXbcXfu1TB+C91hL2XvKKKqyoVtaGZa7bhgqS14iBad2lFJnrFcX2X1g2MgjRsyG8FlHUhBfKl8seMfIAgFpLKTTY4ju0RdDCYGTs+0dyPD3NQjAA/A78zIUnYtcr5Xt+hfLq11/0BYbz4l3S3UUXshddPH0kdhO5EYF4cUmQUBqnTwpMekmtwWZv1LFKbXzjob2nKup2zIKpUclt9l38CXQNE/HjnPRO4p5WBEq4GgIcAkztpgPQV13Cka/Meddpn2jXrczAVGd4wYCpj787oxYjVTl1yfL3wUk2NZFU37cWKuKfgmsGBz6A0sb+6UJNdjYoow6441ieDoro3i9yy6HyqTN5864UOjnKQTslUjM9zDdeaoqhw8KJ9odSNlFRDJmVJMmGri/eBbpaFPFzKCNH0KrBbGIF1pWS3KVAYo=",  // left eye
    'param1': '' }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/verify?access_token=' + bearer;
  var payload = '{ "subject_id": "' + params.subject_id 
        + '", "iris_signature1": "' + params.signature_right
        + '", "iris_signature2": "' + params.signature_left + '" }';
  
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
        verify_results = JSON.stringify(json_response,null,2);    
        $('#iris_verify').text(verify_results);
      }
      else {
        $('#iris_verify').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

// performs iris signature comparison
// requires previously encoded Iris Signature for comparison
// requires a valid oauth2 bearer access_token (see above example)
function compare_signatures(bearer) {
  var compare_result = null;
  $('#iris_compare').text('waiting for the response from https://iris-cloud.com...');
  $('#iris_compare').show(); // unhide div to show comparison result
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'signature1': "DyPo75u9ZjBVflrenSYetF6UHLRhzT4cpGLNgqDfT/wpIkEEbxcsR8S3oyHCnwujO384SQviNA2Ud+nyEE7BmNdIALyqicDUS0fDuXdu+voCj++LXbi2G7HM3I/jrB7jVYET2t5yteDJ9nSDGH3np3aT7MVFYuxACMegG3iBHKStDhldAN4Iwd5K4s5ERbKtkuB6jQyDm4rgzy04YFAqgsvwkukgtJ4V+EOIoe7l49uEdDSFZwkessNsjfAGp9Ha1O9rtrJbSLlDOtzXObR3/QkW9sW03vOkoQSFise36Ug1AysjU/i2EMuixQ0UIJ/FPuvZ0MZ3uQN/wQCXTqNA9etXSLdjSnIkP8xV0jS4nsRu9Qe5COMaTY217Hg6xAm2obG0zYpDgXPcZOyOIE6LJTRyBddJQfzl9cJCCKTOhzzOyTJf4PuzrUU4rCh1mZH5dUqFKoAsJdtongRzok2E1LuzDO0wVbIa3p7X0JQvjDV0StkdPTCXCpAJTwwTQaA6v/Vd+osi0uWyPadvmna9lGLFXn4ePcL0W1YY53b7fChCfKOy0F/P5eTVBQSukdbcgDDy03jv6HgvnfW1urkXHwcLQm3AEntyOJIWvlF+2Ni8tgXyZQ74bBI7lxn429S9G6yq20PWaWWHSgL+rW+9TggL42VvxTO1xs2JDCgb9rhBKcp72651mAf33WP7RkXrkFiXllim1DxIHCRizaK+26v6jQqDrVUbQydYHSzFIGMGCcqUz9USIPTnW+iguW+zPYlsKliK3/ggr+QMDEts5pn5dYZpV0gC+gIX1zpMAg89WAtVqje9MC5g0OJHNSYUcHx8rHrvaodBA5aqfgp7inJAdB2lsW96OT+itnJBqxtmzICsc8NEJ/EkVmyEoF1WSiYYD3BIMmfBjsHtnD4pb+VDu6RXnJL6iGdv2q0C8cMh2R/Fhe/TVTT1qZ43B0iR+PX4TyKlpnx+jJpKNr382Y8qNTW3TtbHJ1si9WS5lUM=",
    'signature2': "xEyVpXnL6uahyuecZwGBbtdweo6kTqPCfhBEQU81WdB9WWHjPNnQ+nZ0YzNqUWxFIbZ8HgRe7yUlYNue3Ra5W2cbXoGH4AGhRukOioHYnaZpiYA+o0Gqx77OCaaslbhoiu/H1YCymsrANoCPYooalHeKYKlJVUhXX420Jr0CjwV4rIOzOm+hwwOd7D0A9U1MPCkET+yP4tLOQ80MSp4CSJX1pykm85nChqCKiD8vdLqAsS8/yTyBKqWUBte//XYKtqXwKNEckPK6jJ5iJC6FD1gMq/fhRRTJ5tzDRz7WnfxY57WOegQZM6FGZ7JSRcgUVJ3butuBmJ6P0gJDXjeRDNZOUZ+x5Ifq5CysBuDENUWoNX/+rHXAmUL9BdHuqVPOqzjUd+iYhASTc7olAU39OPnsv5+en6GgW//hw/xyojD6VEV2GW7F/B49BZ8wGFM5u8DN8pN1/zqYvElTG/Kmnh9myeim5B2FSglkwJjKoOYwZP+T/5fNtrr+wRZXQCgw904t7VRECwQ4dBSgjes0pD8O1wWZyGvUyH7RmkjuJCZRMI/9J2wQuu6599KbGR1xElogBoc4sdh/HMoD5VpSHiqHkeHp0OkUF9glomVjvt5tZ1M9jOecmeu3tyRPZcDZVV1wlJKDR+s3LVA5cpPqeSgSRXF0SChy/pxvI2vOrECxFcOI7eLDWssokzY+jDEJJhvaHZJUS4eKe+BT0fUxlygxykMK/N8DT1t5FyLtt9vBHrqJE1KVjb2/evmGmnWobSs+Gxj+97JbfkHOpyidmkg8lkNEoiAsOmXwFn23V4n3T3WpVbLti6CGm/Nj1M/lQBlld/RZjPA0TnmU9M4H0zXbGTpKd9RXLCFaVZFIX3IJS51+L4HYB1doIxNjV7+qvCCMa2wF0ZMuG+80OL0ATLj6Xudm7mKtztYaN1ePTpllSq9fNnJERNcOciEUILuupqa8DMlktmoVdXGjrUO4zhF2lr4yCwfQAGYY72YDTWM=",
    'param1': '' }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/compare?access_token=' + bearer;
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
        compare_result = JSON.stringify(json_response,null,2);    
        $('#iris_matched').html('IRIS Match Status: ' + json_response.status);
		$('#iris_similarity').html('Similarity Score: ' + json_response.similarity_score);
      }
      else {
        $('#iris_matched').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

// enrolls a new Subject with reference Iris Signatures
// requires previously encoded Iris Signature for left/right eyes for reference (gallary)
// requires a valid oauth2 bearer access_token (see above example)
function enroll_subject(bearer) {
  var enroll_result = null;
  $('#enroll_subject').text('waiting for the response from https://iris-cloud.com...');
  $('#enroll_subject').show(); // unhide div to show enroll result
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'subject_id' : "6b846c78-c066-8248-af8a-8f22c2106a94",
    'signature_right': "YTS39ZQ9ptHm0s5VWtQqxJIGrLgDedRfVWCvIuZoXBMpIkEEbxcsR8S3oyHCnwujO384SQviNA2Ud+nyEE7BmNdIALyqicDUS0fDuXdu+voCj++LXbi2G7HM3I/jrB7jVYET2t5yteDJ9nSDGH3np3aT7MVFYuxACMegG3iBHKStDhldAN4Iwd5K4s5ERbKtkuB6jQyDm4rgzy04YFAqgsvwkukgtJ4V+EOIoe7l49uEdDSFZwkessNsjfAGp9Ha1O9rtrJbSLlDOtzXObR3/QkW9sW03vOkoQSFise36Ug1AysjU/i2EMuixQ0UIJ/FPuvZ0MZ3uQN/wQCXTqNA9etXSLdjSnIkP8xV0jS4nsRu9Qe5COMaTY217Hg6xAm2obG0zYpDgXPcZOyOIE6LJTRyBddJQfzl9cJCCKTOhzzOyTJf4PuzrUU4rCh1mZH5dUqFKoAsJdtongRzok2E1LuzDO0wVbIa3p7X0JQvjDV0StkdPTCXCpAJTwwTQaA6v/Vd+osi0uWyPadvmna9lGLFXn4ePcL0W1YY53b7fChCfKOy0F/P5eTVBQSukdbcgDDy03jv6HgvnfW1urkXHwcLQm3AEntyOJIWvlF+2Ni8tgXyZQ74bBI7lxn429S9G6yq20PWaWWHSgL+rW+9TggL42VvxTO1xs2JDCgb9rhBKcp72651mAf33WP7RkXrkFiXllim1DxIHCRizaK+26v6jQqDrVUbQydYHSzFIGMGCcqUz9USIPTnW+iguW+zPYlsKliK3/ggr+QMDEts5pn5dYZpV0gC+gIX1zpMAg89WAtVqje9MC5g0OJHNSYUcHx8rHrvaodBA5aqfgp7inJAdB2lsW96OT+itnJBqxtmzICsc8NEJ/EkVmyEoF1WSiYYD3BIMmfBjsHtnD4pb+VDu6RXnJL6iGdv2q0C8cMh2R/Fhe/TVTT1qZ43B0iR+PX4TyKlpnx+jJpKNr382Y8qNTW3TtbHJ1si9WS5lUM=",  // right eye
    'signature_left': "T9RCU24XtJNzS+XAb/66+DXpg3hFHR+nyUGdqv7Sq1TndakDIm1lQapChbf5L4YNSLkxqWx61glb4RIqafZBQW+PYwd6P0cvRPpN52Mmn9UihJ8wF2sdNyTgJv8cX/38aCdYSgmy5UJabUi5bArUtIEqeepEgaaxeYAGaQD9O1yi4ufn1Ca8DdzRupyDZK5vD9BV8tQX9on1U8cWUYgTQxgrnQ7vasjxOGsCrFSffa02A5ru6wudzuv8i4pqgtAijVI/mYN2iyigmdVXWdhJ6GnJHfWE41z4Qa4vqE1v7Z9pUrlaBF3S+ptE6j/XWxDRcUkospyTbGdvIoUljFLe4gARI3Ry+m4AMjzuuSIAsflxjU1lO7UnxUEaFoiNKocYxViNZFKVp9vTa46lEt2leBJEPJaoxyDg4WKwTdLi2NPRfAJWLdRSPaiBBbpkOUmUBepXtaJMrfcvwaoH1rV79xPOaRdSzlcJg23R2R7a9uZfBdE8MfiLvfSyGmubqDNk0Nhd9xSe4VFEUbJfoYAlt8g2i1Lcz5J+88kE4Xi15hfwyCmvhvx0HpVz43HHeXr0qXbcXfu1TB+C91hL2XvKKKqyoVtaGZa7bhgqS14iBad2lFJnrFcX2X1g2MgjRsyG8FlHUhBfKl8seMfIAgFpLKTTY4ju0RdDCYGTs+0dyPD3NQjAA/A78zIUnYtcr5Xt+hfLq11/0BYbz4l3S3UUXshddPH0kdhO5EYF4cUmQUBqnTwpMekmtwWZv1LFKbXzjob2nKup2zIKpUclt9l38CXQNE/HjnPRO4p5WBEq4GgIcAkztpgPQV13Cka/Meddpn2jXrczAVGd4wYCpj787oxYjVTl1yfL3wUk2NZFU37cWKuKfgmsGBz6A0sb+6UJNdjYoow6441ieDoro3i9yy6HyqTN5864UOjnKQTslUjM9zDdeaoqhw8KJ9odSNlFRDJmVJMmGri/eBbpaFPFzKCNH0KrBbGIF1pWS3KVAYo=",  // left eye
    // below are optional columns that can be saved along with enrollments
    // these columns needs to be defined in data store configuration file,
    // omit them if your installation does not need them
    "first_name": "John",
    "last_name": "Doe", 
    "date_of_birth": "1991-03-13",
    "xoptional": "<hello>world</hello>",
    "face_image": "iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAC3ElEQVR42u2ah4ojMQyG5/2fLD0hhUB6J733Nss3MMexR45sIlsesj8Ylt2sR/rikWXJXiwW822MTCbjNxoNfzwe++v12j8ej/75fA4GP/M7/sZn+KwtuzzTD6hUKoFz9/vdf1Z8lv+pVqvRBVAsFv3tdvu004/EHIVCIVoAWq2Wf7vd3nY+FHMxZyQA9Pt9Mce/i7mdBsC3ZFrNZtNNALynksv+kXhGPp93D8BmszHufCh2CKcAsNXZVrlcdgfAcrm0DmA+n7sBgKztJ0mOlIgFyWRSH0CtVrPufChePXUAw+FQDcBgMNAHsFqt1ABIxIG3AXCS0xLnBHUA1+tVDQDw1QHYyP6cBvDxK+B0OqkBcCIGSBQ9XtVisdAHMJvN1ACQg6gD6PV6agAkqkRvA9A4CYYqlUr6ADgMaYjtN5FI6ANg7Pd76wCkiiIiADQORN1u1x0AVGdsS6pXIAIgHo8HLS5bksgARQHYfg3Yep0DwJK0IcpvuVzOPQAMG6VxCrCSNosCsFEflKgDGgNAMDR5OpQMfkYAMNrttjEA0n1BIwBIT01siawsidTXOACGiS5xZO4HhLFAslrMXMwZGQDSq8DUt28UQDqdFukZMofJW2NGb4kdDoe3ARD8TNroPAATe78VAKlUSqRpEtlXgM6tlCSqv1YBkLFJXppgLjJM5wGwVKfTqZjj30UPIpvNugeAWsBoNLLSJySucKmaq7hqAMjKqMlzc1Mi0r8qnk2swZZXM8WnAHAI4SGdTifox2l2hB8Jm7ANG7H12YPTQwAsMWpv1N817wC8KmzGdnz43+vyD4B6va7S6DAtfMK3hwCI4Davu2oJH/9OrLwwa9MMZraFr/j8B8BkMtG2ybrwOQBAjV3jqqu2wv6CZ7KI6brw3dO84qItfPd2u522HWrCd0/zmpu28N1zMa21JXz3opjmSgnfvU/cAkPh+y+Aj38FiISXy+UjB75/Adp+CJ9q5f4rAAAAAElFTkSuQmCC"
}; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/subject/' + params.subject_id + '?access_token=' + bearer;
  var payload = '{ "iris_signature1": "' + params.signature_right
        + '", "iris_signature2": "' + params.signature_left
        + '", "first_name": "' + params.first_name
        + '", "last_name": "' + params.last_name
        + '", "date_of_birth": "' + params.date_of_birth
        + '", "xoptional": "' + params.xoptional
        + '", "face_image": "' + params.face_image + '" }';
  
  // construct payload
  var xmlhttp = new XMLHttpRequest();

  // this is a put call
  xmlhttp.open('PUT', url, true);
  // add required headers
  xmlhttp.setRequestHeader('Content-Type', 'application/json');
  xmlhttp.setRequestHeader('Accept', 'application/json');
  
  xmlhttp.send(payload);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {
        var json_response = JSON.parse(xmlhttp.responseText);
        enroll_result = JSON.stringify(json_response,null,2);    
        $('#enroll_subject').text(enroll_result);
      }
      else {
        $('#enroll_subject').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

// deletes a new Subject from enrollment data store
// requires a valid oauth2 bearer access_token (see above example)
function delete_subject(bearer) {
  var delete_result = null;
  $('#delete_subject').text('waiting for the response from https://iris-cloud.com...');
  $('#delete_subject').show(); // unhide div to show delete result
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'subject_id' : "d9db65c1-63ec-9a41-a1fa-87e2d5c5b4ad"
  }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/subject/' + params.subject_id + '?access_token=' + bearer;
  // payload not required
  var payload = null;
  
  // construct payload
  var xmlhttp = new XMLHttpRequest();

  // this is a put call
  xmlhttp.open('DELETE', url, true);
  // add required headers
  xmlhttp.setRequestHeader('Content-Type', 'application/json');
  xmlhttp.setRequestHeader('Accept', 'application/json');
  
  xmlhttp.send(payload);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {
        var json_response = JSON.parse(xmlhttp.responseText);
        delete_result = JSON.stringify(json_response,null,2);    
        $('#delete_subject').text(delete_result);
      }
      else {
        $('#delete_subject').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

// retrieves an existing Subject from the enrollment data store
// requires a valid oauth2 bearer access_token (see above example)
function get_subject(bearer) {
  var get_result = null;
  $('#get_subject').text('waiting for the response from https://iris-cloud.com...');
  $('#get_subject').show(); // unhide div to show subject details
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 
    'subject_id' : "d9db65c1-63ec-9a41-a1fa-87e2d5c5b4ad"
  }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/subject/' + params.subject_id + '?access_token=' + bearer;
  // payload not required
  var payload = null;
  
  // construct payload
  var xmlhttp = new XMLHttpRequest();

  // this is a put call
  xmlhttp.open('GET', url, true);
  // add required headers
  xmlhttp.setRequestHeader('Content-Type', 'application/json');
  xmlhttp.setRequestHeader('Accept', 'application/json');
  
  xmlhttp.send(payload);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {
        var json_response = JSON.parse(xmlhttp.responseText);
        if (json_response)
          get_result = JSON.stringify(json_response,null,2);
        else 
          get_result = xmlhttp.responseText;
        $('#get_subject').text(get_result);
      }
      else {
        $('#get_subject').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

// checks for the existance of a given subject in enrollment data store
// requires a valid oauth2 bearer access_token (see above example)
function exists_subject(bearer) {
  $('#exists_subject').text('waiting for the response from https://iris-cloud.com...');
  $('#exists_subject').show(); // unhide div to show subject's existance
  var params = {
    'host': "iris-cloud.com:8188/uniqbio/irisserver", 

    'subject_id' : "6b846c78-c066-8248-af8a-8f22c2106a94"
  }; // parameters

  // service endpoint
  var url = 'https://' + params.host + '/subject/' + params.subject_id + '?access_token=' + bearer;
  // payload not required
  var payload = null;
  
  // construct payload
  var xmlhttp = new XMLHttpRequest();

  // this is a HEAD call
  xmlhttp.open('HEAD', url, true);
  // add required headers
  xmlhttp.setRequestHeader('Content-Type', 'application/json');
  xmlhttp.setRequestHeader('Accept', 'application/json');
  
  xmlhttp.send(payload);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4) {
      if ( xmlhttp.status == 200) {
        $('#exists_subject').text("subject id=" + params.subject_id + " exist");
      }
      else if ( xmlhttp.status == 404) { // not found
        $('#exists_subject').text("subject id=" + params.subject_id + " does NOT exist");
      }
      else {
        $('#exists_subject').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};
</script>

</head>
<body class="w3-light-grey" onLoad="show_status();">
<!-- Navigation Bar -->
<div id="nav" class="w3-top">
	<div class="w3-bar w3-white w3-card-2">
	  <a href="#" class="w3-bar-item w3-button w3-mobile"><img src="images/uka.jpeg" width="35px"/><span class="w3-bar-item w3-button w3-padding-large w3-hide-medium w3-hide-large w3-right" href="javascript:void(0)" onClick="myFunction()" title="Toggle Navigation Menu"><i class="fa fa-bars"></i></span></a>
	  <a href="#blog" class="w3-bar-item w3-button w3-mobile w3-hide-small">Blog</a>
	  <a href="#about" class="w3-bar-item w3-button w3-mobile w3-hide-small">About</a>
	  <a href="#contact" class="w3-bar-item w3-button w3-mobile w3-hide-small">Contact</a>
	  <a href="#donate" class="w3-bar-item w3-button w3-round w3-right w3-red w3-mobile w3-hide-small" style="margin:5px;">Donate</a>
	  <a href="javascript:;" onClick="show_box2();" class="w3-bar-item w3-button w3-right w3-light-grey w3-mobile w3-hide-small">Enroll details</a>
	</div>
</div>
<div id="navDemo" class="w3-bar-block w3-white w3-hide w3-hide-large w3-hide-medium w3-top" style="margin-top:46px">
  <a href="javascript:;" onClick="show_box2();" class="w3-bar-item w3-button w3-light-grey w3-padding-large">Enroll details</a>
  <a href="#donate" class="w3-bar-item w3-button w3-round w3-red w3-padding-large"  style="margin:5px;">Donate</a>
  <a href="#blog" class="w3-bar-item w3-button w3-padding-large">Blog</a>
  <a href="#about" class="w3-bar-item w3-button w3-padding-large">About</a>
  <a href="#contact" class="w3-bar-item w3-button w3-padding-large">Contact</a>
</div>
<!--<script type="text/javascript" src="http://www.nihilogic.dk/labs/canvas2image/canvas2image.js"></script>-->

<script>
		function myFunction() {
			var x = document.getElementById("navDemo");
			
			if (x.className.indexOf("w3-show") == -1) {
				x.className += " w3-show";
				
			} else { 
				x.className = x.className.replace(" w3-show", "");
				
			}
		}
		/**
		// Put event listeners into place
		window.addEventListener("DOMContentLoaded", function() {
			// Grab elements, create settings, etc.
            var canvas = document.getElementById('canvas');
            var context = canvas.getContext('2d');
            var video = document.getElementById('video');
            var mediaConfig =  { video: true };
            var errBack = function(e) {
            	console.log('An error has occurred!', e)
            };

			// Put video listeners into place
            if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia(mediaConfig).then(function(stream) {
                    video.src = window.URL.createObjectURL(stream);
                    video.play();
                });
            }

            /* Legacy code below! */
			/**
            else if(navigator.getUserMedia) { // Standard
				navigator.getUserMedia(mediaConfig, function(stream) {
					video.src = stream;
					video.play();
				}, errBack);
			} else if(navigator.webkitGetUserMedia) { // WebKit-prefixed
				navigator.webkitGetUserMedia(mediaConfig, function(stream){
					video.src = window.webkitURL.createObjectURL(stream);
					video.play();
				}, errBack);
			} else if(navigator.mozGetUserMedia) { // Mozilla-prefixed
				navigator.mozGetUserMedia(mediaConfig, function(stream){
					video.src = window.URL.createObjectURL(stream);
					video.play();
				}, errBack);
			}

			// Trigger photo take
			document.getElementById('snap').addEventListener('click', function() {
				context.drawImage(video, 0, 0, 320, 240);
				to_image();
			});
		}, false);
**/

		function to_image(){
                var canvas = document.getElementById("canvas");
                document.getElementById("image").src = canvas.toDataURL('image/jpeg', 1.0);
				var baseimg = document.getElementById("image").src;
				baseimg = baseimg.replace(/^data:image\/(png|jpg|jpeg);base64,/, "");
				document.getElementById("base").innerHTML = "";
				document.getElementById("base").innerHTML = baseimg;
				gen_iris_signature($('#server_auth').text());
                //Canvas2Image.saveAsJPG(canvas);
            }
		function loadImageFileAsURL()
		{
			var filesSelected = document.getElementById("inputFileToLoad").files;
			if (filesSelected.length > 0)
			{
				var fileToLoad = filesSelected[0];
		 
				var fileReader = new FileReader();
		 
				fileReader.onload = function(fileLoadedEvent) 
				{
					var textAreaFileContents = document.getElementById("base");
					var baseimg = fileLoadedEvent.target.result;
			 		baseimg = baseimg.replace(/^data:image\/(png|jpg|jpeg);base64,/, "");
					textAreaFileContents.innerHTML = "";
					textAreaFileContents.innerHTML = baseimg;
					document.getElementById("image").src = 'data:image/jpeg;base64,'+ baseimg;
					gen_iris_signature($('#server_auth').text());
				};
		 
				fileReader.readAsDataURL(fileToLoad);
			}
		}
		
		function show_box()
		{
			document.getElementById('id01').style.display = 'block';
			document.getElementById("base").innerHTML = "";
		}
		function show_box2()
		{
			document.getElementById('id02').style.display = 'block';
			document.getElementById("base").innerHTML = "";
		}
		function close_box(){
			document.getElementById('id01').style.display = '';
			document.getElementById('id02').style.display = '';
			document.getElementById("base").innerHTML = "";
			document.getElementById("iris_sign_id").innerHTML = "";
		}
		/**
		// Put event listeners into place
		window.addEventListener("DOMContentLoaded", function() {
			// Grab elements, create settings, etc.
            var canvas = document.getElementById('canvas2');
            var context = canvas.getContext('2d');
            var video = document.getElementById('video2');
            var mediaConfig =  { video: true };
            var errBack = function(e) {
            	console.log('An error has occurred!', e);
            };

			// Put video listeners into place
            if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia(mediaConfig).then(function(stream) {
                    video.src = window.URL.createObjectURL(stream);
                    video.play();
                });
            }

            /* Legacy code below! */
			/**
            else if(navigator.getUserMedia) { // Standard
				navigator.getUserMedia(mediaConfig, function(stream) {
					video.src = stream;
					video.play();
				}, errBack);
			} else if(navigator.webkitGetUserMedia) { // WebKit-prefixed
				navigator.webkitGetUserMedia(mediaConfig, function(stream){
					video.src = window.webkitURL.createObjectURL(stream);
					video.play();
				}, errBack);
			} else if(navigator.mozGetUserMedia) { // Mozilla-prefixed
				navigator.mozGetUserMedia(mediaConfig, function(stream){
					video.src = window.URL.createObjectURL(stream);
					video.play();
				}, errBack);
			}

			// Trigger photo take
			document.getElementById('snap2').addEventListener('click', function() {
				context.drawImage(video, 0, 0, 320, 240);
				to_image2();
			});
		}, false);
**/

		function to_image2(){
                var canvas2 = document.getElementById("canvas2");
                document.getElementById("image2").src = canvas2.toDataURL('image/jpeg', 1.0);
				var baseimg2 = document.getElementById("image2").src;
				baseimg2 = baseimg2.replace(/^data:image\/(png|jpg|jpeg);base64,/, "");
				document.getElementById("base").innerHTML = "";
				
				document.getElementById("base").innerHTML = baseimg2;
				document.getElementById("image2").src = 'data:image/jpeg;base64,'+ baseimg2;
				gen_iris_signature($('#server_auth').text());
                //Canvas2Image.saveAsJPG(canvas);
            }
		function loadImageFileAsURL2()
		{
			var filesSelected = document.getElementById("inputFileToLoad2").files;
			if (filesSelected.length > 0)
			{
				var fileToLoad = filesSelected[0];
		 
				var fileReader = new FileReader();
		 
				fileReader.onload = function(fileLoadedEvent) 
				{
					var textAreaFileContents = document.getElementById("base");
					var baseimg2 = fileLoadedEvent.target.result;
			 		baseimg2 = baseimg2.replace(/^data:image\/(png|jpg|jpeg);base64,/, "");
					textAreaFileContents.innerHTML = "";
					
					textAreaFileContents.innerHTML = baseimg2;
					gen_iris_signature($('#server_auth').text());
				};
				fileReader.readAsDataURL(fileToLoad);
			}
		}
	</script>
<div id="id01" class="w3-modal">
	<div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-width:60%">
	
	  <div class="w3-center"><br>
		<span onClick="close_box()" class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
		<!--<img src="img_avatar4.png" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top"> -->
	  </div>
	  <div align="center">
		<video id="video" width="320px" height="240px" autoplay></video>
		<span>
			<button id="snap" type="button" class="w3-button w3-blue">Snap Eye</button>
			<b>Or</b>
			<label class="w3-button w3-blue">
				<span>
					Upload
				</span>
			<input type="file" id="inputFileToLoad" style="display:none;" onChange="loadImageFileAsURL();"/>
			</label>
		</span>
		
		<canvas id="canvas" width="320px" height="240px" style="display:none"></canvas>
		<img id="image" src="" width="320px"><br><br>
		<b><span id="iris_matched"></span></b><br>
		<b><span id="iris_similarity"></span></b><br>
		<b><span id="mother_name"></span></b><br>
		<b><span id="bhamashah"></span></b><br>
		<b><span id="address"></span></b><br>
		<b><span id="mobile"></span></b>
		
	  </div>
	  <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
		<button onClick="close_box()" type="button" class="w3-button w3-red">Cancel</button>
		<!--<span class="w3-right w3-padding w3-hide-small">Forgot <a href="#">password?</a></span>-->
	  </div>
		
	</div>
</div>

<div id="id02" class="w3-modal">
	<div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-width:60%">
	
	  <div class="w3-center"><br>
		<span onClick="close_box()" class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
		<!--<img src="img_avatar4.png" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top"> -->
	  </div>
	  <form class="w3-container">
		  <div align="center">
			<video id="video2" width="320px" height="240px" autoplay></video>
			<span>
				<button id="snap2" type="button" class="w3-button w3-blue">Snap Eye</button>
				<b>Or</b>
				<label class="w3-button w3-blue">
					<span>
						Upload
					</span>
				<input type="file" id="inputFileToLoad2" style="display:none;" onChange="loadImageFileAsURL2();"/>
				</label>
			</span>
			
			<canvas id="canvas2" width="320px" height="240px" style="display:none"></canvas>
			<img id="image2" src="" width="320px">
			<span id="iris_add_success"></span>
			<span id="iris_add_msg"></span>
			
		  </div>
	  
	  
		<div class="w3-section">
		  <label><b>Family Bhamashah ID</b></label>
		  <input class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Enter Bhamashah ID" value="" id="bhamashah_id" required>
		  <label><b>Child Birth Mark (Option)</b></label>
		  <input class="w3-input w3-border" type="text" placeholder="Enter any birth mark of child" id="birth_mark" name="birth_mark" required>
		  <button onClick="add_iris()" class="w3-button w3-block w3-green w3-section w3-padding" type="button">Submit</button>
		  <!--<input class="w3-check w3-margin-top" type="checkbox" checked="checked"> Remember me-->
		</div>
	  </form>
	  <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
		<button onClick="close_box()" type="button" class="w3-button w3-red">Cancel</button>
		<!--<span class="w3-right w3-padding w3-hide-small">Forgot <a href="#">password?</a></span>-->
	  </div>
		
	</div>
</div>

<!-- Header -->
<header class="w3-display-container w3-content" style="max-width:1500px;">
  <img class="w3-image" src="images/lostchild.jpg" alt="The lost child" style="100%">
  <span id="hidden_info" style="display:none">
  	<pre id="server_auth"></pre>
	<pre id="server_status"></pre>
	<pre id="base"></pre>
	<pre id="iris_sign_id"></pre>
	<pre id="iris_img"></pre>
  </span>
  <div class="w3-center w3-display-left w3-padding w3-col l6 m8">
    <div class="w3-container w3-red">
      <h2><!--<i class="fa fa-bed w3-margin-right"></i>-->Welcome to UdayKiAasha</h2>
    </div>
	
	<br>
	<br>
	<button onClick="show_box();" class="w3-button w3-white w3-border w3-border-green w3-round-large">Upload IRIS Pattern</button>
  </div>
</header>

<!-- Page content -->
<div class="w3-content" style="max-width:1532px;">
  <div class="w3-row-padding" id="about">
    <div class="w3-col l4 m7">
	<br><br>
      <h2>About</h2>
      <h4>UdayKiAasha drives technology innovation to fight against the loss of children's. We use biometric ID match to combat loss and help parents find their child.</h4>
    </div>
    <div class="w3-col l8 m5">
      <div id="googleMap" style="width:100%;height:400px;" class="w3-grayscale"></div>
    </div>
  </div>
  
  <div class="w3-row w3-large w3-center" style="margin:32px 0">
    <div class="w3-third"><i class="fa fa-map-marker w3-text-red"></i> Near KIET College, Ghaziabad, U.P, India.</div>
    <div class="w3-third"><i class="fa fa-phone w3-text-red"></i> Phone: +919454910640</div>
    <div class="w3-third"><i class="fa fa-envelope w3-text-red"></i> Email: support@udaykiaasha.org</div>
  </div>

  
  <div class="w3-container w3-padding-32 w3-black w3-opacity w3-card-2 w3-hover-opacity-off" style="margin:32px 0;">
    <h2>An eye for an eye can save the World</h2>
    <p>Subscribe for getting under the Government Initiative of Child Protection</p>
    <label>E-mail</label>
    <input class="w3-input w3-border" type="text" placeholder="Your Email address">
    <button type="button" class="w3-button w3-red w3-margin-top">Subscribe</button>
  </div>

  <div class="w3-container" id="contact">
    <h2>Contact</h2>
    <p>If you have any questions, do not hesitate to ask them.</p>
    <i class="fa fa-map-marker w3-text-red" style="width:30px"></i> Near KIET College, Ghaziabad, U.P, India.<br>
    <i class="fa fa-phone w3-text-red" style="width:30px"></i> Phone: +919454910640<br>
    <i class="fa fa-envelope w3-text-red" style="width:30px"> </i> Email: support@udaykiaasha.org<br>
    <form action="/action_page.php" target="_blank">
      <p><input class="w3-input w3-padding-16 w3-border" type="text" placeholder="Name" required name="Name"></p>
      <p><input class="w3-input w3-padding-16 w3-border" type="text" placeholder="Email" required name="Email"></p>
      <p><input class="w3-input w3-padding-16 w3-border" type="text" placeholder="Message" required name="Message"></p>
      <p><button class="w3-button w3-black w3-padding-large" type="submit">SEND MESSAGE</button></p>
    </form>
  </div>

<!-- End page content -->
</div>

<!-- Footer -->
<footer class="w3-padding-32 w3-black w3-center w3-margin-top">
  <h5>Find Us On</h5>
  <div class="w3-xlarge w3-padding-16">
    <i class="fa fa-facebook-official w3-hover-text-indigo"></i>
    <i class="fa fa-instagram w3-hover-text-purple"></i>
    <i class="fa fa-snapchat w3-hover-text-yellow"></i>
    <i class="fa fa-pinterest-p w3-hover-text-red"></i>
    <i class="fa fa-twitter w3-hover-text-light-blue"></i>
    <i class="fa fa-linkedin w3-hover-text-indigo"></i>
  </div>
  <p>Powered by <a href="https://www.helpmiii.com" target="_blank" class="w3-hover-text-green">HelpMiii</a></p>
</footer>

<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>

</body>
</html>
