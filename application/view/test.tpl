{include file=header.tpl}

<div id="nav">
    <a href="index.php" title="{t}Back to the dashboard{/t}">{t}Dashboard{/t}</a> > Test page
</div>

<div class="main_center">
	<div class="box">
	  <p class="title">{t}Required components{/t}</p>
	<table border="0">
		{foreach from=$checks item=check}
		  <tr>
		    <td style="font-weight:bold;font-size:10pt;vertical-align:middle;text-align:left;padding:10px 5px 5px 30px;">
				{$check.check_label}
			</td>
			<td style="text-align:left;vertical-align:middle;">
				{$check.check_descr}
			</td>
			<td  style="text-align:center;width=60px;">
				<img src='application/view/style/images/{$check.check_result}' width='25' alt=''/>
			</td>
		  </tr>
		{/foreach}
	</table>
	</div> <!-- end div class=box -->
	
	<div class="box">
	  <p class="title">Graph</p>
	<table>
	  <tr>
	    <td style="font-weight:bold;font-size:10pt;vertical-align:middle;text-align:left;padding:10px 5px 5px 30px;">
			{t}Graph system capabilities (Bacula-web only use PNG image format){/t}
		</td>
	    <td>
		  <img src="{$graph_test}" alt='' />
	    </td>
	  </tr>
	</table>
	</div> <!-- end div class=box -->
</div> <!-- end div id=main_center -->

</body>
</html>