# Spreadsheet question type

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