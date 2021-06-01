@local @local_syllabus @core @javascript
Feature: As an admin I want to be able to turn on and off the plugin and

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    Given the following "local_resourcelibrary > category" exist:
      | component   | area   | name     |
      | core_course | course | Syllabus |
    Given the following "local_resourcelibrary > field" exist:
      | component   | area   | name                | customfieldcategory | shortname | type     | configdata |
      | core_course | course | Test Field Text     | Syllabus            | CF1       | text     |            |
      | core_course | course | Test Field Checkbox | Syllabus            | CF2       | checkbox |            |
    And the following "activities" exist:
      | activity | name      | intro     | course | idnumber |
      | page     | PageName1 | PageDesc1 | C1     | PAGE1    |

  Scenario: As an admin if I turn off the plugin feature, I should not see any the syllabus for the course and instead the usual Moodle course
    info page
    Given I am on "Course 1" course homepage
    And I follow "C1"
    Then I am on the "Course 1" page
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to "Advanced features" in site administration
    And I should see "Enable Syllabus"
    And I set the field "Enable Syllabus" to "0"
    And I click on "Save changes" "button"
    And I navigate to "Courses" in site administration
    Then I should not see "Syllabus"
    Given I am on "Course 1" course homepage
    And I follow "C1"
    Then I am on the "Course 1" page
