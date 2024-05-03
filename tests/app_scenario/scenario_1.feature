@local_competvet
Feature: Edit completion settings of an activity
  In order to edit completion settings without accidentally breaking user data
  As a teacher
  I need to edit the activity and use the unlock button if required

  Scenario: Edit completion settings of an activity
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 123 | C123        | 1                |
    And the following "activities" exist:
      | activity | course | idnumber | intro | name     | completion | completionview |
      | page     | C123     | p1       | x     | TestPage | 2          | 1              |
