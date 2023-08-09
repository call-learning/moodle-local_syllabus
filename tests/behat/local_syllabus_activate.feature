@local @local_syllabus @core
Feature: As an admin I want to be able to turn on and off the plugin and

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    Given syllabus fields are updated
    Given the following "local_syllabus > fieldlocation" exist:
      | iddata    | origin          | location | sortorder |
      | fullname  | Origin: Course | title    | -1        |
      | shortname | Origin: Course | header   | -1        |
      | fullname  | Origin: Course | title    | -1        |
      | summary   | Origin: Course | content  | -1        |

    @javascript
  Scenario: As an admin if I turn off the plugin feature, I should not see any the syllabus for the course and instead the usual Moodle course
  info page
    Given I log in as "guest"
    And I am on "Course 1" course homepage
    Then I should see "Short name"
    And I log in as "admin"
    And I navigate to "Advanced features" in site administration
    And I should see "Enable Syllabus"
    And I set the field "Enable Syllabus" to "0"
    And I click on "Save changes" "button"
    And I log out
    Given I log in as "guest"
    And I am on "Course 1" course homepage
    Then I should not see "Short name"
