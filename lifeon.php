<!DOCTYPE html>
<html>
<head>
<title>Unique Biometrics | Support | Lightning Iris Authentication Server Support</title>
<!-- for-mobile-apps -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Unique Biometrics, Iris Recognition, Iris Signature, Identification, Verification, Match, Authentication, fast, extreme, speed, SDK, OEM, REST, Android, Java, .NET" />
<script type="application/x-javascript"> 
	addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
	function hideURLbar(){ window.scrollTo(0,1); } 
</script>

<!-- //for-mobile-apps -->
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<!-- js -->
<script src="js/jquery-1.11.1.min.js"></script>
<?php 
$imagedata = file_get_contents("images/q1.jpg");
             // alternatively specify an URL, if PHP settings allow
			 
$base64 = base64_encode($imagedata);
echo $base64;
echo '<br>'. strlen($base64) .'<br>';

?>

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
    "data": "<?php echo $base64 ; ?>"\
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
        $('#img_processed').attr('src', 'data:image/gif;base64,' 
            + json_response.iris_image_processed.image_buffer.data);
        $('#img_processed').show();
		$('.prompt1').show();
      }
      else {
        //alert("Error (server status) - " + xmlhttp.responseText);
        $('#iris_sign').text("ERROR - " + xmlhttp.responseText);
      }
    }
  }
};

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
        $('#iris_compare').text(compare_result);
      }
      else {
        $('#iris_compare').text("ERROR - " + xmlhttp.responseText);
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
<!-- //js -->
<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Josefin+Sans:400,100,100italic,300,300italic,400italic,600,600italic,700,700italic' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Shadows+Into+Light' rel="stylesheet" type="text/css" />
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700,300' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="css/font-awesome.min.css">

<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
<!--/script-->
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".scroll").click(function(event){		
			event.preventDefault();
			$('html,body').animate({scrollTop:$(this.hash).offset().top},900);
		});
	});
</script>
</head>
	
<body>
<!-- banner -->
	<div class="banner1">
		<div class="container">
			<div class="topnav">
				<!-- header -->
				<div class="header">
					<div class="header-left">
						<a href="/index.html"> <img background="transparent" class="logo-image" src="/images/logo.png" alt=""/> </a>
					</div>
					<div class="header-left1">
						<span class="menu"><img src="/images/menu.png" alt=" "></span>
						<ul class="nav1">
								<li class="hvr-sweep-to-bottom"><a href="/index.html">Home<i class="glyphicon glyphicon-home" aria-hidden="true"></i></a></li>
							<li class="hvr-sweep-to-bottom"><a href="/products.html">Products<i class="glyphicon glyphicon-briefcase" aria-hidden="true"></i></a></li>
							<li class="hvr-sweep-to-bottom"><a href="/services.html">Services<i class="glyphicon glyphicon-wrench" aria-hidden="true"></i></a></li>
							<!--li class="hvr-sweep-to-bottom"><a href="/about.html">About<i class="glyphicon glyphicon-user" aria-hidden="true"></i></a></li-->
							<li class="hvr-sweep-to-bottom"><a href="/blog.html">Blog<i class="glyphicon glyphicon-edit" aria-hidden="true"></i></a></li>
							<li class="hvr-sweep-to-bottom active"><a href="/support.html">Support<i class="glyphicon glyphicon-user" aria-hidden="true"></i></a></li>
						</ul>
					</div>

					<div class="clearfix"> </div>
				</div>
			</div>
			<!-- script for menu -->
				<script> 
					$( "span.menu" ).click(function() {
					$( "ul.nav1" ).slideToggle( 300, function() {
					 // Animation complete.
					});
					});
				</script>
			<!-- //script for menu -->
		</div>
	</div>
<!-- //banner -->
	<div class="contact">
		<div class="container">	
			<h3><a href="/support.html">Support </a>> <a href="/lightning/">Lightning Iris Authentication Server Support </a></h3>
			<hr>	
			<div id="tbTop"><a href="https://www.google.com/accounts/ServiceLogin?service=ah&passive=true&continue=https://appengine.google.com/_ah/conflogin%3Fcontinue%3Dhttp://www.uniquebiometrics.com/lightning/topic%253Fid%253D5742636757417984&ltmpl=gm&shdf=ChMLEgZhaG5hbWUaB3dlYnNpdGUMEgJhaCIU-k70v3g7mxOx5_qNo4JFluHtX8coATIU0QZz5S851r8rW2030AUebgtijFA">Log in or register</a></div>
			
			<div style="float: right; margin-right: 2em">				
				<a href="/lightning/rss" title="RSS Feed">
				<i class="fa fa-rss-square fa-lg"></i>
				</a>
			</div>
			
			<div class="col-md-4">
				<br><br><div class="services-section1"> 	<div class="services-grids"> 		<div class="services-grid"> 			<img src="../images/icon2.png" class="img-responsive" alt=""> <br> 			<h4>Lightning Iris Authentication Server Support</h4> 			<p>Lightning Iris Authentication Server Installation, configuration and integrating with external Application/Solution</p> 		</div>					 		<div class="clearfix"></div> 	</div> </div>
			</div>
	
			<div class="col-md-8 contents">
				<div class="posts">
					<h3>Lightning Iris Authentication Server REST API Live Demo</h3>
					<hr>
									
					
						<a name="5124589889781760"></a>
							
						 <!-- not moderator -->
						
							
															
								
								
								<div class="post"><!-- live demo --> 			   <!-- <script src="js/sdk-samples.js"></script>  -->				We have setup an instance of the Lightning Iris Authentication Server on Microsoft Azure Cloud and accessible via our domain https://www.iris-cloud.com. This page gives you working code snippets in Javascript/jQuery and compatible with latest Web Browsers (Chrome, IE11, Edge, Safari) supporting HTML5. You an run these sample code snippets against live Lighting Iris Authentication Server hosted on https://iris-cloud.com:8181/uniqbio/irisserver/. These samples can be easily modified to run under any environment (.NET, Java, PHP, Python, Node.js etc.) of your liking. This shows how easy it is to integrate Iris Authentication capabilities to virtually any kind of Applications including purely browser based, iOS/Android, Windows 10 Universal Apps and others. Please ask questions in this forum related to Lightning Iris Authentication Server and questions regarding APIs and programming environment. We will make our very best effort to provide best possible support with a smile :) 				 				<hr> 				<h4>Checking the status of the Server</h4> 				<p>Following Javascript/jQuery can be used to retrieve status from the Lightning Iris Authentication Server.<br>NOTE - This API does not require oAuth2 <em>"Access Token"</em>.</p> 				 				<!-- placeholder for the sample script--> 				<pre id='code_server_status' style="white-space: pre-wrap; font-family:monospace;"></pre> 			 				<input type='button' onClick="show_status();" value="Show Server Status"/>  				<p>The API response shown below:</p> 				 				<!-- placeholder for server status response -->			 				<pre id="server_status" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				<!-- getting authorization token --> 				<hr> 				<div id='get_token'></div> 				<h4>Obtaining the oAuth2 Authorization Token </h4> 				<p> 				Making calls to Lighthning Iris Authentication Server requires a vaild oAuth2 <em>"Access Token"</em>. Following JavaScript can be used to obtain an oAuth2 <em>"Access Token"</em> from Lightning Iris Authentication Server. <em>"Access Token"</em> is valid until the expiration time or if the Server is restarted. You may reuse the token until API encounters "Not Authorized" - HTTP error 401/403. 				</p> 				 				<!-- placeholder for the sample script--> 				<pre id='code_access_token' style="white-space: pre-wrap;" ></pre> 				<input type='button' onClick="request_auth();" value="Request oAuth2 Access Token" style="background: #00ff80; color: #101010;"/>  				<br><p> 				The generated <b>Access Token</b> displayed below will be used by subsequent API calls 				</p> 				<!-- placeholder for the displaying the oAuth2 token --> 				<pre id="server_auth" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				<hr> 				 				<h4>Generating an Iris Signature (Encode) </h4> 				<p>Following JavaScript can be used to generate Iris Signature from an eye image. Images taken from Iris Scanners conforming to ISO/IEC 19794-6 Iris Image Standards are recommanded for optimal accuracy. The image of the eye containing the iris should meet following requirements in general: 				</p> 				<ul class="fa-ul"> 				<li><i class="fa-li fa fa-circle"></i>Image should be 8bit grayscale jpeg, bmp or raw pixel data. To reduce the payload size, we recommnd jpeg compression with quality set to 80</li>        				<li><i class="fa-li fa fa-circle"></i>Image size should be less than 720x520 pixels, cropped around the eye, with eye is in the center of the frame</li>        				<li><i class="fa-li fa fa-circle"></i>Diameter of the eye should be between 160 to 280 pixels</li>        				<li><i class="fa-li fa fa-circle"></i>Images should have good contrast between pupil/iris and iris/scalara boundary</li>          				<li><i class="fa-li fa fa-circle"></i>Free of excessive specular reflections on the iris</li>          				<li><i class="fa-li fa fa-circle"></i>Reflections from IR illumination should be inside the pupil bounds</li>          				</ul> 								 				<!-- placeholder for the sample script--> 				<pre id='code_iris_sign' class="code-style" style="white-space: pre-wrap;" ></pre> 		 				<input type='button' onClick="gen_iris_signature($('#server_auth').text());" value="Generate Iris Signature"/> <input type='button' onClick="$('#iris_sign').text(''); $('#iris_sign').hide(); $('#img_processed').hide(); $('.prompt1').hide();" value="Clear"/> 				 				<p>The processed iris image extracted from the API response will be displayed below:</p> 				<img id="img_processed" class="img-responsive" style="display: none;" src=""/> 				 				<p class="prompt1" style="display: none; font-weight: 500; margin-top: 1em;" >Click here to see server response data</p>		 				<pre id="iris_sign" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p> 				 				<!--identify API--> 				<hr> 				<h4>Identify an unknown Subject with Iris Signature </h4> 				<p>Following JavaScript can be used to perform an iris identification (1:N). </p> 				 				<!-- placeholder for the sample script--> 				<pre id='code_identify' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="identify_iris($('#server_auth').text());" value="Identify Subject"/> <input type='button' onClick="$('#iris_identify').text(''); $('#iris_identify').hide();" value="Clear"/> 				<p>The API response shown below:</p> 				<pre id="iris_identify" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p> 				<!-- verification --> 				<hr> 				<h4>Verify a Known Subject using Iris Signature (1:1)</h4> 				<p>Following JavaScript can be used to perform an iris verification (1:1). </p> 			 				<!-- placeholder for the sample script--> 				<pre id='code_verify' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="verify_iris($('#server_auth').text());" value="Verify Subject"/> <input type='button' onClick="$('#iris_verify').text(''); $('#iris_verify').hide();" value="Clear"/> 				 				<p>The API response shown below:</p>				 				<pre id="iris_verify" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p> 					 				<!-- compare --> 				<hr> 				<h4>Compare 2 Iris Signatures </h4> 				<p>Following JavaScript can be used to compare 2 Iris Signatures.</p> 				 				<!-- placeholder for the sample script--> 				<pre id='code_compare' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="compare_signatures($('#server_auth').text());" value="Compare 2 Iris Signatures"/> <input type='button' onClick="$('#iris_compare').text(''); $('#iris_compare').hide();" value="Clear"/> 				 				<p>The API response shown below:</p>				 				<pre id="iris_compare" class="code-style" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p> 				 				<!-- enroll --> 				<hr> 				<h4>Enroll a Subject</h4> 				<p>Following JavaScript can be used to enroll a new Subject.</p> 				 				<!-- placeholder for the sample script--> 				<pre id='code_enroll' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="enroll_subject($('#server_auth').text());" value="Enroll Subject"/> <input type='button' onClick="$('#enroll_subject').text(''); $('#enroll_subject').hide();" value="Clear"/> 				<p>The API response shown below:</p>				 				<pre id="enroll_subject" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p> 				 				<!-- exists subject --> 				<hr> 				<h4>Check for an existing Subject by Id</h4> 				<p>Following JavaScript can be used to check for existance of a given Subject in the enrollment data store.</p> 			 				<!-- placeholder for the sample script--> 				<pre id='code_exists_subject' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="exists_subject($('#server_auth').text());" value="Check Subject Existance"/> <input type='button' onClick="$('#exists_subject').text(''); $('#exists_subject').hide();" value="Clear"/> 				<p>The API response shown below:</p> 				<pre id="exists_subject" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p>				 				<!-- get subject --> 				<hr> 				<h4>Retrieve Enrollement Data </h4> 				<p>Following JavaScript can be used to get an existing Subject from the enrollment data store.</p> 			 				<!-- placeholder for the sample script--> 				<pre id='code_get_subject' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="get_subject($('#server_auth').text());" value="Get Subject"/> <input type='button' onClick="$('#get_subject').text(''); $('#get_subject').hide();" value="Clear"/> 				<p>The API response shown below:</p> 				<pre id="get_subject" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p>	 				 				<!-- delete --> 				<hr> 				<h4>Delete a Subject </h4> 				<p>Following JavaScript can be used to delete an existing Subject from the enrollment data store. </p> 				 				<!-- placeholder for the sample script--> 				<pre id='code_delete' style="white-space: pre-wrap;" ></pre> 			 				<input type='button' onClick="delete_subject($('#server_auth').text());" value="Delete Subject"/> <input type='button' onClick="$('#delete_subject').text(''); $('#delete_subject').hide();" value="Clear"/> 				<p>The API response shown below:</p>				 				<pre id="delete_subject" class="code-style" style="display: none; white-space: pre-wrap;"></pre> 				 				<p>An oAuth2 <a href="#get_token"><b>Access Token</b></a> is required to make the call.</p> 				 				<script> 					jQuery('#code_server_status').load("/examples/server_status.html", function () {}); 					jQuery('#code_access_token').load("/examples/get_auth.html", function () {}); 					jQuery('#code_iris_sign').load("/examples/gen_iris_signature.html", function () {}); 					jQuery('#code_identify').load("/examples/identify.html", function () {}); 					jQuery('#code_verify').load("/examples/verify.html", function () {}); 					jQuery('#code_compare').load("/examples/compare.html", function () {}); 					jQuery('#code_enroll').load("/examples/enroll.html", function () {}); 					jQuery('#code_delete').load("/examples/delete.html", function () {}); 					jQuery('#code_exists_subject').load("/examples/exists_subject.html", function () {}); 					jQuery('#code_get_subject').load("/examples/get_subject.html", function () {}); 					 					$('.prompt1').click(function(){						 						if($(this).text()=='Collapse'){ 							$('#iris_sign').hide(); 							$(this).text('Click here to see server response'); 						}else{ 							$('#iris_sign').show(); 							$(this).text('Collapse'); 						} 					});					 				</script></div>		
								
								<h3>Comments</h3>
								<hr>
								
								
							
						
						

	
					
						<a name="4697585314955264"></a>
							
						 <!-- not moderator -->
						
							
								
								<div class="comment-box">
															
								
								<div class="comments-info mytable">
									<span id="v" style="width: 48px;">
										<img src="/images/user_icon_small.png"/>
									</span>
									<div id="x">
										<div>
										Annon
										<i class="fa fa-clock-o"> </i>
										March 5th, 2016 6:29a.m.									
										</div>
									
										<div>
											
										</div>
									</div>															
								</div>
								
								
								<div class="post">Can I use this in a free App I am developing for Android?</div>		
								
								</div>
								
								
							
						
						

	
					
						<a name="6256367190933504"></a>
							
						 <!-- not moderator -->
						
							
								
								<div class="comment-box">
															
								
								<div class="comments-info mytable">
									<span id="v" style="width: 48px;">
										<img src="/images/user_icon_small.png"/>
									</span>
									<div id="x">
										<div>
										Unique Biometrics Support Team
										<i class="fa fa-clock-o"> </i>
										March 6th, 2016 7:34p.m.									
										</div>
									
										<div>
																			
												http://www.twitter.com/uidbiometrics, 
											
										</div>
									</div>															
								</div>
								
								
								<div class="post"><div class="comment-box"> 	<div class="comments-info mytable"> 		<span id="v" style="width: 48px;"> 			<img src="/images/user_icon_small.png"> 		</span> 		<div id="x"> 			<div> Annon <i class="fa fa-clock-o"> </i> March 5th, 2016 6:29a.m.</div> 		</div> 	</div> 	<div class="post">Can I use this in a free App I am developing for Android? </div>	 </div><br> <p>If your question is about using our demo server hosted on https://iris-cloud.com in your App for free - the answer is NO for following reasons. This server is meant for showcasing our technology to potential customers (Partners and System Integrators). The data on the server will be periodically purged and no guaranteed availability around the clock. We keep all data enrolled on this server secured with industry standard AES 256 bit Encryption and we do not intend to share the data with anyone outside our organization. But we disclaim any responsibility arising from the future cyber attacks or hacks that may arise from unforeseen incidents. Our intent is to sell the commercial software licenses. The Iris Authentication Server will be hosted by our customers in their own data centers or in private cloud owned by our customers.</p> <p>Our Iris Authentication Software works best when the Iris images are taken with near-infrared (NIR) light. NIR point light source (usually NIR LED), combined with a narrow IR band-pass filter is recommended to reduce reflections from the surroundings which may otherwise dominate the iris region of the eye. NIR light also can look through the color pigments that block iris patterns in dark colored eyes. The image, if taken with ordinary mobile phone camera, neither has NIR LED flash nor band-pass filter. However, our algorithm is known to work well if images taken with an ordinary mobile camera clearly displays the iris patterns. See above "Generate Iris Signature" for general requirements for iris images acceptable to our algorithm.</p> <p>I hope this answers your question. If you want to evaluate the server, please let us know by emailing us at support@uniquebiometrics.com. We suggest you try our IriStick Edition of our software. It provides the same REST APIs documented above and provides seamless upgrade path to more scalable Iris Authentication Server Editions.</p></div>		
								
								</div>
								
								
							
						
						

	
					

					<div class="topic-nav">
						

						<a href="/lightning/">
							<i style="float: right; padding: 1em" class="fa fa-list fa-lg"><span> Other recent topics</span></i>						
						</a>

						
						<a href="/lightning/post?id=5742636757417984">
							<i style="float: right; padding: 1em" class="fa fa-edit fa-lg"><span> Post to this topic</span></i>
						</a>
												
					</div>
				</div>
			</div>
		</div>
	</div>
	<hr>

	<!-- footer -->
		<!-- footer -->
	<!-- privacy policy section -->
	<div id="privacy_policy" class="privacy-policy">
	
	</div>
			
	<!-- footer -->
	<div class="footer ftr-copywrite">
		<div class="container">				 	
			<div class="footer-left ftr_navi ftr">
				<ul>
					<li><a href="/index.html">Home</a></li>
					<li><a href="/products.html">Products</a></li>
					<li><a href="/services.html">Services</a></li>						
					<li><a href="/blog.html">Blog</a></li>
					<li><a href="/support.html">Support</a></li>
				</ul>
			</div>

			<div class="footer-right ftr-logo">
				<a href="/index.html"><h4>Unique Biometrics<br><span> Iris Authentication Technologies</span></h4></a>
				<ul>
					<li><a href="https://twitter.com/uidbiometrics" class="f"> </a></li>
					<li><a href="#" class="f1"> </a></li>
				</ul>
			</div>
			<div class="clearfix"> </div>
		</div>
		
		<div>		
			<p>&copy; 2016 Unique ID Solutions, Inc. All Rights Reserved.
			
			<script type="text/javascript">
			function toggle_visibility(id) {
			   var e = document.getElementById(id);
			   if (e != null){
				   if(e.style.display == 'block')
					  e.style.display = 'none';
				   else
					  e.style.display = 'block';
				}
			}
			function showPrivacyPolicy() {
				var e = document.getElementById('privacy_policy');
				if (e != null){
					e.style.display = 'block';
					jQuery('#privacy_policy').load("/privacy.html", function () {});
				}
			}
			</script>			
			<a href="#privacy_policy" onClick="showPrivacyPolicy();" style="padding-left: 20px;"> Privacy Policy</a></p>			
		</div>
	</div>
<script type="text/javascript">
		$(document).ready(function() {
				/*
				var defaults = {
				containerID: 'toTop', // fading element id
				containerHoverID: 'toTopHover', // fading element hover id
				scrollSpeed: 1200,
				easingType: 'linear' 
				};
				*/
		$().UItoTop({ easingType: 'easeOutQuart' });
});
</script>
<a href="#to-top" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>	<!--footer-->
	<!--footer-->
</body>
</html>

