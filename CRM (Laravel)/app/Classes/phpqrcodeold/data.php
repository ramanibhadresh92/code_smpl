<!DOCTYPE html>
<html>
<head>
 <title>Free Online QR Codes Generator - Seegatesite.com</title>
 <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
 <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">

<div class="container content">

<div class="row">

<div class="col-xs-12 col-md-6">

<h3 class="header-h3">QR CODE GENERATOR</h3>


<div class="tab-content">

<div class="form-group">
 <label class="label-form">Free text</label>
 <input type="text" class="form-control" id="data" placeholder="Free text for share">
 </div>

 </div>

 </div>

<!--
<div class="col-xs-12 col-md-6">-->

<div class="col-xs-12 col-md-6">

<h3 class="header-h3">QR CODE RESULT</h3>

 <input type="hidden" class="form-control" id="hiddendata">

<div id="hasil"></div>

 </div>

<!--
<div class="col-xs-12 col-md-6">-->
 </div>

<!--row-->
 </div>

<!-- container content-->
 </div>

<!--wrapper-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha256-Sk3nkD6mLTMOF0EOpNtsIry+s1CsaqQC1rVLTAy+0yc= sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
<script type="text/javascript">
 $( document ).ready(function() {
 $( "#data" ).keyup(function() {
 if($("#data").val()!=''){
 $('#hiddendata').val($("#data").val());
 var formData = {data:$("#hiddendata").val()};
 getdata(formData);
 }else{
 $("#hiddendata").val('');
 $("#hasil").html('');
 }

 });
});
function getdata(formData){
 $.ajax({
 url : "parameter.php",
 type: "POST",
 data : formData,
 success: function(data, textStatus, jqXHR)
 {
 $("#hasil").html(data);
 },
 error: function (jqXHR, textStatus, errorThrown)
 {
 alert('error');
 }
 });
}
</script>
</body>
</html>
b. Create new php file as data.php and copy the following script.

<?php $data = $_GET['data']; include('phpqrcode/qrlib.php'); $codeContents = base64_decode($data); QRcode::png($codeContents, false, QR_ECLEVEL_L, 10); ?>
1
<?php $data = $_GET['data']; include('phpqrcode/qrlib.php'); $codeContents = base64_decode($data); QRcode::png($codeContents, false, QR_ECLEVEL_L, 10); ?>
c. Create new php file as parameter.php and copy the following script.

<?php
$data = base64_encode($_POST['data']);
echo '<center><img src="https://'.$_SERVER['SERVER_NAME'].'/data.php?data='.$data.'" /></center>';
?>
1
2
3
4
<?php
$data = base64_encode($_POST['data']);
echo '<center><img src="https://'.$_SERVER['SERVER_NAME'].'/data.php?data='.$data.'" /></center>';
?>
d. Create new CSS file as style.css and copy the following script.

body{
 font: normal 16px/20px 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Geneva, Verdana, sans-serif;
}
.content{
 margin-top: 15px;
}
#hasil img{
 max-width: 300px;
 -moz-box-shadow: 3px 3px 3px 3px #ccc;
 -webkit-box-shadow: 3px 3px 3px 3px #ccc;
 box-shadow: 3px 3px 3px 3px #ccc;
}
.label-form{
 font-weight: 100!important;
}
.tab-pane{
 padding: 10px;
}
.form-group {
 margin-bottom: 5px;
}
.header-h3{
 padding: 10px;
 background-color: green;
 color: white;
}
.form-control
{
 background: transparent;
 border: none;
 border-bottom: 1px dashed #83A4C5;
 box-shadow: 0 0 0 8px rgba(255,255,255,0.5);
 outline: none;
 padding: 0px 0px 0px 10px;
}
.form-control:focus, textarea:focus{
 border-bottom: 1px dashed #83A4C5;
 box-shadow: 0 0 0 8px rgba(255,255,255,0.5);
 outline: 0 none;
}
.footer{
 border-top: 1px solid #BEC8CC;
 font-size: 12px;
 margin-top: 20px;
 padding-top: 20px;
}
.footer a{
 color:#000000;
}
.footer a:hover{
 color:#000000;
 text-decoration: none!important;
}
.header{
 margin-top: 10px;
 margin-bottom:5px;
}
1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
body{
 font: normal 16px/20px 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Geneva, Verdana, sans-serif;
}
.content{
 margin-top: 15px;
}
#hasil img{
 max-width: 300px;
 -moz-box-shadow: 3px 3px 3px 3px #ccc;
 -webkit-box-shadow: 3px 3px 3px 3px #ccc;
 box-shadow: 3px 3px 3px 3px #ccc;
}
.label-form{
 font-weight: 100!important;
}
.tab-pane{
 padding: 10px;
}
.form-group {
 margin-bottom: 5px;
}
.header-h3{
 padding: 10px;
 background-color: green;
 color: white;
}
.form-control
{
 background: transparent;
 border: none;
 border-bottom: 1px dashed #83A4C5;
 box-shadow: 0 0 0 8px rgba(255,255,255,0.5);
 outline: none;
 padding: 0px 0px 0px 10px;
}
.form-control:focus, textarea:focus{
 border-bottom: 1px dashed #83A4C5;
 box-shadow: 0 0 0 8px rgba(255,255,255,0.5);
 outline: 0 none;
}
.footer{
 border-top: 1px solid #BEC8CC;
 font-size: 12px;
 margin-top: 20px;
 padding-top: 20px;
}
.footer a{
 color:#000000;
}
.footer a:hover{
 color:#000000;
 text-decoration: none!important;
}
.header{
 margin-top: 10px;
 margin-bottom:5px;
}