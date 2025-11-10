# Utiliser une image php avec Apache
FROM php:8.4-apache

# Installer les dépendances et bibliothèques nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    git \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer l'extension MongoDB pour PHP
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Configurer Apache pour pointer vers le répertoire 'Public'
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Activer le mod_rewrite d'Apache
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Création des répertoires de stockage et de logs avec les permissions appropriées
RUN mkdir -p storage/logs public && chown -R www-data:www-data /var/www/html

# Copier le reste de l'application
COPY . .

# Changer les permissions des fichiers pour Apache
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Exposer le port 80 pour Apache
EXPOSE 80

# Démarrer Apache en mode premier plan avec ENTRYPOINT (au lieu de CMD) pour plus de sécurité
ENTRYPOINT ["apache2-foreground"]