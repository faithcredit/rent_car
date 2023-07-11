# Leverage Browser Caching by SG-Optimizer
<IfModule mod_expires.c>
    ExpiresActive on
  # CSS
    ExpiresByType text/css                              "access plus 1 year"
  # JavaScript
    ExpiresByType application/javascript                "access plus 1 year"
    ExpiresByType application/x-javascript              "access plus 1 year"
  # Manifest files
    ExpiresByType application/x-web-app-manifest+json   "access plus 0 seconds"
    ExpiresByType text/cache-manifest                   "access plus 0 seconds"
  # Media
    ExpiresByType audio/ogg                             "access plus 1 year"
    ExpiresByType image/gif                             "access plus 1 year"
    ExpiresByType image/jpg                             "access plus 1 year"
    ExpiresByType image/jpeg                            "access plus 1 year"
    ExpiresByType image/png                             "access plus 1 year"
    ExpiresByType image/svg                             "access plus 1 year"
    ExpiresByType image/svg+xml                         "access plus 1 year"
    ExpiresByType video/mp4                             "access plus 1 year"
    ExpiresByType video/ogg                             "access plus 1 year"
    ExpiresByType video/webm                            "access plus 1 year"
    ExpiresByType image/x-icon                          "access plus 1 year"
    ExpiresByType application/pdf                       "access plus 1 year"
    ExpiresByType application/x-shockwave-flash         "access plus 1 year"
  # XML
    ExpiresByType text/xml                              "access plus 0 seconds"
    ExpiresByType application/xml                       "access plus 0 seconds"
  # Web feeds
    ExpiresByType application/atom+xml                  "access plus 1 hour"
    ExpiresByType application/rss+xml                   "access plus 1 hour"
  # Web fonts
    ExpiresByType application/font-woff                 "access plus 1 year"
    ExpiresByType application/font-woff2                "access plus 1 year"
    ExpiresByType application/vnd.ms-fontobject         "access plus 1 year"
    ExpiresByType application/x-font-ttf                "access plus 1 year"
    ExpiresByType font/opentype                         "access plus 1 year"
</IfModule>
# END LBC
