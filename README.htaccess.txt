<IfModule mod_rewrite.c>
RewriteEngine On

##
# fill this out if you're not running from a top level url
##
#RewriteBase /~user/sub/dir/

##
# uncomment the next two lines if you want to control and remove the "www." prefix from your domains
##
#RewriteCond %{HTTP_HOST} ^www.domain.com$ [NC]
#RewriteRule ^(.*)$ http://domain.com/$1 [R=301,L]


##
# some protective rules
##
# Deny access to .htaccess
RewriteRule ^\.htaccess$ - [F]

# Do not worry about missing favico
RewriteRule ^favicon\.ico - [L]
RewriteRule ^favico\.ico - [L]
RewriteRule ^robots\.txt - [L]
# needed for plesk based hosting
RewriteRule ^webstat - [L]

##
# these are the actual conditions and rules that work on the index.php file
##

# don't rewrite existing files, directories, or links
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l

# don't rewrite media or templates folders
RewriteCond %{REQUEST_URI} !^(media|templates)/

#choose the top one if you're running from a top level url
#RewriteRule ^(.*)$ /index.php/$1 [L]
RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>

##
# don't allow access to the cgi/cron interface from the web
##
<Files cron.php>
Order deny,allow
Deny From all
</Files>
