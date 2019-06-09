<?php
  $dynamic_app = getenv('DYNAMIC_APP');
  $static_app = getenv('STATIC_APP');
?>
<VirtualHost *:80>
	ServerName demo.res.ch

	ProxyPass '/api/joke/' 'http://<?php echo "$dynamic_app" ?>/'
	ProxyPassReverse '/api/joke/' 'http://<?php echo "$dynamic_app" ?>/'
	
	ProxyPass '/' 'http://<?php echo "$static_app" ?>/'
	ProxyPassReverse '/' 'http://<?php echo "$static_app" ?>/'
</VirtualHost>
