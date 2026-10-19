# Gestion des Dictées (Dictation Manager)
 
A web app to store, enter and analyze dictations written by primary school students. It is made for teachers and for researchers.
 
This is a group project for the course *Programmation Web et Base de données pour le TAL* (Master 1 Industries de la Langue, Université Grenoble Alpes).
 
## What it does
 
This app helps teachers keep track of dictations and finds the students' mistakes automatically.
 
There are two types of users:
 
- **Teachers** manage their own classes, students and dictations, and grade the students' work.
- **Researchers** can see the data from all teachers and all classes. They can study the results by age, mother tongue, school and more.
## Features
 
### For teachers
 
- Create, edit and delete classes. Each class has a school level, a school and a school year.
- Add and delete students. For each student, you save their year of birth, their mother tongue, and whether French is spoken at home.
- Create and delete dictations. You choose the type (words, sentences or a full text), the school level, the date, and you enter the correct text.
- Enter a student's answer by hand, **or** upload a photo of a handwritten dictation and turn it into text (OCR).
- Run an automatic analysis that finds the mistakes and sorts them by type.
- The app counts the mistakes by type and gives a score out of 20. The teacher can change both before saving.
- See all results in a table and filter them by student, class, dictation, dictation type, mistake type, school level and date.
- Export the results to a CSV file.
### For researchers
 
- See all results, from all teachers and all classes.
- The table also shows student information: mother tongue, year of birth, and French at home.
- Filter by student, class, school, school level, dictation, mistake type, mother tongue, year of birth and date.
- Export the filtered data to CSV. The file opens correctly in Excel (UTF-8 with BOM).
### Privacy
 
Student names are never saved. Each student only has a number ID.
 
## Technologies
 
| Technology | What it is used for |
|---|---|
| PHP | Server logic, sessions, API calls |
| MySQL | Database |
| HTML / CSS | Pages and layout |
| JavaScript | Pop-up windows, client-side CSV export |
| Python + Stanza | Automatic grammar analysis |
| Google Vision API | OCR (reading handwritten text from a photo) |
 
## How the automatic analysis works
 
The script `analyser_stanza.py` compares the correct text with the student's text.
 
1. PHP calls the Python script with `shell_exec`. Both texts are encoded in base64, so accents and apostrophes do not break.
2. Stanza reads each text (`tokenize`, `pos`, `lemma`). It splits the text into words, finds the type of each word (noun, verb…), and finds its base form.
3. The sentences of the two texts are matched one by one. Inside each sentence, the words are compared with Python's `SequenceMatcher`.
4. Each difference gets a mistake type:
| What is different | Mistake type |
|---|---|
| Same word type, different grammar features, on a **noun** | Plural noun |
| Same word type, different grammar features, on a **verb** | Subject-verb agreement |
| Same word type, different grammar features, on an **adjective or determiner** | Adjective agreement |
| A word or punctuation mark is missing in the student's text | Missing word / punctuation |
| The student's text has an extra word | Extra word |
| Anything else | Spelling mistake |
 
### Example
 
Correct text:
> Rémi, joyeux, et sa troupe passent à travers un bois. Seul Rémi s'engage courageusement dans la plaine.
 
Student's text:
> Rémi joyeuse et sa troupe passent à travers un bois. Seul Rémi s'engage corageucement dans plaine.
 
Output (the app shows it in French):
 
```
PHRASE 1
ponctuation manquante : attendu ","
accord adjectif : attendu "joyeux", reçu "joyeuse"
ponctuation manquante : attendu ","
PHRASE 2
faute orthographique : attendu "courageusement", reçu "corageucement"
mot manquant : attendu "la"
```
 
PHP reads this text, counts the mistakes by type, and saves it in the database.
 
## OCR module
 
1. The teacher uploads a photo (JPG, PNG or WEBP, max 5 MB) on `ocr.php`.
2. The image is sent to the Google Vision API, with French as the language hint.
3. The text appears in a box where the teacher can fix recognition errors.
4. The teacher can copy the text or send it straight to the answer form.
The page also gives tips for better results: good light, flat paper, and only the text in the picture.
 
## Project structure

```
├── scripts/
│   ├── index.php / index.css/             # login page and user roles
│   ├── inscription.php / inscription.css/ # sign-up (teacher or researcher)
│   ├── classes.php / classes.css/         # teacher's classes
│   ├── eleves.php / eleves.css/           # students in a class
│   ├── dictee.php / dictee.css/           # create a new dictation
│   ├── saisir_reponses.php / .css/        # enter students' answers
│   ├── ocr.php/                           # OCR with Google Vision
│   ├── analyse.php/                       # link between PHP and the Python script
│   ├── analyser_stanza.py/                # python grammar analysis
│   ├── resultats.php / resultats.css/     # results page (teacher)
│   ├── resultats_chercheur.php/           # results page (researcher)
│   ├── export_csv.php/                    # CSV export on the server (teacher)
│   ├── resultats.js/                      # CSV export in the browser (researcher)
│   ├── recalculate_errors.php/            # tool to recount mistakes and scores for saved results
│   ├── config.php / db.php/               # settings and database connection
│   ├── deconnexion.php/                   # log out
│   ├── requirements.txt/                  # dependencies required
│
├── cahier_fr.md          # Documentation in french
└── README.md             # Documentation
```
 
## Database
 
The database was not added here. Your MySQL database should have five main tables:
 
- `g1_utilisateur` – teacher and researcher accounts (username, email, password, role, institution)
- `g1_classe` – classes, linked to a teacher (name, school level, school, school year)
- `g1_eleve` – students, linked to a class (year of birth, mother tongue, French at home)
- `g1_dictee` – dictations, linked to a teacher (title, type, school level, correct text, date)
- `g1_dictes_resultats` – students' answers, analysis text, score, and number of mistakes by type
With this data you can compare school levels (CP to CM2), mistake types, classes, schools, dictation types, and social and language factors such as mother tongue or French at home.
 
## Setup
 
You need:
 
- A web server with PHP
- MySQL
- Python 3 with Stanza and its French model
- A Google Vision API key
Steps:
 
1. Copy the project files to your PHP server.
2. Create the MySQL database and the tables (see the Database section).
3. Put your database login and your Google Vision API key in `config.php`.
4. Install Stanza and the French model:
```bash
pip install stanza
python -c "import stanza; stanza.download('fr')"
```
 
**Note:** on the university server, we had Python 3.6 and no admin rights, so Stanza was installed in `/tmp/python_lib`. This folder is deleted every time the server restarts, so Stanza has to be installed again.
 
## Known limitations
 
- Passwords are saved in plain text (no hashing).
- Deleting a class does not delete its students and results.
- You cannot edit a dictation or a result after saving it.
- Stanza can give the same grammar features to two different verb forms (for example *fait* / *fais*). Then a subject-verb mistake is labeled as a spelling mistake.
- A missing accent can change the tense that Stanza finds (for example *avançaient* / *avancaient*). Then a spelling mistake is labeled as a subject-verb mistake.
- Sentences are compared one by one. If the student wrote a different number of sentences, the extra ones are not checked.
- Each student belongs to only one class, so you cannot follow the same student over several years.
- The OCR uses a Google Vision API key with a limited quota.
- The site does not fully work on mobile screens.
## Team
 
Group 1, Master 1 Industries de la Langue, Université Grenoble Alpes:
 
- **Ana Laura Donega-Cantuaria** – login and sign-up pages, user roles, classes page, shared HTML/CSS templates, design system, first database design
- **Catherine Kovach** – new dictation page, design of the answer page, connection between the PHP pages, the server and the database
- **Ekaterina Leonova** – Stanza analysis script, teacher results page, CSV export fix, Stanza install on the server, mistake recount script
- **Zohré Shafiee** – "Corriger automatiquement" button, link between the answer page and the database, project requirements
- **Darya Zdrelyuk** – OCR module (Google Vision), mother tongue field, dynamic filters on the results pages, integrating Stanza analysis on the researcher page, test data