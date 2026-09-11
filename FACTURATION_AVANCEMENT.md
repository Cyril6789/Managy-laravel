# Facturation — suivi d’implémentation

## Éditeur de facture professionnel

- [x] Utiliser la même grande modale pour une facture libre et une facture issue d’une intervention.
- [x] La modale ne doit pas pouvoir se fermer en cliquant ailleur
- [x] les élements de création d'une ligne faturable, doivent visuellement se trouver une seule ligne (Catalogue, designation, quantité, prix u, unité, prix saisi, tva) pas sur deux ligne
- [x] Laisser les dropdowns de la ligne d'ajout passer au-dessus du contenu, sans scroll interne, et compacter les boutons pour supprimer le scroll horizontal
- [x] Rajoute aussi un bouton "Enregistrer le brouillon", et donc le statut associé (en brouillon) (sans numérotation du coup) editable plus tard
- [x] Préremplir le client et toutes les lignes facturables de l’intervention sans créer de facture à l’ouverture.
- [x] Permettre l’ajout d’une prestation du catalogue ou d’une ligne entièrement libre.
- [x] Permettre la modification de la désignation, quantité, unité, prix HT et TVA de chaque ligne.
- [x] Permettre la suppression et le réordonnancement des lignes par glisser-déposer.
- [x] Permettre une remise en euros ou en pourcentage sur chaque ligne.
- [x] Permettre une remise globale en euros ou en pourcentage, avec ventilation correcte de la TVA.
- [x] Afficher dans la modale un récapitulatif dynamique HT, TVA et TTC avant émission.
- [x] Ouvrir le PDF généré dans une modale et rafraîchir l’historique des factures.

## Émission et immutabilité

- [x] Ne réserver le numéro et ne créer la facture qu’au clic final de génération.
- [x] Lier définitivement la facture d’intervention à son intervention et empêcher tout doublon.
- [x] Interdire la modification et la suppression d’une facture après son émission.
- [x] Conserver le PDF original archivé sans jamais l’écraser ni le régénérer.
- [x] Appliquer les réglages et mentions légales uniquement aux nouvelles factures ; les archives existantes restent inchangées.

## Suivi des paiements

- [x] Ajouter un historique append-only des règlements avec date, montant, mode, référence et note facultative.
- [x] Autoriser plusieurs règlements et plusieurs modes de paiement sur une même facture.
- [x] Proposer les modes espèces, carte bancaire, chèque, virement et autre.
- [x] Calculer automatiquement le total réglé et le solde restant.
- [x] Calculer automatiquement les statuts « En attente de paiement », « Partiellement payée » et « Payée ».
- [x] Empêcher un règlement nul, négatif ou supérieur au solde restant.
- [x] Afficher le statut et les montants réglé/restant dans l’historique des factures.
- [x] Ajouter l’interface de saisie et l’historique des règlements depuis la consultation d’une facture.

## PDF acquitté sans altérer l’original

- [x] Masquer les colonnes TVA et TTC et afficher uniquement les montants HT lorsque la société est dispensée de TVA.
- [x] Masquer aussi tous les choix et champs TVA/HT-TTC dans l’éditeur lorsque la société est dispensée de TVA.
- [x] Injecter les mentions légales par défaut dans toute nouvelle facture même si les réglages n’ont jamais été enregistrés.
- [x] Conserver le PDF commercial original comme archive immuable.
- [x] Générer une copie d’état distincte après chaque règlement, sans écraser les versions précédentes.
- [x] Afficher un tampon « PARTIELLEMENT PAYÉE » ou « PAYÉE » sur la copie d’état.
- [x] Positionner les tampons de paiement sous le bloc d’adresses, dans une zone blanche du PDF.
- [x] Ancrer les conditions et totaux en bas de la dernière page, avec le détail des règlements juste au-dessus et un passage de page sûr pour les factures longues.
- [x] Aligner par le bas les conditions et le total, à environ 1,5 cm de la séparation du pied de page.
- [x] Afficher sur la copie d’état les dates, montants et modes des règlements.
- [x] Permettre de consulter/télécharger l’original et la dernière copie d’état.

## Vérifications

- [x] Tester une facture libre avec lignes catalogue et libres, remises par ligne et remise globale.
- [x] Tester une facture d’intervention préremplie, réordonnée puis générée.
- [x] Tester qu’une facture émise ne peut être ni modifiée, ni supprimée, ni générée deux fois.
- [x] Tester les trois statuts de paiement et plusieurs règlements successifs.
- [x] Tester que chaque copie d’état est créée séparément et que le PDF original reste byte-à-byte identique.
- [x] Exécuter la suite PHP complète et le build frontend.

## Autre
- [x] Les factures doivent aussi être visible dans la fiche client, et dans la fiche inter
- [x] Améliorer l’affichage des factures dans la fiche client avec leurs statuts colorés et signaler en haut les règlements en attente.
