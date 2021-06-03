@local @local_syllabus @core @javascript
Feature: As an admin I want to be able to turn on and off the plugin and

  Background:
    Given the following "courses" exist:
      | shortname | fullname | summary            |
      | C1        | Course 1 | Course summary ... |
    Given the following "custom field categories" exist:
      | name              | component   | area   | itemid |
      | Category for test | core_course | course | 0      |
    And the following "custom fields" exist:
      | name    | category          | type | shortname | description | configdata |
      | Field 1 | Category for test | text | f1        | d1          |            |
    Given syllabus fields are updated
    Given the following "local_syllabus > fieldlocation" exist:
      | iddata    | origin                | location | sortorder |
      | fullname  | Origin: Course       | title    | -1        |
      | shortname | Origin: Course       | header   | -1        |
      | fullname  | Origin: Course       | title    | -1        |
      | summary   | Origin: Course       | content  | -1        |
      | f1        | Origin: Custom Field | header   | -1        |

  Scenario: As a guest user I should be able to see the syllabus with its components
    Given I log in as "guest"
    And I am on "Course 1" course homepage
    And I click on "C1" "link" in the ".breadcrumb" "css_element"
    Then I should see "Summary"
    Then I should see "Course 1"
    Then I should see "Course summary ..."
    Then I should see "Field 1"
