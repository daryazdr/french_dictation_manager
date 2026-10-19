<?php
/*
 * install_march.php
 * Reproduit exactement install_libs.php du dossier analyser (mars 2026)
 * ⚠️ SUPPRIMER après utilisation !
 */

set_time_limit(600);
ini_set('output_buffering', 'off');
ob_implicit_flush(true);

echo "<pre style='font-family:monospace; font-size:0.85rem;'>";
echo "=== INSTALLATION (méthode mars 2026) ===\n\n";

// Quelle version de python3/pip3 ?
echo "--- Versions ---\n";
echo shell_exec("which pip3 2>&1");
echo shell_exec("pip3 --version 2>&1");
echo shell_exec("which python3 2>&1");
echo shell_exec("python3 --version 2>&1");
flush();

// Vider l'ancien dossier
echo "\n--- Suppression /tmp/python_lib ---\n";
echo shell_exec("rm -rf /tmp/python_lib 2>&1");
echo shell_exec("mkdir -p /tmp/python_lib 2>&1");
flush();

// Exactement comme install_libs.php du mars
echo "\n--- Installation numpy 1.26.4 ---\n";
echo shell_exec("pip3 install numpy==1.26.4 --target=/tmp/python_lib 2>&1");
flush();

echo "\n--- Installation torch 2.0.1 ---\n";
echo shell_exec("pip3 install torch==2.0.1 --target=/tmp/python_lib 2>&1");
flush();

echo "\n--- Installation stanza (dernière version) ---\n";
echo shell_exec("pip3 install stanza --target=/tmp/python_lib 2>&1");
flush();

// Vérification
echo "\n--- Vérification ---\n";
$check = shell_exec("export PYTHONPATH=/tmp/python_lib && python3 -c \"
import sys
sys.path.insert(0, '/tmp/python_lib')
sys.path = [p for p in sys.path if 'easyocr' not in p]
import stanza, torch, numpy
print('stanza:', stanza.__version__)
print('torch:', torch.__version__)
print('numpy:', numpy.__version__)
\" 2>&1");
echo $check;
flush();

// Test complet
echo "\n--- TEST PIPELINE ---\n";
$test = shell_exec("export LC_ALL=C.UTF-8 && export LANG=C.UTF-8 && export PYTHONPATH=/tmp/python_lib && python3 -c \"
import sys
sys.path.insert(0, '/tmp/python_lib')
sys.path = [p for p in sys.path if 'easyocr' not in p]
import logging
logging.getLogger('stanza').setLevel(logging.ERROR)
import stanza
nlp = stanza.Pipeline('fr', processors='tokenize,pos,lemma', dir='/tmp/stanza_fr', use_gpu=False, download_method=None)
doc = nlp('Il fait beau.')
for s in doc.sentences:
    for t in s.tokens:
        print(t.text, t.words[0].upos)
print('===TEST OK===')
\" 2>&1");
echo $test . "\n";

if (strpos($test, '===TEST OK===') !== false) {
    echo "\n✅ STANZA FONCTIONNE !\n\n";

    // Copier vers dossier permanent
    echo "--- Copie vers dossier permanent ---\n";
    $dest_lib    = '/home/L3/l3_gr16/public_html/PWTAL/EkaterinaL/projetweb/python_lib';
    $dest_models = '/home/L3/l3_gr16/public_html/PWTAL/EkaterinaL/projetweb/stanza_models';

    echo shell_exec("mkdir -p $dest_lib && cp -r /tmp/python_lib/. $dest_lib/ 2>&1");
    echo "✓ python_lib copié vers $dest_lib\n";
    echo shell_exec("mkdir -p $dest_models && cp -r /tmp/stanza_fr/. $dest_models/ 2>&1");
    echo "✓ stanza_fr copié vers $dest_models\n\n";

    // Vérifier permanent
    $check2 = shell_exec("export LC_ALL=C.UTF-8 && export LANG=C.UTF-8 && export PYTHONPATH=$dest_lib && python3 -c \"
import sys
sys.path.insert(0, '$dest_lib')
sys.path = [p for p in sys.path if 'easyocr' not in p]
import logging
logging.getLogger('stanza').setLevel(logging.ERROR)
import stanza
nlp = stanza.Pipeline('fr', processors='tokenize,pos,lemma', dir='$dest_models', use_gpu=False, download_method=None)
doc = nlp('Il fait beau.')
print('===PERMANENT OK===')
\" 2>&1");
    echo $check2;

    if (strpos($check2, '===PERMANENT OK===') !== false) {
        echo "\n✅ DOSSIER PERMANENT OK !\n";
        echo "Chemin lib    : $dest_lib\n";
        echo "Chemin modèle : $dest_models\n";
    } else {
        echo "\n⚠️ Permanent inaccessible — /tmp fonctionne\n";
    }
} else {
    echo "\n❌ Erreur\n";
}

echo "\n=== FIN ===\n";
echo "</pre>";
?>
