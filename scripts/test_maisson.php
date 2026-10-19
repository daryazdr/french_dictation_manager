<?php
echo "<pre>";
$b64_m = base64_encode("maison porte fenêtre cuisine salon chambre");
$b64_s = base64_encode("maisson porte fenetre cuisine salon chambre");

$out = shell_exec("export LC_ALL=C.UTF-8 && export LANG=C.UTF-8 && export PYTHONPATH=/tmp/python_lib && python3 -c \"
import sys, base64
sys.path.insert(0, '/tmp/python_lib')
sys.path = [p for p in sys.path if 'easyocr' not in p]
import logging
logging.getLogger('stanza').setLevel(logging.ERROR)
import stanza
nlp = stanza.Pipeline('fr', processors='tokenize,pos,lemma', dir='/tmp/stanza_fr', use_gpu=False, download_method=None)
m = base64.b64decode('$b64_m').decode('utf-8')
s = base64.b64decode('$b64_s').decode('utf-8')
doc_m = nlp(m)
doc_s = nlp(s)
print('MODELE:')
for sent in doc_m.sentences:
    for t in sent.tokens:
        w = t.words[0]
        print(f'  {t.text:15} upos={w.upos:6} lemma={w.lemma:15} feats={w.feats}')
print('ELEVE:')
for sent in doc_s.sentences:
    for t in sent.tokens:
        w = t.words[0]
        print(f'  {t.text:15} upos={w.upos:6} lemma={w.lemma:15} feats={w.feats}')
\" 2>&1");
echo $out;
echo "</pre>";
?>
