
# EBM Search - Document Management System

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Elasticsearch](https://img.shields.io/badge/Elasticsearch-005571?style=for-the-badge&logo=elasticsearch&logoColor=white)](https://www.elastic.co)
[![MongoDB](https://img.shields.io/badge/MongoDB-47A248?style=for-the-badge&logo=mongodb&logoColor=white)](https://www.mongodb.com)

Application web de gestion et de recherche de documents avec recherche full-text et gestion dynamique des types de documents.

## ✨ Fonctionnalités principales

- 🔍 Recherche full-text dans le contenu des documents
- 📁 Gestion complète des documents (CRUD)
- 🏷️ Création dynamique de types de documents
- 📄 Support des formats PDF et Word
- 🎯 Surlignage des termes recherchés
- 🔒 Gestion sécurisée des fichiers
- 📈 Indexation temps réel avec Elasticsearch
- 📦 Stockage des fichiers sur disque

## 🚀 Installation

### Prérequis
- PHP 8.0+
- Composer
- MongoDB 4.4+
- Elasticsearch 7.10+
- Node.js 14+
- Serveur web (Apache/Nginx) ou PHP built-in server

### Étapes d'installation
1. Cloner le dépôt :
```bash
git clone https://github.com/moodeski/ebm_search.git
cd ebm-search
```

2. Installer les dépendances :
```bash
composer install
npm install && npm run build
```

3. Configurer l'environnement :
```bash
cp .env.example .env
php artisan key:generate
```

4. Modifier le fichier `.env` :
```ini
APP_NAME=EBM_Search
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=db_name

ELASTICSEARCH_HOST=localhost:9200
ELASTICSEARCH_USERNAME=username
ELASTICSEARCH_PASSWORD=password

FILESYSTEM_DISK=public
```

5. Créer le lien de stockage :
```bash
php artisan storage:link
```

6. Démarrer les services :
```bash
# Elasticsearch (selon votre installation)
sudo systemctl start elasticsearch

# Serveur de développement
php artisan serve
```

## 🛠 Configuration Elasticsearch

Créer les indexes nécessaires :
L’indexation est automatiquement prise en charge par l’application.

## 📚 Utilisation

### Accès à l'application
- URL : `http://localhost:8000`

### Gestion des documents
1. **Ajouter un document**
   - Formats supportés : PDF, Word
   - Extraction automatique du texte
   - Métadonnées automatiquement remplies

2. **Recherche avancée**
   - Champ libre de recherche
   - Filtrage par type de document
   - Surlignage des résultats

3. **Gestion des types**
   - Création/Modification dynamique
   - Liste mise à jour en temps réel

## 📦 Dépendances principales
- [Laravel 12](https://laravel.com)
- [Elasticsearch PHP Client](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html)
- [Smalot PDF Parser](https://github.com/smalot/pdfparser)
- [PHPWord](https://github.com/PHPOffice/PHPWord)
- [MongoDB Laravel Integration](https://github.com/jenssegers/laravel-mongodb)

## 🔍 Exemple de recherche Elasticsearch
```json
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "doc_content": "exemple de recherche"
          }
        }
      ],
      "filter": [
        {
          "term": {
            "doc_type": "CV"
          }
        }
      ]
    }
  }
}
```

## 🚨 Dépannage

### Problèmes courants
1. **Erreur de connexion Elasticsearch**
   - Vérifier les logs Elasticsearch
   - Confirmer les credentials dans `.env`

2. **Problème d'extraction de texte**
   - Vérifier les dépendances : `pdftotext` et `phpword` (composer require)
   - Vérifier les permissions des fichiers

3. **Erreur mbstring ou mongodb**
   - Activer l'extension PHP mbstring ou mongodb.dll (php.ini) :
   ```bash
   sudo apt-get install php-mbstring
   sudo systemctl restart apache2
   ```

## 📄 Licence
MIT License - Voir le fichier [LICENSE.md](LICENSE) pour plus de détails

---

**Développé par** : Souleymane MAIGA et Modibo Kane NIARE
**Client** : Entreprise Kankou Moussa  
**Date de livraison** : 21/04/2025