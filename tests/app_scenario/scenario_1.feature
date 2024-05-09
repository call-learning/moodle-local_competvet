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
      | student1  | Student   | One      | student1@example.com  | password |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | CVET1  | editingteacher |
      | observer1 | CVET1  | observer       |
      | teacher1  | CVET1  | editingteacher |
      | student1  | CVET1  | student        |
    And the following "activities" exist:
      | activity  | course | idnumber | intro | name     | completion | completionview |
      | competvet | CVET1  | p1       | x     | TestPage | 2          | 1              |
