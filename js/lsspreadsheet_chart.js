/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *Utility function thats called from the document ready functions written by php
 *The functions here are not for editing the chart properites!
 *
 *@param {Integer} id moodle id of the question
 */
function isEncHTML(str) {
    if(str.search(/&amp;/g) != -1 || str.search(/&lt;/g) != -1 || str.search(/&gt;/g) != -1)
        return true;
    else
        return false;
};

function decHTMLifEnc(str){
    if(typeof(str)==="string"){
        if(isEncHTML(str))
            return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g,' ');
        return str;
    }
}

function set_up_question(id)
{
    
    set_chart_data_listeners(id);
    get_student_chart_data(id);
    get_chart_stats(id);
    update_chart_image(id);
}

/**
 * Function to add event listeners (click and change) to update the stats and the chart image
 * @param {Integer} id the moodle id of the question
 * 
 */

function set_chart_data_listeners(id)
{ 
    //The "resp" part is to do with the way that moodle labels text inputs
    
    $.each(lsspreadsheetdata[id].chartinstructions.xseries, function (i, elem) {
        $('input[name="resp'+lsspreadsheetdata[id].qid+"_"+elem+'"]').change(function(){
            get_student_chart_data(id);
        });
    });
    
    $.each(lsspreadsheetdata[id].chartinstructions.xseries, function (i, elem) {
        $('input[name="resp'+lsspreadsheetdata[id].qid+"_"+elem+'"]').click(function(){
            get_student_chart_data(id);
        });
    });
        
    $.each(lsspreadsheetdata[id].chartinstructions.yseries, function (i, elem) {     
        $('input[name="resp'+lsspreadsheetdata[id].qid+"_"+elem+'"]').change(function(){
            get_student_chart_data(id);
        });
    });   
    
    $.each(lsspreadsheetdata[id].chartinstructions.yseries, function (i, elem) {     
        $('input[name="resp'+lsspreadsheetdata[id].qid+"_"+elem+'"]').click(function(){
            get_student_chart_data(id);
        });
    });  
}

/**
 * Function to get all of the data for a graphing question and send it to the server for plotting
 * 
 * @param {Integet} id moodle id of the question
 */
function get_student_chart_data(id){
    
    //reset the chart data each time
    lsspreadsheetdata[id].chartdata = {}
    lsspreadsheetdata[id].chartdata.xseries = [];
    lsspreadsheetdata[id].chartdata.yseries = [];
    lsspreadsheetdata[id].chartdata.title = decHTMLifEnc(lsspreadsheetdata[id].chartmeta.series[0].title);
    lsspreadsheetdata[id].chartdata.xaxistitle = decHTMLifEnc(lsspreadsheetdata[id].chartmeta.series[0].xaxistitle);
    lsspreadsheetdata[id].chartdata.yaxistitle = decHTMLifEnc(lsspreadsheetdata[id].chartmeta.series[0].yaxistitle);
    lsspreadsheetdata[id].chartdata.urlsalt = Math.floor(Math.random()*11);
    
    var xpoint=0,
    ypoint=0;

    $.each(lsspreadsheetdata[id].chartinstructions.xseries, function (i, elem) {
        xpoint = $('input[name="resp'+lsspreadsheetdata[id].qid+"_"+elem+'"]').val();
        xpoint = parseFloat(xpoint);
        lsspreadsheetdata[id].chartdata.xseries.push(xpoint);
    });
        
    $.each(lsspreadsheetdata[id].chartinstructions.yseries, function (i, elem) {
        ypoint = $('input[name="resp'+lsspreadsheetdata[id].qid+"_"+elem+'"]').val();
        ypoint = parseFloat(ypoint);
        lsspreadsheetdata[id].chartdata.yseries.push(ypoint);
    });    
    
    //Get the r^2, intercept and slope of the line of best fit
    get_chart_stats(id);

}

/**
 * function to change the image source of the chart, sends the get data for the plot
 * 
 * @param {Integer} id moodle id of the question to pull the information from the page and know which image to update
 */
function update_chart_image(id){
    
    var jsondata = JSON.stringify(lsspreadsheetdata[id].chartdata);
    var src = lsspreadsheetdata[id].server+"/question/type/lsspreadsheet/ajax_chart.php?data="+jsondata;
    $('#lsspreadsheetchart_resp'+lsspreadsheetdata[id].qid).attr("src",src);
}
 
/**
  * Function to get the text values of the r^2, Intercept and slope of the line of best fit
  * 
  * @param {Integer} id Moodle id of the question
  */
function get_chart_stats(id)
{
    var jsondata = JSON.stringify(lsspreadsheetdata[id].chartdata);
    
    $.get(lsspreadsheetdata[id].server+'/question/type/lsspreadsheet/ajax_chartStats.php',{
        data:jsondata
    }, function(stats_json) {
        var stats = $.parseJSON(stats_json);
        
        if(stats != false){
            lsspreadsheetdata[id].stats = stats;
            try{
            if(stats.linearregresion !== false){
                $('#stats_slope_td_resp'+lsspreadsheetdata[id].qid).html(lsspreadsheetdata[id].stats.linearregression.slope);
                $('#stats_intercept_td_resp'+lsspreadsheetdata[id].qid).html(lsspreadsheetdata[id].stats.linearregression.intercept);
            }
            else{
                $('#stats_slope_td_resp'+lsspreadsheetdata[id].qid).html("");
                $('#stats_intercept_td_resp'+lsspreadsheetdata[id].qid).html("");
            }
            if(stats.rsquared !== false){
                $('#stats_rsquared_td_resp'+lsspreadsheetdata[id].qid).html(lsspreadsheetdata[id].stats.rsquared.value);
            }
            else{
                $('#stats_rsquared_td_resp'+lsspreadsheetdata[id].qid).html(""); 
            }
            update_chart_image(id);
            }
            catch(e){
                
            }
        }

    });
  
}