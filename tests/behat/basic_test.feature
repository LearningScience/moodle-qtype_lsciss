@qtype @qtype_lsspreadsheet
Feature: Test all the basic functionality of this question type
  In order evaluate students analysis of their own data
  As an teacher
  I need to create and preview spreadsheet questions.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And I log in as "teacher"
    And I follow "Course 1"
    And I navigate to "Question bank" node in "Course administration"

  @javascript @_switch_window
  Scenario: Create, edit then preview a pattern match question.
    # Create a new question.
    And I add a "Spreadsheet" question filling the form with:
      | Question name                 | Test spreadsheet question |
      | Question text                 | Fill in the box           |
      | Spreadsheet JSON              | [{"cell": {"table0_cell_c0_r0": {"celltype": "Label_1", "chart": "", "col": 0, "feedback": "", "formula": "", "rangetype": "", "row": 0, "textvalue": "Height (m)"}, "table0_cell_c1_r0": { "celltype": "CalcAnswer_1", "chart": "", "col": 1, "feedback": "", "formula": "=0.2", "rangetype": "SigfigRange_2", "row": 0, "textvalue": "=0.2"}}, "chartdata": null, "metadata": { "columns": 2, "rows": 15, "title": ""}}] |
    Then I should see "Test spreadsheet question"

    # Preview it. Test correct and incorrect answers.
    When I click on "Preview" "link" in the "Test spreadsheet question" "table_row"
    And I switch to "questionpreview" window

    And I set the following fields to these values:
      | How questions behave | Deferred feedback |
      | Marked out of        | 3                 |
      | Marks                | Show mark and max |
    And I press "Start again with these options"
    And the state of "Fill in the box" question is shown as "Not yet answered"
    And I should see "Height (m)"
    And I switch to the main window

    # Backup the course and restore it.
    When I log out
    And I log in as "admin"
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    When I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    Then I should see "Course 2"
    When I navigate to "Question bank" node in "Course administration"
    Then I should see "Test spreadsheet question"

    # Edit the copy and verify the form field contents.
    When I click on "Edit" "link" in the "Test spreadsheet question" "table_row"
    Then the following fields match these values:
      | Question name                 | Test spreadsheet question |
      | Question text                 | Fill in the box           |
      | Spreadsheet JSON              | [{"cell": {"table0_cell_c0_r0": {"celltype": "Label_1", "chart": "", "col": 0, "feedback": "", "formula": "", "rangetype": "", "row": 0, "textvalue": "Height (m)"}, "table0_cell_c1_r0": { "celltype": "CalcAnswer_1", "chart": "", "col": 1, "feedback": "", "formula": "=0.2", "rangetype": "SigfigRange_2", "row": 0, "textvalue": "=0.2"}}, "chartdata": null, "metadata": { "columns": 2, "rows": 15, "title": ""}}] |
    And I set the following fields to these values:
      | Question name | Edited question name |
    And I press "id_submitbutton"
    Then I should see "Edited question name"
