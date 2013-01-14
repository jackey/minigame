## .

## Nginx 配置
```
	location ~* \.(ico|css|js|gif|jpe?g|png)(\?[0-9]+)?$ {
                expires max;
                log_not_found off;
        }
 
        location / {
                # Check if a file exists, or route it to index.php.
                try_files $uri $uri/ /index.php;
        }
 
        location ~* \.php$ {
                fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                fastcgi_split_path_info ^(.+\.php)(.*)$;
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }

	# deny access to .htaccess files, if Apache's document root
	# concurs with nginx's one
	#
	#location ~ /\.ht {
	#	deny all;
	#}
```
