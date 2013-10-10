<?php
header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $gConfig['app_name']; ?></title>
	<meta name="author" content="opale-concept.com" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
/*<![CDATA[*/
  body {
	  font-family: "Times New Roman", Times, serif;
	  background-color: #ffffff;
	  color: #333333;
	  margin: 20px;
  }
  a {
	  color: #096DD1;
		text-decoration: none;
  }
  a:hover {
	  color: #DF7800;
  }
  a:focus {
	  color: #6E749F !important;
  }
	ul, li {
		margin: 0;
	}
  .header {
	  color: #6E749F;
	  font-style: bold;
	  font-size: 120%;
	  padding-bottom: 10px;
		border-bottom: solid #2F4769 1px;
		margin-bottom: 15px;
  }
  .part {
		border-top: solid #2F4769 1px;
		padding: 10px;
	}
  .title {
	  padding-bottom: 5px;
  	font-weight: bold;
	}
	table {
		border-collapse: collapse;
		border-color: #ccc;
	}
	table th {
		text-align: left;
	}
	table td, table th {
		padding-left: 5px;
		padding-right: 5px;
	}
	td.col_1, th.col_1 {
		width: 50%;
	}
	td.col_2, th.col_2 {
		width: 50%;
	}
  .error {
	  color: #750000;
		margin-top: 15px;
  }
	.footer {
		border-top: solid #2F4769 1px;
		margin-top: 15px;
		padding-top: 10px;
	  font-size: 80%;
	  font-style: italic;
  }
  /*]]>*/
  </style>
</head>

<body>
	<!-- Header begin -->
	<div class="header">
		<a href="."><?php echo $gConfig['app_name']; ?></a>
	</div>
	<!-- Header end -->

	<!-- Content begin -->
	<div class="content">
