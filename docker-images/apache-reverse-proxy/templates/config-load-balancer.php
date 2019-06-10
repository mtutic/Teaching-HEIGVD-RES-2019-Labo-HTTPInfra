<?php
	$dynamic_app_1 = getenv('DYNAMIC_APP_1');
	$dynamic_app_2 = getenv('DYNAMIC_APP_2');
	$static_app_1 = getenv('STATIC_APP_1');
	$static_app_2 = getenv('STATIC_APP_2');
?>
<VirtualHost *:80>
	ServerName demo.res.ch

	<Proxy "balancer://joke">
		BalancerMember 'http://<?php echo "$dynamic_app_1" ?>'
		BalancerMember 'http://<?php echo "$dynamic_app_2" ?>'
	</Proxy>

	<Proxy "balancer://static">
		BalancerMember 'http://<?php echo "$static_app_1" ?>'
		BalancerMember 'http://<?php echo "$static_app_2" ?>'
	</Proxy>

	ProxyPass '/api/joke/' 'balancer://joke/'
	ProxyPassReverse '/api/joke/' 'balancer://joke/'
	
	ProxyPass '/' 'balancer://static/'
	ProxyPassReverse '/' 'balancer://static/'
</VirtualHost>
