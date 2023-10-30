{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<center>{'LBL_LOADING_PLEASE_WAIT'|vtranslate}...</center>

<form class="form-horizontal" name="step7" method="post" action="index.php?module=Users&action=Login">
	{*<img src="//stats.vtiger.com/stats.php?uid={$APPUNIQUEKEY}&v={$CURRENT_VERSION}&type=I&industry={$INDUSTRY|urlencode}" alt='' title='' border=0 width='1px' height='1px'>*}
	<input type=hidden name="username" value="admin" >
	<input type=hidden name="password" value="{$PASSWORD}" >
</form>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('form[name="step7"]').submit();
	});
</script>
