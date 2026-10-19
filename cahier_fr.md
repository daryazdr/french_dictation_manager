# **Programmation Web et Base de données pour la TAL** 

# **Projet “Gestion des Dictées”** 

**Groupe 1, M1 Industrie de la Langue :** 

Ana Laura DONEGA-CANTUARIA, Catherine KOVACH, Ekaterina LEONOVA, Zohré SHAFIEE, Darya ZDRELYUK 

# **I. CONTEXTE ET OBJECTIF DU PROJET** 

Dans le cadre du projet de notre cours _Programmation Web et Base de données_ , nous avons développé un site web intitulé « **Gestion des Dictées** ». Ce site permet de gérer, saisir et analyser des dictées d’élèves afin de faciliter l’exploitation pédagogique et la recherche. 

L'objectif principal est de proposer une gestion structurée des données ainsi qu'une analyse automatique des productions écrites des élèves. Le site repose sur deux interfaces distinctes, conçues pour répondre aux besoins spécifiques de chaque profil. Le professeur a besoin d'un espace de gestion pédagogique pour suivre ses classes, saisir les dictées et évaluer les productions de ses élèves. Le chercheur, en revanche, a besoin d'un accès transversal à l'ensemble des données, indépendamment du professeur ou de la classe, afin de mener des analyses sur des variables sociolinguistiques telles que l'âge, la langue maternelle ou l'école de l'élève. 

# **II. Technologies utilisées** 

|**Technologie**|**Usage**|
|---|---|
|PHP|Logique serveur, sessions, appels API|
|MySQL|Base de données relationnelle|
|HTML / CSS|Interface utilisateur, mise en page|
|JavaScript|Interactions côté client, modals, export CSV|
|Python + Stanza|Analyse linguistique automatique (morphosyntaxe)|
|Google Vision API|OCR : reconnaissance de texte manuscrit|
|Serveur i3l|Hébergement de l’application|



# **III. Architecture et structure des fichiers** 

|**Fichier**|**Description**|
|---|---|
|index.php / index.css|Page de connexion (authentification, gestion des rôles)|
|inscription.php / inscription.css|Création de compte (professeur ou chercheur)|
|classes.php / classes.css|Gestion des classes du professeur|
|eleves.php / eleves.css|Gestion des élèves d’une classe|
|dictee.php / dictee.css|Création d’une nouvelle dictée|
|saisir_reponses.php / .css|Saisie des réponses des élèves|
|ocr.php|Transcription OCR via Google Vision API|
|analyse.php|Pont PHP/Python pour l’analyse Stanza|
|analyser_stanza.py|Script Python d’analyse linguistique|
|resultats.php / resultats.css|Visualisation des résultats (professeur)|
|resultats_chercheur.php|Visualisation des résultats (chercheur)|
|export_csv.php|Export CSV côté serveur (espace professeur)|
|resultats.js|Export CSV côté client (espace chercheur)|
|recalculate_errors.php|Script utilitaire de recalcul des erreurs|
|config.php / db.php|Configuration et connexion à la base de données|
|deconnexion.php|Destruction de session et déconnexion|



# **IV. FONCTIONNALITÉS PRINCIPALES** 

### **Profil professeur** 

_Gestion des classes_ 

● Création, modification et suppression de classes 

- Chaque classe est associée à un niveau scolaire, une école et une année scolaire 

### _Gestion des élèves_ 

- Ajout et suppression d'élèves au sein d'une classe 

- Chaque profil élève inclut : année de naissance, langue maternelle, indication du français parlé à la maison 

### _Gestion des dictées_ 

- Création et suppression de dictées 

- Choix du type de dictée (mots, phrases ou texte) 

- Indication du niveau scolaire et de la date de passation 

- Saisie du texte de référence 

### _Saisie des réponses élèves_ 

- Sélection de l'élève et de la dictée concernée 

- Saisie manuelle du texte produit par l'élève 

- Possibilité d'importer une image de dictée manuscrite et de la convertir en texte via un module OCR (Google Vision API) 

- Analyse linguistique automatique du texte via Stanza : détection et classification des erreurs par type (faute orthographique, accord adjectif, pluriel nom, accord sujet-verbe, mot ou ponctuation manquant) 

- Décompte automatique des erreurs par type, modifiable manuellement avant enregistrement 

- Calcul automatique du score /20, modifiable manuellement 

- Enregistrement en base de données avec sauvegarde du texte d'analyse 

### _Visualisation des résultats_ 

- Affichage tabulaire de tous les résultats 

- Filtrage par : élève, classe, dictée, type de dictée, type d'erreur, niveau scolaire, date 

### **Profil chercheur** 

### _Visualisation des résultats_ 

- Accès à l'ensemble des données sans restriction par professeur 

- Affichage tabulaire incluant les informations sur l'élève (langue maternelle, année de naissance, français à la maison) 

- Filtrage par : élève, classe, école, niveau scolaire, dictée, type d'erreur, langue maternelle, année de naissance, date 

### **Export** 

- Export des données filtrées au format CSV (compatible Excel via encodage UTF-8 BOM) 

- Les colonnes exportées incluent : classe, niveau, élève, dictée, texte modèle, texte élève, analyse détaillée, score, nombre d'erreurs par type 

# **V. TRAITEMENT AUTOMATIQUE DES DONNÉES (PYTHON)** 

**Description générale** Script Python appelé depuis PHP via shell_exec. Il compare deux textes français — la dictée de référence et la production de l'élève — et identifie les erreurs phrase par phrase en s'appuyant sur la bibliothèque Stanza pour l'analyse morphosyntaxique. 

**Fonctionnement** Les deux textes sont passés en arguments encodés en base64 pour éviter les problèmes d'encodage (accents, apostrophes) lors du passage PHP → shell → Python. Stanza analyse chaque texte avec un pipeline tokenize, pos, lemma : découpage en tokens, identification de la catégorie grammaticale (POS) et lemmatisation. Les phrases sont ensuite alignées deux à deux, et les tokens sont comparés via SequenceMatcher (algorithme de diff). 

### **Analyse interne Stanza (pipeline tokenize, pos, lemma)** 

Pour la phrase modèle _« Les enfants regardent la pluie tomber. »_ , Stanza retourne pour chaque token son texte, sa catégorie grammaticale (upos), son lemme et ses traits morphologiques (feats) : 

|**Token**|**Upos**|**Lemma**|**Feats**|
|---|---|---|---|
|Les|DET|le|Definite=Def|Gender=Masc|Number=Plur|PronType=Art|
|enfants|NOUN|enfant|Gender=Masc|Number=Plur|
|regardent|VERB|regarder|Mood=Ind|Number=Plur|Person=3|Tense=Pres|VerbForm<br>=Fin|
|la|DET|le|Definite=Def|Gender=Fem|Number=Sing|PronType=Art|
|pluie|NOUN|pluie|Gender=Fem|Number=Sing|
|tomber|VERB|tomber|VerbForm=Inf|



|.|PUNC<br>T|.<br>None|
|---|---|---|



**Classification des erreurs** Pour chaque différence détectée, le script applique une logique de classification : 

- même catégorie grammaticale (upos), feats différents sur un NOUN → pluriel nom 

- même catégorie grammaticale, feats différents sur un VERB → accord sujet-verbe 

- même catégorie grammaticale, feats différents sur un ADJ ou DET → accord adjectif 

- token absent chez l'élève → mot manquant 

- token supplémentaire chez l'élève → mot en trop 

- dans tous les autres cas → faute orthographique 

**Output** Texte brut structuré par phrase, par exemple pour la dictée modèle _« Rémi, joyeux, et sa troupe passent à travers un bois. Seul Rémi s'engage courageusement dans la plaine. »_ et la production de l'élève _« Rémi joyeuse et sa troupe passent à travers un bois. Seul Rémi s'engage corageucement dans plaine. »_ : 

### PHRASE 1 

ponctuation manquante : 

attendu "," 

accord adjectif : 

attendu "joyeux", 

reçu "joyeuse" 

ponctuation manquante : 

attendu "," 

PHRASE 2 faute orthographique : attendu "courageusement", reçu "corageucement" mot manquant : attendu "la" 

Ce texte est récupéré par PHP, parsé pour compter les erreurs par type, et sauvegardé dans la colonne analyse de la base de données. 

### **Limites** 

● Le modèle Stanza peut attribuer les mêmes traits morphologiques (feats) à deux formes verbales différentes en contexte (ex. fait / fais → tous deux classifiés Person=3), ce qui entraîne une classification en faute orthographique au lieu de accord sujet-verbe 

● La perte d'accent seule peut modifier le Tense détecté par Stanza (ex. avançaient → Imp vs avancaient → Pres), entraînant une classification incorrecte en accord sujet-verbe au lieu de faute orthographique 

● L'analyse est effectuée phrase par phrase : si le nombre de phrases diffère entre le modèle et la production de l'élève, les phrases excédentaires ne sont pas comparées 

● Le script dépend de la disponibilité de Stanza dans /tmp/python_lib, répertoire temporaire effacé à chaque redémarrage du serveur 

# **VI. Module OCR (Google Vision)** 

Le module OCR permet au professeur de transcrire automatiquement une dictée manuscrite à partir d’une photo. Le flux est le suivant : 

- Le professeur uploade une image (JPG, PNG ou WEBP, max 5 Mo) depuis la page ocr.php 

- L’image est encodée en base64 et envoyée à l’API Google Vision avec un indice de langue française 

- Le texte reconnu est affiché dans une zone éditable permettant au professeur de corriger les erreurs de 

- reconnaissance 

• Le texte peut être copié ou transféré directement vers le formulaire de saisie via sessionStorage (JavaScript) 

La clé API Google Vision est stockée dans **config.php** . La page inclut également des conseils pour obtenir une meilleure reconnaissance (bonne lumière, feuille à plat, cadrage du texte uniquement). 

# **VII. Base de données** 

### **Description:** 

- La base de données MySQL est composée de cinq tables principales reliées entre elles par des clés étrangères. La table g1_utilisateur stocke les comptes des professeurs et chercheurs (identifiant, email, mot de passe, rôle, institution). 

- La table g1_classe relie chaque classe à un professeur et contient le nom de la classe, le niveau scolaire, l'école et l'année scolaire. La table g1_eleve associe chaque élève à une classe et enregistre son année de naissance, sa langue maternelle et si le français est parlé à la maison. 

- La table g1_dictee contient les dictées créées par les professeurs et reliées à leur compte (titre, type, niveau scolaire, contenu de référence, date de passation). 

- Enfin, la table g1_dictes_resultats stocke les productions des élèves, le texte d'analyse généré automatiquement, le score et le décompte des erreurs par type (faute orthographique, mot ou ponctuation manquant, accord adjectif, nom pluriel, accord sujet-verbe). 

### **Limites de la base de données:** 

- Pour des raisons d'anonymat, chaque élève inscrit se voit attribuer un identifiant numérique unique ; son nom n'est pas enregistré, afin de ne pas compromettre son identité, les données de l'élève étant directement liées aux résultats. De plus, chaque élève est rattaché à une seule classe, ce qui empêche de suivre un même élève sur plusieurs années ou niveaux scolaires. 

- Cette conception ne permet donc pas de mener des études longitudinales basées sur le suivi individuel des élèves. En revanche, la base de données permet d'autres types d'analyses, telles que : des comparaisons entre niveaux scolaires (CP, CE1, CE2, CM1, CM2), des analyses par type d'erreur (orthographe, accord, pluriel, etc.), des croisements entre variables sociolinguistiques (langue maternelle, français parlé à la maison, année de naissance), des comparaisons entre classes ou entre écoles, et des analyses par type de dictée (mots, phrases, texte). 

# **VIII. Limites et perspectives** 

- Les mots de passe sont stockés en clair dans la base de données (pas de hachage) 

- La suppression d’une classe ne supprime pas automatiquement les élèves et résultats associés (pas de suppression en cascade) 

- Il n’est pas possible de modifier une dictée ou un résultat après enregistrement 

- Stanza dépend de /tmp/python_lib, répertoire temporaire effacé à chaque redémarrage du serveur, nécessitant une réinstallation 

- L’OCR dépend d’une clé API Google Vision avec un quota limité 

- L’interface n’est pas adaptée aux écrans mobiles (pas de responsive design complet) 

# **IX. Contributions** 

## **Ekaterina LEONOVA** 

**Page de résultats (resultats.php / resultats.css)** Développement quasi-complet de la page de résultats côté professeur : affichage tabulaire des résultats par classe, mise en page et stylisation de l'ensemble de la page, qui a également servi de base pour la page chercheur **(resultats_chercheur.php)** . 

**Script d’analyse linguistique (analyser_stanza.py)** Développement complet du script Python d’analyse morphosyntaxique : comparaison token par token des textes modèle et élève via Stanza, classification des erreurs par type (faute orthographique, accord adjectif, pluriel nom, accord sujet-verbe, mot/ponctuation manquant), et intégration avec le serveur PHP via **analyse.php** (appel shell, encodage base64, sauvegarde en base de données). 

**Export CSV (export_csv.php)** Correction du format d'export CSV pour assurer une ouverture correcte dans Excel : remplacement des sauts de ligne dans le champ analyse par un séparateur |, garantissant l'intégrité des colonnes. 

**Saisie des réponses (saisir_reponses.php)** Amélioration du module de saisie : correction de la commande d'appel Python, sauvegarde automatique du texte d'analyse en base de données lors de l'enregistrement, correction du bind_param, et ajout d'un aperçu de l'analyse avant validation. 

**Installation et débogage de Stanza** Déploiement de la bibliothèque d'analyse linguistique Stanza sur le serveur de l'université, en surmontant plusieurs contraintes techniques : incompatibilité entre la version de Python disponible (3.6) et les versions récentes de Stanza, absence de droits d'administration, répertoire /tmp effacé à chaque redémarrage, et conflits de dépendances (torch, protobuf, emoji). Résolution par installation ciblée dans /tmp/python_lib 

**Recalcul des scores et compteurs d'erreurs** Développement d'un script de recalcul unitaire **(recalculate_errors.php)** permettant de resynchroniser les compteurs d'erreurs par type (orthographe, accord, pluriel, etc.) et les scores de toutes les entrées existantes en base de données, à partir du texte d'analyse généré automatiquement. Exécuté en mode simulation (dry_run) avant application pour garantir l'intégrité des données. 

## **Catherine KOVACH** 

**Créer une nouvelle Dictée** La création du page nouvelle dictée **(dictee.php)** qui existe dans toutes les classes pour créer une connexion entre dictées spécifiques et leurs étudiants. Quand on crée une dictée ils sont les champs pour la titre qui facilite accès facile pendant .. Nouvelle Dictee are connected to class level and to the professor, this allows the professor to use the same dictee for multiple classes. 

**Saisir les réponses (saisir_reponses.php).** Conception de la page « Saisir les réponses ». Deux menus déroulants permettent de sélectionner l'élève dans la classe concernée et de choisir une dictée correspondant au niveau scolaire de la classe. Un champ pour saisir la dictée de l'élève. Un bouton relié au code Python pour l'analyse automatique. Un champ indiquant le nombre d'erreurs pour chaque type de critère après l'analyse automatique, avec la possibilité d'ajouter ou de modifier ce nombre manuellement, à l'aide de flèches ou en saisissant le chiffre. Une case de note finale reliée à l'analyse automatique, et le même élément modifiable. 

**Connexion et serveur** Mise en place et création de la connexion initiale entre toutes les pages PHP et le serveur de l'école. De même, création de la connexion entre la base de données et les pages, puis réalisation de tests en ajoutant des dictées et les réponses des élèves. 

## **Darya ZDRELYUK** 

**OCR** Reconnaissance de texte manuscrit **(ocr.php)** Développement d'un module de transcription automatique des dictées manuscrites à partir d'une photo. Le professeur peut désormais uploader une image de la copie de l'élève, qui est envoyée à l'API Google Vision pour en extraire le texte. Le résultat est affiché dans une zone éditable permettant au professeur de corriger d'éventuelles erreurs de reconnaissance avant utilisation. Intégration avec la page de saisie des réponses : le texte transcrit est automatiquement pré-rempli dans le champ de la dictée élève. Ajout de la clé API Google Vision dans **config.php** . 

**Élèves (eleves.php)** Ajout du champ langue maternelle dans le formulaire de création d'un élève, permettant de renseigner cette information dès l'inscription. 

**Résultats — Espace professeur (resultats.php, export_csv.php)** Refonte des filtres de la page de résultats : remplacement des filtres statiques par des filtres dynamiques alimentés depuis la base de données. Ajout de trois 

nouveaux filtres : Nom de dictée (par titre), Nom de classe et Niveau scolaire, ainsi que deux nouvelles colonnes correspondantes dans le tableau. Ajout d'un bouton retour et affichage du score sur 20. Mise à jour du fichier d'export CSV pour inclure les nouvelles colonnes et prendre en compte les nouveaux paramètres de filtrage. 

**Résultats — Espace chercheur (resultats_chercheur.php)** Branchement de l'analyse automatique Python (Stanza) sur la page chercheur. Ajout de trois nouveaux filtres : Niveau scolaire, École et Élève (ID), ainsi que les colonnes correspondantes dans le tableau. 

**Données de test** Ajout de nouvelles dictées de type « phrase » et « mot » dans la base de données afin de tester le bon fonctionnement de l'analyse sur différents formats de dictée. 

## **Ana Laura DONEGA-CANTUARIA** 

**Page de connexion (index.php / index.css)** Conception et implémentation de la page de connexion avec une interface split-screen : présentation du projet à gauche et formulaire d'authentification à droite. Mise en place d'un système de gestion de rôles (professeur / chercheur) avec redirection conditionnelle vers des interfaces différenciées. Le professeur accède uniquement aux données de ses classes pour l'évaluation des élèves, tandis que le chercheur accède à l'ensemble des données afin d'effectuer des recherches sur des variables sociolinguistiques (âge, langue maternelle, école, etc.). Gestion des sessions PHP, validation des entrées utilisateur et affichage dynamique des messages d'erreur. 

**Page d'inscription (inscription.php / inscription.css)** Développement du formulaire de création de compte avec sélection du rôle utilisateur. Implémentation d'un affichage conditionnel du champ « institution » pour les chercheurs via JavaScript. Vérification côté serveur de l'unicité de l'identifiant en base de données avant insertion, avec retour utilisateur via messages de confirmation ou d'erreur. 

**Page des classes (classes.php / classes.css)** Conception de la page « Mes classes » avec affichage dynamique des classes associées au professeur connecté. Intégration de requêtes SQL (LEFT JOIN, GROUP BY) pour le comptage du nombre d'élèves par classe. Implémentation des fonctionnalités de création et suppression de classes avec fenêtres modales de confirmation, ainsi que navigation vers les pages de gestion des élèves. Contrôle d'accès basé sur les sessions pour restreindre l'usage au profil professeur. 

**Structure et templates HTML/CSS** Création de gabarits réutilisables (en-tête, pied de page, navigation, fenêtres modales) utilisés par l'ensemble du projet. Mise en place d'une structure cohérente facilitant l'intégration des contributions des autres membres du groupe. 

**Identité visuelle et design system (CSS)** Définition d'un design system complet appliqué à toutes les pages : palette de couleurs, typographies et composants réutilisables (cards, boutons, formulaires, séparateurs). Utilisation de Flexbox et Grid pour assurer une mise en page responsive et cohérente. 

**Configuration et base de données (config.php)** Mise en place d'un fichier de configuration centralisé pour la connexion MySQL. Conception initiale du schéma relationnel et création des premières tables. Insertion de jeux de données de test, incluant des dictées réelles collectées auprès d'une élève de CE1. La structure a ensuite été enrichie et adaptée collectivement. 

## **Zohré SHAFIEE** 

**Analyse automatique du bouton “Corriger automatiquement” (saisir_reponses.php)** à l’intégration du bouton « Corriger automatiquement » dans la page saisir_reponses.php, avec l’aide des membres du groupe. Ce bouton avait pour objectif de permettre le lancement de l’analyse automatique directement depuis la page de saisie des réponses. 

**Saisie des réponses — lien avec la base de données (saisir_reponses.php / BDD)** Travail, avec l’aide du groupe, sur la tentative de synchronisation entre la page de saisie des réponses et la base de données MySQL. Cette partie concernait notamment le lien entre l’élève sélectionné, la dictée choisie, le texte de référence et la réponse saisie par l’élève. 

**Intégration de l’analyse automatique dans la page de saisie (saisir_reponses.php / python_lib.py)** Contribution à la mise en place du lien entre l’interface web et la fonctionnalité d’analyse automatique. La version finale du script Python a ensuite été reprise et améliorée par une autre membre du groupe, mais ma contribution a porté sur l’intégration de cette fonctionnalité dans la page saisir_reponses.php. 

**Cahier des charges et définition des besoins** Participation à la réflexion collective autour du cahier des charges du projet, notamment pour définir les besoins principaux de l’application, les fonctionnalités attendues et les profils utilisateurs concernés. 

