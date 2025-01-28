{extends "$layout"}

{block name="content"}
{l s='Wait for redirection...' mod='hbepay'}

<form id="hbepay" method="post" action="{$action}">
	<input type="submit" value="">
</form>

<script type="text/javascript">
	document.getElementById('hbepay').submit();
</script>
{/block}
