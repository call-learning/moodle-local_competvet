@local_competvet
Feature: Edit completion settings of an activity
  In order to edit completion settings without accidentally breaking user data
  As a teacher
  I need to edit the activity and use the unlock button if required

  Scenario: Edit completion settings of an activity
    Given the following "courses" exist:
      | fullname            | shortname | enablecompletion |
      | Compet Vet Course 1 | CVET1     | 1                |
    And the following "users" exist:
      | username  | firstname | lastname | email                 | password |
      | teacher1  | Teacher   | One      | teacher1@example.com  | password |
      | observer1 | Observer  | One      | observer1@example.com | password |
      | observer2 | Observer  | Two      | observer2@example.com | password |
      | student1  | Student   | One      | student1@example.com  | password |
      | student2  | Student   | Two      | student2@example.com  | password |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | CVET1  | editingteacher |
      | observer1 | CVET1  | observer       |
      | observer2 | CVET1  | observer       |
      | student1  | CVET1  | student        |
      | student2  | CVET1  | student        |
    And the following "groups" exist:
      | course | name   | idnumber |
      | CVET1  | Group1 | G1       |
      | CVET1  | Group2 | G2       |
    And the following "group members" exist:
      | group | user     |
      | G1    | student1 |
      | G2    | student2 |
    And the following "activities" exist:
      | activity  | course | idnumber | intro | name    | shortname | completion | completionview | situationtags |
      | competvet | CVET1  | S1       | x     | MEDCHIR | SIT1      | 2          | 1              | y:1           |
    And the following "mod_competvet > plannings" exist:
      | situation | group | startdate        | enddate               | session  |
      | SIT1      | G1    | last Monday      | Monday next week      | SESSION1 |
      | SIT1      | G2    | Monday next week | Monday next fortnight | SESSION1 |
    And the following "mod_competvet > observations" exist:
      | student  | observer  | planning                                                   | context              | comment                     | privatecomment        | category         | status    |
      | student1 | observer1 | last Monday > Monday next week > SESSION1 > SIT1           | Context for this obs | Comment for this obs (obs1) | Private comment(obs1) | eval:observation | completed |
      | student1 | observer2 | last Monday > Monday next week > SESSION1 > SIT1           | Context for this obs | Comment for this obs (obs2) | Private comment(obs1) | eval:observation | completed |
      | student2 | observer1 | Monday next week > Monday next fortnight > SESSION1 > SIT1 | Context for this obs | Comment for this obs        | Private comment(obs1) | eval:observation | completed |
    And the following "mod_competvet > observation_criterion_value" exist:
      | observation                                                             | criterion | value                          |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer1 | Q001      | 5                              |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer1 | Q002      | Comment for this criteria Q002 |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer1 | Q003      | Comment for this criteria Q003 |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer1 | Q004      | Comment for this criteria Q004 |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer1 | Q005      | Comment for this criteria Q005 |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer2 | Q007      | 8                              |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer2 | Q001      | 6                              |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer2 | Q002      | Comment for this criteria Q002 |
      | last Monday > Monday next week > SESSION1 > SIT1 > student1 > observer2 | Q007      | 10                             |
    And the following "mod_competvet > certification" exist:
      | student  | planning                                         | criterion | comment               | status        |
      | student1 | last Monday > Monday next week > SESSION1 > SIT1 | CERT1     | Comment for this cert | cert:seendone |
