	</div>
	<!-- Content end -->

<?php
	if (count($gErrorArray)) {
		$str = '';
		$str .= '	<!-- Error begin -->' . "\n";
		$str .= '	<div class="error">' . "\n";
		$title = 'Errors (' . count($gErrorArray) . ')';
		$str .= '		<table width="100%">' . "\n";
		$str .= '			<tr>' . "\n";
		$str .= '				<th colspan="2">' . $title . '</th>' . "\n";
		$str .= '			</tr>' . "\n";
		foreach ($gErrorArray as $fileName => $error) {
			// Display error
			$str .= '			<tr>' . "\n";
			$str .= '				<td class="col_1">' . $fileName . '</td>' . "\n";
			$str .= '				<td class="col_2">' . $error . '</td>' . "\n";
			$str .= '			</tr>' . "\n";
		}
		$str .= '		</table>' . "\n";
		$str .= '	</div>' . "\n";
		$str .= '	<!-- Error end -->' . "\n";
		echo $str;
	}
?>
	</div>

	<!-- Footer begin -->
	<div class="footer">
<?php
	if (!empty($gConfig['admin_email'])) {
?>
		<script type="text/javascript">
/*<![CDATA[*/
			document.write("<n uers=\"znvygb:<?php echo str_rot13($gConfig['admin_email']); ?>\">".replace(/[a-zA-Z]/g, function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));
			document.write("Contact<\/a>");
/*]]>*/
		</script>
<?php
	}
?>
	</div>
	<!-- Footer end -->

</body>
</html>
