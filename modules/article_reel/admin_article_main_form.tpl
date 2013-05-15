[{$smarty.block.parent}]
	[{if $edit}]
	<tr>
	  <td class="edittext" width="120">
	      360°-Ansicht hinzufügen
	  </td>
	  <td class="edittext" valign="top">
	  	<input type="file" name="userfile[]" multiple />
    	<input type="submit" class="edittext" id="" name="" value="Hochladen" onClick="javascript:{document.myedit.fnc.value='upload_reel';document.myedit.setAttribute('enctype','multipart/form-data');}" [{ $readonly }]>
	  	[{if $oView->checkReelExistence($edit->oxarticles__oxid->value) == true}]
	  		<span class="reel-info"><br/>Es ist bereits eine 360°-Ansicht für diesen Artikel vorhanden<br/>Vorhandene Daten werden beim erneuten Upload überschrieben</span>
	  	[{else}]
	  		<span class="reel-info"><br/>Noch keine 360°-Ansicht vorhanden</span>
	  	[{/if}]
	  </td>
	</tr>	
	[{/if}]