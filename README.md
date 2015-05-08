# Spreassheet question type

Created by Learning Science for Bristol University.

Note that this is currently work-in-progress.


##Installation

###Installation Using Git 

To install using git for the latest version (the master branch), type this command in the
root of your Moodle install:

    git clone https://bitbucket.org/lsciss/moodle-qtype_lsciss.git question/type/lsciss
    echo '/question/type/lsciss/' >> .git/info/exclude


##PHP Excel

This question type ships with PHP Excel 1.8.0 which has been modified to add the ROUNDSIGFIG function, see patch file.  This function is NOT required for the question type itslef to work and was added as a convenience method for the eBiolabs project.

## PHP Unit tests for moodle
See https://docs.moodle.org/dev/PHPUnit#Initialisation_of_test_environment

Add a new dataroot directory and prefix into your config.php, you can find examples in config-dist.php (scroll down to 'Section 9').

    $CFG->phpunit_prefix = 'phpu_';
    $CFG->phpunit_dataroot = '/home/example/phpu_moodledata';

Then you need to initialise the test environment using following command.

    cd /home/example/moodle
    php admin/tool/phpunit/cli/init.php

## Running Spreadsheet PhpUnit tests

    vendor/bin/phpunit question/type/lsciss/tests/lsspreadsheet_cellgrader_test.php
    vendor/bin/phpunit question/type/lsciss/tests/lsspreadsheet_cell_test.php
    vendor/bin/phpunit question/type/lsciss/tests/lsspreadsheet_test.php
    vendor/bin/phpunit question/type/lsciss/tests/question_test.php
    vendor/bin/phpunit question/type/lsciss/tests/questiontype_test.php
    vendor/bin/phpunit question/type/lsciss/tests/phpexcel_test.php
