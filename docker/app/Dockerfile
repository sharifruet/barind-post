FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    locales \
    wget \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql \
    && docker-php-ext-enable gd intl \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Install fonts
RUN apt-get update && apt-get install -y \
    fonts-dejavu \
    fonts-liberation \
    fonts-noto \
    fonts-noto-cjk \
    fontconfig \
    wget \
    && mkdir -p /usr/share/fonts/truetype/noto \
    && wget -O /usr/share/fonts/truetype/noto/NotoSansBengali-Bold.ttf "https://fonts.gstatic.com/s/notosansbengali/v1/ieVc2YdFI3GCY6SyQy1KfStzYKZgzN1z0w.woff2" \
    && wget -O /usr/share/fonts/truetype/noto/NotoSansBengali-Regular.ttf "https://fonts.gstatic.com/s/notosansbengali/v1/ieVc2YdFI3GCY6SyQy1KfStzYKZgzN1z0w.woff2" \
    && fc-cache -fv

# Set UTF-8 locale
RUN locale-gen en_US.UTF-8 && locale-gen bn_BD.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

# Configure PHP for image processing
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Set document root to /var/www/html/public for CodeIgniter 4
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Configure Apache for CodeIgniter 4
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    DirectoryIndex index.php\n\
    AcceptPathInfo On\n\
</Directory>\n\
\n\
# Enable Unicode URL support\n\
<IfModule mod_rewrite.c>\n\
    RewriteEngine On\n\
    RewriteOptions AllowNoSlash\n\
</IfModule>\n\
\n\
# Set default charset\n\
AddDefaultCharset UTF-8\n\
\n\
# Enable Unicode in URLs\n\
<IfModule mod_rewrite.c>\n\
    RewriteCond %{REQUEST_URI} [^\x00-\x7F]\n\
    RewriteRule .* - [E=UNICODE_URL:1]\n\
</IfModule>' > /etc/apache2/conf-available/codeigniter.conf \
    && a2enconf codeigniter

# Create a proper Apache configuration
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"] 