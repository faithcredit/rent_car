# HTTPS forced by SG-Optimizer
<IfModule mod_rewrite.c>
    RewriteEngine On
    {MAYBE_WWW}
    RewriteCond %{HTTP:X-Forwarded-Proto} !https
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
# END HTTPS