# LS Spreadsheet Question type

A question type where students enter their own data into a predefined table and can be marked on the results of calculations performed using these values. 

This question type is based on an Excel spreadsheet and allows students to enter their raw data and results of calculations using this data into a table and have their answers automatically marked. The instructor sets up a table and denotes each cell as being a section heading, left or centrally aligned label, input or calculated type using a series of radio buttons. Html tags can be used to format any text.

Section heading: any text entered into a cell denoted as a section heading spans the entire width of the question without affecting the width of that column in the table

Left aligned label: typically used for row headings, the text in these cells is left aligned 

Centrally aligned label: typically used for column headings, the text in these cells is centrally aligned 

Input: used for raw data to be entered by student and not being marked

Calculated: usually used for entering the results of calculations that are based on data in input cells, can also be used to mark raw data that is expected to be within a certain range

An Excel formula is entered into each of the calculated cells and used to mark the calculations. The default mark for each calculated cell is 1 mark but this can be altered to any integer. Each calculated cell can be set to have an absolute range or be marked based on the number of significant figures or decimal places. There is also an inbuilt 2% tolerance on each calculated cell to account for rounding errors that may occur in multi-step calculations.

On submitting the question, the expected answer and the appropriate number of significant figures or decimal places are provided underneath each calculated cell type. Feedback can be provided for each calculated cell or for the question as a whole.


## Required Moodle Version
This version works with Moodle 2013111800 2.6 and above. 


## Installation
1. Ensure you have the correct version of Moodle as stated above. 
2. Put Moodle in 'Maintenance Mode' to prevent any users other than administrators from being able to use the site during installation. See the Moodle documentation [here](http://docs.moodle.org/en/admin/setting/maintenancemode).
3. Copy 'lsciss' to your '<moodle-root>/question/type/' directory
4. Login to your site as admin and follow the standard plugin update notification.
5. Exit 'Maintenance Mode'

## Usage
See the [wiki](https://github.com/LearningScience/moodle-qtype_lsciss/wiki).

## PHP Excel

This question type ships with PHP Excel 1.8.0 which has been modified to add the ROUNDSIGFIG function, see patch file.  This function is NOT required for the question type itself to work and was added as a convenience method.


## License

The LS Spreadsheet Question type is 'free' software released under the [GNU General Public License v3 or later](http://www.gnu.org/copyleft/gpl.html). See 'COPYING.txt'


## Author

Created by [Learning Science Ltd](https://learnsci.co.uk).