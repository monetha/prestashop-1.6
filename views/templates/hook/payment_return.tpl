{if $status == 'ok'}
<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='monethagateway'}
		<br /><br />
		{l s='Please send us a payment ' mod='monethagateway'}
		{if isset($payment_url)}
			<strong><a href="{$payment_url}" target="_blank">{l s='here' mod='monethagateway'}</a></strong>
		{/if}
		<br /><br />- {l s='Amount' mod='monethagateway'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br /><br />- {l s='Order number: #%d.' sprintf=$id_order mod='monethagateway'}
		{if isset($reference)}
			<br /><br />- {l s='Order reference: %s.' sprintf=$reference mod='monethagateway'}
		{/if}
		<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='monethagateway'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='monethagateway'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='monethagateway'}</a>.
	</p>
{elseif $status == 'paid'}
	{l s='Thanks for your payment.' mod='monethagateway'}
	<br /><br />- {l s='Order number: #%d.' sprintf=$id_order mod='monethagateway'}
	{if isset($reference)}
		<br /><br />- {l s='Order reference: %s.' sprintf=$reference mod='monethagateway'}
	{/if}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='monethagateway'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='monethagateway'}</a>.
	<br /><br />
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='monethagateway'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='monethagateway'}</a>.
	</p>
{/if}
