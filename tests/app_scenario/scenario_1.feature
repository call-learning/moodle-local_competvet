@local_competvet
Feature: Edit completion settings of an activity
  In order to edit completion settings without accidentally breaking user data
  As a teacher
  I need to edit the activity and use the unlock button if required

  Scenario: Edit completion settings of an activity
    Given the following "courses" exist:
      | fullname                  | shortname | enablecompletion |
      | Compet Vet Course Cypress | CEVETCYPR | 1                |
    And the following "users" exist:
      | username      | firstname | lastname | email                     | password |
      | teachercypr1  | Teacher   | One      | teachercypr1@example.com  | password |
      | observercypr1 | Observer  | One      | observercypr1@example.com | password |
      | observercypr2 | Observer  | Two      | observercypr2@example.com | password |
      | studentcypr1  | Student   | One      | studentcypr1@example.com  | password |
      | studentcypr2  | Student   | Two      | studentcypr2@example.com  | password |
    And the following "course enrolments" exist:
      | user          | course    | role           |
      | teachercypr1  | CEVETCYPR | editingteacher |
      | observercypr1 | CEVETCYPR | observer       |
      | observercypr2 | CEVETCYPR | observer       |
      | studentcypr1  | CEVETCYPR | student        |
      | studentcypr2  | CEVETCYPR | student        |
    And the following "groups" exist:
      | course    | name   | idnumber |
      | CEVETCYPR | Group1 | G1       |
      | CEVETCYPR | Group2 | G2       |
    And the following "group members" exist:
      | group | user         |
      | G1    | studentcypr1 |
      | G2    | studentcypr2 |
    And the following "activities" exist:
      | activity  | course    | idnumber | intro | name    | shortname | completion | completionview | situationtags |
      | competvet | CEVETCYPR | S1       | x     | MEDCHIR | SITCYPR1  | 2          | 1              | y:1           |
    And the following "mod_competvet > plannings" exist:
      | situation | group | startdate        | enddate               | session  |
      | SITCYPR1  | G1    | last Monday      | Monday next week      | SESSION1 |
      | SITCYPR1  | G2    | Monday next week | Monday next fortnight | SESSION1 |
    And the following "mod_competvet > observations" exist:
      | student      | observer      | planning                                                       | context              | comment                     | privatecomment        | category         | status    |
      | studentcypr1 | observercypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1           | Context for this obs | Comment for this obs (obs1) | Private comment(obs1) | eval:observation | completed |
      | studentcypr1 | observercypr2 | last Monday > Monday next week > SESSION1 > SITCYPR1           | Context for this obs | Comment for this obs (obs2) | Private comment(obs1) | eval:observation | completed |
      | studentcypr2 | observercypr1 | Monday next week > Monday next fortnight > SESSION1 > SITCYPR1 | Context for this obs | Comment for this obs        | Private comment(obs1) | eval:observation | completed |
    And the following "mod_competvet > observation_criterion_value" exist:
      | observation                                                                         | criterion | value                          |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr1 | Q001      | 5                              |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr1 | Q002      | Comment for this criteria Q002 |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr1 | Q003      | Comment for this criteria Q003 |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr1 | Q004      | Comment for this criteria Q004 |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr1 | Q005      | Comment for this criteria Q005 |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr2 | Q007      | 8                              |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr2 | Q001      | 6                              |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr2 | Q002      | Comment for this criteria Q002 |
      | last Monday > Monday next week > SESSION1 > SITCYPR1 > studentcypr1 > observercypr2 | Q007      | 10                             |
    And the following "mod_competvet > certification" exist:
      | student      | planning                                             | criterion | level | comment               | status        | supervisors                  | validations                                                                                   |
      | studentcypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1 | CERT1     | 50    | Comment for this cert | cert:seendone | observercypr1, observercypr2 | {observercypr1: "cert:seendone", "My comment"},{observercypr2: "cert:seendone", "My comment"} |
    And the following "mod_competvet > case" exist:
      | student      | planning                                             | fields                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
      | studentcypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1 | "nom_animal": "Rebecca", "espece": "Chien", "race": "Caniche", "sexe": "F", "date_naissance": "2013-11-06", "num_dossier": "2502698046842591", "date_cas": "2024-04-07", "motif_presentation": "Boiterie", "resultats_examens": "Autres examens à faire", "diag_final": "Fracture", "traitement": "Chirurgie", "evolution": "Bon", "taches_effectuees": "Examen clinique, traitement", "reflexions_cas": "Cas complexe. Suivi nécessaire.", "role_charge": "Observateur"                        |
      | studentcypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1 | "nom_animal": "Brian", "espece": "Oiseau", "race": "Perroquet", "sexe": "M", "date_naissance": "2014-07-05", "num_dossier": "2502698068764105", "date_cas": "2023-06-10", "motif_presentation": "Vomissement", "resultats_examens": "Anomalie détectée", "diag_final": "Dermatite", "traitement": "Repos", "evolution": "Stable", "taches_effectuees": "Consultation, examen clinique, diagnostic, traitement", "reflexions_cas": "Réponse positive au traitement.", "role_charge": "Assistant" |
      | studentcypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1 | "nom_animal": "Michelle", "espece": "Oiseau", "race": "Canari", "sexe": "F", "date_naissance": "2012-01-21", "num_dossier": "2502698078674955", "date_cas": "2023-10-21", "motif_presentation": "Diarrhée", "resultats_examens": "Autres examens à faire", "diag_final": "Infection urinaire", "traitement": "Rien", "evolution": "Bon", "taches_effectuees": "Consultation, examen clinique", "reflexions_cas": "Cas complexe. Suivi nécessaire.", "role_charge": "Assistant"                  |
    And the following "mod_competvet > todo" exist:
      | student      | planning                                             | action               | targetuser    | data                               |
      | studentcypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1 | 'eval:asked'         | observercypr1 | "context": "Context for this todo" |
      | studentcypr1 | last Monday > Monday next week > SESSION1 > SITCYPR1 | 'certif:valid:asked' | observercypr1 | "criteria": "CERT1"                |
