# KM_Search : Application de Recherche de Documents

## Table des Matières
- [Introduction](#introduction)
- [Fonctionnalités](#fonctionnalités)
- [Architecture et Technologies](#architecture-et-technologies)
- [Installation et Configuration](#installation-et-configuration)
- [Utilisation](#utilisation)
- [Indexation et Recherche](#indexation-et-recherche)
- [Déploiement et Présentation Live](#déploiement-et-présentation-live)
- [Auteurs](#auteurs)
- [Licence](#licence)

## Introduction
KM_Search est une application web conçue pour l'entreprise **Kankou Moussa** afin de gérer et rechercher l'ensemble de ses documents.  
L'application permet la création, la modification et la suppression de documents, ainsi qu'une recherche avancée en full-text sur le contenu des documents avec un filtrage par type (ex. : CV, fiche de poste, évaluation annuelle).  
Ce projet s'inscrit dans le cadre du Master 1 en Data Science (UIE) et doit être présenté en live.

## Fonctionnalités
- **Création de documents**  
  Lors de la création, l'utilisateur renseigne :
  - `doc_id` : Identifiant unique (généré automatiquement)
  - `doc_name` : Nom du document
  - `doc_type` : Type du document (sélection dynamique depuis l'interface)
  - `doc_content` : Contenu textuel extrait automatiquement (PDF ou Word)
  - `doc_format` : Format du document (word, pdf)
  - `doc_insert_date` et `doc_updated_date` : Dates d'insertion et de modification (gérées automatiquement)
  - `doc_file_full_path` : Chemin du fichier stocké sur le disque

- **Modification et suppression de documents**  
  Possibilité de mettre à jour ou de supprimer un document avec suppression du fichier associé et de son index dans Elasticsearch.

- **Recherche avancée**  
  La recherche s'effectue sur le champ `doc_content` avec :
  - Un champ texte pour la recherche full-text (avec surlignage des résultats)
  - Un filtre facultatif par type de document (un seul type à la fois)
  - Affichage du nom du document, d'un extrait surligné et d'un lien de téléchargement

- **Indexation**  
  Les documents (et les types de documents) sont indexés dans Elasticsearch pour assurer une recherche rapide et efficace.

## Architecture et Technologies
- **Backend** : PHP (Laravel 12)
- **Base de données** : MongoDB
- **Moteur de recherche** : Elasticsearch (configuration avec analyseur français)
- **Frontend** : Blade Templates
- **Stockage** : Fichiers stockés en local
- **Extraction de contenu** : Utilisation de bibliothèques tierces pour extraire le texte des fichiers PDF et Word

## Installation et Configuration
### Prérequis
- PHP >= 8.0
- Composer
- MongoDB
- Elasticsearch (compatible avec la version configurée)
- Extensions PHP : mbstring, etc.

### Installation
1. **Cloner le dépôt**  
   ```bash
   git clone https://votre-repo-url.git
   cd KM_Search
